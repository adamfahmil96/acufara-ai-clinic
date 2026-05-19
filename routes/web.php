<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\WhatsAppAuthController;

Route::get('/', function () {
    return view('welcome');
});

// WhatsApp OTP Authentication Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [WhatsAppAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login/otp', [WhatsAppAuthController::class, 'requestOtp'])->name('login.otp');
    Route::get('/login/verify', [WhatsAppAuthController::class, 'showVerifyForm'])->name('login.verify');
    Route::post('/login/verify', [WhatsAppAuthController::class, 'verifyOtp'])->name('login.verify.post');
});

Route::post('/logout', [WhatsAppAuthController::class, 'logout'])->name('logout');

// Protected Routes for Patient
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});
