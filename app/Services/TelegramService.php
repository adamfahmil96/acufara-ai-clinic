<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Transport tipis untuk Telegram Bot API.
 *
 * Hanya bertugas mengirim pesan — format & routing penerima ada di
 * BookingNotificationService. Lihat docs/decisions/ADR-001-telegram-notifikasi-internal.md
 * untuk alasan Telegram dipakai menggantikan Fonnte pada jalur notifikasi internal.
 */
class TelegramService
{
    private const API_BASE = 'https://api.telegram.org';

    /** Batas keras Telegram untuk field `text` pada sendMessage. */
    private const MAX_MESSAGE_LENGTH = 4096;

    /**
     * Escape teks agar aman disisipkan ke pesan dengan parse_mode=HTML.
     *
     * HTML dipilih di atas MarkdownV2 karena hanya mewajibkan escape 3 karakter
     * (< > &), sementara MarkdownV2 mewajibkan 18 karakter termasuk '.' dan '-'.
     * Alamat pasien seperti "Jl. Solo-Sragen No.5" membuat MarkdownV2 gagal
     * dengan "400 Bad Request: can't parse entities".
     */
    public static function escape(?string $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function isConfigured(): bool
    {
        return (bool) Config::get('services.telegram.bot_token');
    }

    /**
     * Kirim pesan ke satu chat Telegram.
     *
     * @param  string  $html    Pesan dengan tag HTML terbatas yang didukung Telegram
     *                          (<b>, <i>, <code>, <a>). Seluruh nilai dinamis di
     *                          dalamnya wajib sudah melewati static::escape().
     * @param  array<int, array{text: string, url: string}>  $buttons  Tombol inline (satu baris)
     * @return bool  true hanya jika Telegram membalas ok:true
     */
    public function sendMessage(string $chatId, string $html, array $buttons = []): bool
    {
        $token = Config::get('services.telegram.bot_token');

        if (! $token) {
            Log::info("[TELEGRAM] TELEGRAM_BOT_TOKEN tidak diatur. Pesan tidak dikirim ke chat {$chatId}");
            Log::info("[TELEGRAM] Pesan:\n{$html}");

            return false;
        }

        $payload = array_merge($this->buildTextPayload($html), [
            'chat_id' => $chatId,
            'link_preview_options' => ['is_disabled' => true],
        ]);

        if ($buttons !== []) {
            $payload['reply_markup'] = [
                'inline_keyboard' => [array_values(array_map(
                    fn (array $button): array => [
                        'text' => $button['text'],
                        'url' => $button['url'],
                    ],
                    $buttons
                ))],
            ];
        }

        try {
            // CATATAN KEAMANAN: token berada di path URL, bukan header. Jangan pernah
            // mencatat URL ini (atau response mentah yang memuatnya) ke log.
            $response = Http::asJson()
                ->timeout(15)
                ->post(self::API_BASE . "/bot{$token}/sendMessage", $payload);
        } catch (\Throwable $e) {
            Log::error("[TELEGRAM] Exception kirim ke chat {$chatId}: " . $e->getMessage());

            return false;
        }

        // Telegram bisa membalas ok:false, jadi HTTP status saja tidak cukup.
        // Ini pelajaran langsung dari Fonnte, yang membalas HTTP 200 dengan
        // status:false sehingga log melaporkan sukses padahal pesan tidak terkirim.
        // Lihat docs/fonnte-monitoring-guide.md bagian 1.
        if ($response->json('ok') === true) {
            Log::info("[TELEGRAM] Notifikasi terkirim ke chat {$chatId}");

            return true;
        }

        $errorCode = $response->json('error_code') ?? $response->status();
        $description = $response->json('description') ?? 'tidak ada deskripsi dari Telegram';

        Log::error("[TELEGRAM] Gagal kirim ke chat {$chatId} (error {$errorCode}): {$description}");

        if ((int) $errorCode === 403) {
            Log::error(
                "[TELEGRAM] Chat {$chatId} tidak dapat dijangkau bot. Penyebab umum: "
                . 'penerima belum pernah menekan /start pada bot (chat pribadi), '
                . 'atau bot sudah dikeluarkan dari grup.'
            );
        }

        return false;
    }

    /**
     * Tentukan field `text` + `parse_mode` yang aman untuk dikirim.
     *
     * Pemotongan sesungguhnya dilakukan per-field oleh pemanggil, sehingga cabang
     * "kepanjangan" di bawah ini adalah jaring pengaman terakhir. Memotong string
     * HTML mentah berbahaya (bisa memutus entity atau meninggalkan tag tak
     * tertutup, yang membuat Telegram menolak pesan), jadi jika batas terlampaui
     * pesan diturunkan menjadi teks polos tanpa parse_mode — lebih baik terkirim
     * tanpa format daripada gagal total.
     *
     * @return array{text: string, parse_mode?: string}
     */
    private function buildTextPayload(string $html): array
    {
        if (mb_strlen($html) <= self::MAX_MESSAGE_LENGTH) {
            return [
                'text' => $html,
                'parse_mode' => 'HTML',
            ];
        }

        Log::warning('[TELEGRAM] Pesan melebihi ' . self::MAX_MESSAGE_LENGTH . ' karakter, dikirim sebagai teks polos');

        $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return [
            'text' => mb_substr($plain, 0, self::MAX_MESSAGE_LENGTH - 1) . '…',
        ];
    }
}
