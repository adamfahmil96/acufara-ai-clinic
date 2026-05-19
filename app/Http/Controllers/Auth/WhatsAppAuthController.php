<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppAuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string|min:10|max:15',
        ]);

        $waNumber = $request->input('whatsapp_number');
        
        // Bersihkan format nomor WA (misal jika ada awalan 0, ubah ke 62, dll jika perlu)
        // Untuk sekarang simpan as is.
        $this->otpService->generate($waNumber);

        // Simpan nomor WA ke session untuk halaman verifikasi
        session(['otp_whatsapp_number' => $waNumber]);

        return redirect()->route('login.verify')->with('status', 'Kode OTP telah dikirim ke nomor WhatsApp Anda.');
    }

    public function showVerifyForm()
    {
        if (!session()->has('otp_whatsapp_number')) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', ['waNumber' => session('otp_whatsapp_number')]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:4',
        ]);

        $waNumber = session('otp_whatsapp_number');
        $otp = $request->input('otp');

        if (!$waNumber) {
            return redirect()->route('login')->withErrors(['whatsapp_number' => 'Sesi telah habis. Silakan login kembali.']);
        }

        if ($this->otpService->verify($waNumber, $otp)) {
            // OTP Valid
            session()->forget('otp_whatsapp_number');

            // Cari atau buat User
            $user = User::where('whatsapp_number', $waNumber)->first();

            if (!$user) {
                // Buat user baru
                $user = User::create([
                    'name' => 'Pasien ' . substr($waNumber, -4), // Nama default
                    'whatsapp_number' => $waNumber,
                ]);

                // Assign role patient (pastikan role 'patient' sudah ada)
                $user->assignRole('patient');

                // Buat record patient
                Patient::create([
                    'user_id' => $user->id,
                ]);
            }

            // Login user
            Auth::login($user);

            // Redirect ke halaman profile atau booking
            return redirect('/profile')->with('success', 'Berhasil login!');
        }

        return back()->withErrors(['otp' => 'Kode OTP salah atau telah kedaluwarsa.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
