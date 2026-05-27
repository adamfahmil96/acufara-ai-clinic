<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate and send OTP to the given WhatsApp number.
     *
     * @param string $waNumber
     * @return void
     */
    public function generate(string $waNumber): void
    {
        // Generate a random 4-digit OTP
        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        // Simpan OTP ke cache selama 5 menit
        Cache::put($this->getCacheKey($waNumber), $otp, now()->addMinutes(5));

        // Mengirim via Fonnte API
        $token = config('services.fonnte.token');
        
        if ($token) {
            try {
                \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => $token
                ])->post('https://api.fonnte.com/send', [
                    'target' => $waNumber,
                    'message' => "Kode OTP Acufara Clinic Anda adalah: *{$otp}*\n\nJangan berikan kode ini kepada siapapun demi keamanan akun Anda.",
                    'countryCode' => '62',
                ]);
            } catch (\Exception $e) {
                Log::error("❌ [FONNTE] Gagal mengirim OTP ke {$waNumber}: " . $e->getMessage());
            }
        }

        // Tetap catat di log untuk memudahkan development & debugging
        $this->sendViaLog($waNumber, $otp);
    }

    /**
     * Verify the provided OTP.
     *
     * @param string $waNumber
     * @param string $otp
     * @return bool
     */
    public function verify(string $waNumber, string $otp): bool
    {
        $cachedOtp = Cache::get($this->getCacheKey($waNumber));

        if ($cachedOtp && $cachedOtp === $otp) {
            // Valid OTP, clear from cache
            Cache::forget($this->getCacheKey($waNumber));
            return true;
        }

        return false;
    }

    private function getCacheKey(string $waNumber): string
    {
        return 'otp_verification_' . $waNumber;
    }

    /**
     * Simulate sending OTP via Log.
     */
    private function sendViaLog(string $waNumber, string $otp): void
    {
        Log::info("🚀 [OTP SERVICE] Mengirim OTP ke WhatsApp: {$waNumber}. Kode OTP: {$otp}");
    }
}
