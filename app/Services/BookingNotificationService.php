<?php

namespace App\Services;

use App\Mail\BookingNotificationFallbackMail;
use App\Models\Appointment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Menyusun & merutekan notifikasi booking baru ke klinik/admin.
 *
 * Menggantikan WhatsAppNotificationService (Fonnte). Kanal utama Telegram,
 * dengan email sebagai jaring pengaman jika Telegram gagal — sebuah notifikasi
 * booking yang hilang berarti pasien tidak dilayani.
 */
class BookingNotificationService
{
    /**
     * Batas panjang per-field.
     *
     * Telegram menolak pesan di atas 4096 karakter, sedangkan WhatsApp memberi
     * ruang ~65.000 sehingga batas ini tidak pernah relevan sebelumnya. Keluhan,
     * riwayat, dan rekomendasi AI semuanya teks bebas — `ai_recommendation`
     * berasal dari Gemini dengan maxOutputTokens 2048 dan bisa melampaui 4000
     * karakter sendirian. Memotong per-field (bukan memotong pesan akhir)
     * menjaga struktur pesan tetap utuh; detail lengkap tetap bisa dibuka lewat
     * tombol ke admin panel.
     */
    private const MAX_FIELD_LENGTH = 300;

    public function __construct(
        private TelegramService $telegram
    ) {}

    public function notifyNewBooking(Appointment $appointment): void
    {
        $appointment->loadMissing(['patient.user', 'branch', 'service']);

        $chatId = $this->resolveChatId($appointment);
        $sections = $this->sections($appointment);
        $adminUrl = $this->adminUrl($appointment);

        if (! $chatId) {
            Log::warning(
                '[BOOKING NOTIFY] Tidak ada chat Telegram tujuan untuk booking #' . $appointment->id
                . '. Isi telegram_chat_id pada cabang atau atur TELEGRAM_DEFAULT_CHAT_ID.'
            );

            $this->sendEmailFallback($appointment, $sections, $adminUrl);

            return;
        }

        $sent = $this->telegram->sendMessage(
            $chatId,
            $this->renderHtml($sections),
            [['text' => '🔗 Buka di Admin Panel', 'url' => $adminUrl]],
        );

        if (! $sent) {
            $this->sendEmailFallback($appointment, $sections, $adminUrl);
        }
    }

    /**
     * Tentukan chat tujuan berdasarkan tipe kunjungan.
     *
     * Klinik → grup Telegram cabang; homecare → chat default (Acufara pusat).
     * Cabang yang belum mengisi telegram_chat_id jatuh ke chat default supaya
     * notifikasi tidak hilang begitu saja.
     */
    private function resolveChatId(Appointment $appointment): ?string
    {
        $default = Config::get('services.telegram.default_chat_id');

        if ($appointment->service_location_type === Appointment::LOCATION_CLINIC) {
            $branchChatId = $appointment->branch?->telegram_chat_id;

            return $branchChatId ? (string) $branchChatId : $this->asStringOrNull($default);
        }

        return $this->asStringOrNull($default);
    }

    /**
     * Isi pesan sebagai data terstruktur, agar bisa dirender ke HTML (Telegram)
     * maupun teks polos (email) tanpa menduplikasi template.
     *
     * @return array<string, array<string, string>>
     */
    private function sections(Appointment $appointment): array
    {
        $patient = $appointment->patient;
        $user = $patient?->user;

        $gender = match ($patient?->gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => '-',
        };

        $jadwal = $appointment->scheduled_at
            ? $appointment->scheduled_at->translatedFormat('l, d F Y H:i') . ' WIB'
            : '-';

        $tipe = $appointment->service_location_type === Appointment::LOCATION_HOMECARE
            ? 'Homecare (Panggilan ke Rumah)'
            : 'Kunjungan Klinik';

        return [
            '👤 Data Pasien' => [
                'Nama' => $this->field($user?->name),
                'WA' => $this->field($user?->whatsapp_number),
                'Jenis Kelamin' => $gender,
                'Alamat' => $this->field($patient?->default_address),
            ],
            '📅 Detail Booking' => [
                'Layanan' => $this->field($appointment->service?->name),
                'Cabang' => $this->field($appointment->branch?->nama_cabang),
                'Jadwal' => $jadwal,
                'Tipe' => $tipe,
                'Keluhan' => $this->field($appointment->complaint_summary),
                'Riwayat Penyakit' => $this->field($appointment->medical_history),
                'Riwayat Alergi' => $this->field($appointment->allergy_history),
            ],
            '🤖 Analisis AI' => [
                'Urgensi' => $this->field($appointment->ai_urgency),
                'Rekomendasi' => $this->field($appointment->ai_recommendation),
            ],
        ];
    }

    /**
     * @param  array<string, array<string, string>>  $sections
     */
    private function renderHtml(array $sections): string
    {
        $lines = ['📋 <b>BOOKING BARU - Acufara Clinic</b>'];

        foreach ($sections as $title => $fields) {
            $lines[] = '';
            $lines[] = '<b>' . TelegramService::escape($title) . '</b>';

            foreach ($fields as $label => $value) {
                $lines[] = TelegramService::escape($label) . ': ' . TelegramService::escape($value);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, array<string, string>>  $sections
     */
    private function renderPlain(array $sections, string $adminUrl): string
    {
        $lines = ['BOOKING BARU - Acufara Clinic'];

        foreach ($sections as $title => $fields) {
            $lines[] = '';
            $lines[] = $title;

            foreach ($fields as $label => $value) {
                $lines[] = "{$label}: {$value}";
            }
        }

        $lines[] = '';
        $lines[] = 'Lihat di Admin Panel:';
        $lines[] = $adminUrl;

        return implode("\n", $lines);
    }

    /**
     * Kirim email jika Telegram gagal, supaya booking tidak terlewat.
     *
     * @param  array<string, array<string, string>>  $sections
     */
    private function sendEmailFallback(Appointment $appointment, array $sections, string $adminUrl): void
    {
        $email = Config::get('services.telegram.fallback_email');

        if (! $email) {
            Log::warning('[BOOKING NOTIFY] Telegram gagal dan MONITORING_EMAIL tidak diatur. Booking #' . $appointment->id . ' tidak dinotifikasikan.');

            return;
        }

        try {
            Mail::to($email)->send(new BookingNotificationFallbackMail(
                $appointment,
                $this->renderPlain($sections, $adminUrl),
            ));

            Log::info("[BOOKING NOTIFY] Fallback email terkirim ke {$email} untuk booking #{$appointment->id}");
        } catch (\Throwable $e) {
            Log::error('[BOOKING NOTIFY] Fallback email juga gagal untuk booking #' . $appointment->id . ': ' . $e->getMessage());
        }
    }

    private function adminUrl(Appointment $appointment): string
    {
        return rtrim((string) Config::get('app.url'), '/') . "/admin/appointments/{$appointment->id}";
    }

    private function field(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' ? '-' : Str::limit($value, self::MAX_FIELD_LENGTH);
    }

    private function asStringOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
