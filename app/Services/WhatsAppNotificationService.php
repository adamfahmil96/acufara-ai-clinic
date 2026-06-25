<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class WhatsAppNotificationService
{
    /**
     * Kirim notifikasi booking baru ke Acufara.
     * - Tipe Klinik → kirim ke whatsapp_number cabang
     * - Tipe Homecare → kirim ke ACRUFARA_WHATSAPP_NUMBER (.env)
     */
    public function notifyNewBooking(Appointment $appointment): void
    {
        $appointment->load(['patient.user', 'branch', 'service']);

        $targetNumber = $this->resolveTargetNumber($appointment);

        if (!$targetNumber) {
            Log::warning('[WA NOTIFY] Tidak ada nomor tujuan untuk notifikasi booking #' . $appointment->id);
            return;
        }

        $message = $this->formatMessage($appointment);
        $this->sendViaFonnte($targetNumber, $message);
    }

    /**
     * Tentukan nomor tujuan berdasarkan tipe kunjungan.
     */
    private function resolveTargetNumber(Appointment $appointment): ?string
    {
        if ($appointment->service_location_type === Appointment::LOCATION_CLINIC) {
            return $appointment->branch?->whatsapp_number;
        }

        return Config::get('services.fonnte.acufara_number');
    }

    /**
     * Format pesan notifikasi.
     */
    private function formatMessage(Appointment $appointment): string
    {
        $patient = $appointment->patient;
        $user = $patient?->user;
        $branch = $appointment->branch;
        $service = $appointment->service;

        $nama = $user?->name ?? '-';
        $wa = $user?->whatsapp_number ?? '-';
        $gender = $patient?->gender === 'male' ? 'Laki-laki' : ($patient?->gender === 'female' ? 'Perempuan' : '-');
        $alamat = $patient?->default_address ?: '-';
        $layanan = $service?->name ?? '-';
        $cabang = $branch?->nama_cabang ?? '-';
        $jadwal = $appointment->scheduled_at
            ? $appointment->scheduled_at->translatedFormat('l, d F Y H:i') . ' WIB'
            : '-';
        $tipe = $appointment->service_location_type === Appointment::LOCATION_HOMECARE
            ? 'Homecare (Panggilan ke Rumah)'
            : 'Kunjungan Klinik';
        $keluhan = $appointment->complaint_summary ?: '-';
        $urgency = $appointment->ai_urgency ?: '-';
        $recommendation = $appointment->ai_recommendation ?: '-';
        $appUrl = config('app.url');
        $adminLink = "{$appUrl}/admin/appointments/{$appointment->id}";

        return <<<MSG
📋 *BOOKING BARU - Acufara Clinic*

👤 *Data Pasien:*
Nama: {$nama}
WA: {$wa}
Jenis Kelamin: {$gender}
Alamat: {$alamat}

📅 *Detail Booking:*
Layanan: {$layanan}
Cabang: {$cabang}
Jadwal: {$jadwal}
Tipe: {$tipe}
Keluhan: {$keluhan}

🤖 *Analisis AI:*
Urgensi: {$urgency}
Rekomendasi: {$recommendation}

🔗 Lihat di Admin Panel:
{$adminLink}
MSG;
    }

    /**
     * Kirim pesan via Fonnte API.
     */
    private function sendViaFonnte(string $targetNumber, string $message): void
    {
        $token = Config::get('services.fonnte.token');

        if (!$token) {
            Log::info("[WA NOTIFY] FONNTE_TOKEN tidak diatur. Pesan tidak dikirim ke {$targetNumber}");
            Log::info("[WA NOTIFY] Pesan:\n{$message}");
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $targetNumber,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("[WA NOTIFY] Notifikasi berhasil dikirim ke {$targetNumber}");
            } else {
                Log::error("[WA NOTIFY] Gagal kirim ke {$targetNumber}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("[WA NOTIFY] Exception kirim ke {$targetNumber}: " . $e->getMessage());
        }
    }
}
