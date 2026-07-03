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
                'checked_at' => now()->toISOString(),
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/device');

            $data = $response->json();
            $isConnected = $data['status'] ?? false;

            $result = [
                'connected' => $isConnected,
                'message' => $isConnected ? 'Device terhubung' : ($data['reason'] ?? 'Device tidak terhubung'),
                'checked_at' => now()->toISOString(),
            ];

            Cache::put(self::CACHE_KEY, $result, self::CACHE_TTL);

            Log::info('[FONNTE MONITOR] Status check: ' . ($isConnected ? 'CONNECTED' : 'DISCONNECTED'));

            return $result;
        } catch (\Exception $e) {
            Log::error('[FONNTE MONITOR] Exception: ' . $e->getMessage());

            $result = [
                'connected' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'checked_at' => now()->toISOString(),
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
