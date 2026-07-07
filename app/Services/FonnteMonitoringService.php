<?php

namespace App\Services;

use App\Mail\FonnteDisconnectedMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FonnteMonitoringService
{
    private const CACHE_KEY = 'fonnte_status';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Cek status Fonntee dan simpan ke cache.
     */
    public function checkStatus(): array
    {
        $token = Config::get('services.fonnte.token');

        if (!$token) {
            Log::warning('[FONNTE MONITOR] FONNTE_TOKEN tidak diatur');

            return [
                'connected' => false,
                'message' => 'FONNTE_TOKEN tidak diatur',
                'checked_at' => now()->setTimezone('Asia/Jakarta')->toISOString(),
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/device');

            $data = $response->json();

            // Log response mentah untuk debugging
            Log::debug('[FONNTE MONITOR] Raw response: ' . json_encode($data));

            // Response Fonntee:
            // - "status": true/false → status API request (berhasil/gagal)
            // - "device_status": "connect"/"connected"/"disconnect" → status koneksi WhatsApp
            // CATATAN: Fonntee bisa mengembalikan "connect" ATAU "connected"
            $deviceStatus = strtolower($data['device_status'] ?? 'unknown');
            $isConnected = in_array($deviceStatus, ['connect', 'connected']);

            $message = $isConnected
                ? 'Device terhubung'
                : "Device status: {$deviceStatus}";

            $result = [
                'connected' => $isConnected,
                'device_status' => $deviceStatus,
                'device' => $data['device'] ?? null,
                'name' => $data['name'] ?? null,
                'expired' => $data['expired'] ?? null,
                'quota' => $data['quota'] ?? null,
                'message' => $message,
                'checked_at' => now()->setTimezone('Asia/Jakarta')->toISOString(),
            ];

            Cache::put(self::CACHE_KEY, $result, self::CACHE_TTL);

            Log::info('[FONNTE MONITOR] Status check: ' . ($isConnected ? 'CONNECTED' : 'DISCONNECTED') . ' | Device: ' . ($data['device'] ?? '-') . ' | Status: ' . $deviceStatus);

            return $result;
        } catch (\Exception $e) {
            Log::error('[FONNTE MONITOR] Exception: ' . $e->getMessage());

            $result = [
                'connected' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'checked_at' => now()->setTimezone('Asia/Jakarta')->toISOString(),
            ];

            Cache::put(self::CACHE_KEY, $result, self::CACHE_TTL);

            return $result;
        }
    }

    /**
     * Ambil status dari cache (jika ada) atau cek ulang.
     */
    public function getStatus(): array
    {
        return Cache::get(self::CACHE_KEY, fn () => $this->checkStatus());
    }

    /**
     * Kirim email notifikasi jika Fonntee disconnect (dengan throttle).
     */
    public function notifyIfDisconnected(array $status): void
    {
        if ($status['connected']) {
            return;
        }

        $emailThrottle = Config::get('services.fonnte.email_throttle', 30);
        $monitoringEmail = Config::get('services.fonnte.monitoring_email');

        if (!$monitoringEmail) {
            Log::warning('[FONNTE MONITOR] MONITORING_EMAIL tidak diatur');
            return;
        }

        $lastEmailSent = Cache::get('fonnte_last_email_sent_at');

        if ($lastEmailSent && now()->diffInMinutes($lastEmailSent) < $emailThrottle) {
            Log::info('[FONNTE MONITOR] Email throttle aktif, skip pengiriman');
            return;
        }

        try {
            Mail::to($monitoringEmail)->send(new FonnteDisconnectedMail($status));

            Cache::put('fonnte_last_email_sent_at', now(), $emailThrottle * 60);

            Log::info('[FONNTE MONITOR] Email notifikasi terkirim ke ' . $monitoringEmail);
        } catch (\Exception $e) {
            Log::error('[FONNTE MONITOR] Gagal kirim email: ' . $e->getMessage());
        }
    }
}
