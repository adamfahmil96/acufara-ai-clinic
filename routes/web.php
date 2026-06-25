<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\WhatsAppAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelfRegisterController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{article:slug}', [BlogController::class, 'show'])->name('blog.show');

// Layanan Routes
Route::view('/layanan/akupunktur', 'layanan.akupunktur')->name('layanan.akupunktur');
Route::view('/layanan/bekam', 'layanan.bekam')->name('layanan.bekam');
Route::view('/layanan/baby-spa', 'layanan.baby-spa')->name('layanan.baby-spa');

// Self-Registration Routes (Public, no auth required)
Route::get('/daftar', [SelfRegisterController::class, 'index'])->name('self-register.index');
Route::post('/daftar/lookup', [SelfRegisterController::class, 'lookup'])->name('self-register.lookup');
Route::post('/daftar', [SelfRegisterController::class, 'store'])->name('self-register.store');
Route::get('/daftar/berhasil/{appointment}', [SelfRegisterController::class, 'success'])->name('self-register.success');

// AI Triage Endpoint (accessible by both auth and guest, rate limited)
Route::post('/triage', [BookingController::class, 'triage'])->name('booking.triage')->middleware('throttle:10,1');

// WhatsApp OTP Authentication Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [WhatsAppAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login/otp', [WhatsAppAuthController::class, 'requestOtp'])
        ->name('login.otp')
        ->middleware('throttle:5,1'); // Limit to 5 requests per minute
    Route::get('/login/verify', [WhatsAppAuthController::class, 'showVerifyForm'])->name('login.verify');
    Route::post('/login/verify', [WhatsAppAuthController::class, 'verifyOtp'])->name('login.verify.post');
});

Route::post('/logout', [WhatsAppAuthController::class, 'logout'])->name('logout');

// Protected Routes for Patient
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Booking Routes
    Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/book', [BookingController::class, 'store'])->name('booking.store');
});
