<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\WhatsAppAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{article:slug}', [BlogController::class, 'show'])->name('blog.show');

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
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Booking Routes
    Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

    // AI Triage Endpoint (requires auth, rate limited to 10 req/min per user)
    Route::post('/triage', [BookingController::class, 'triage'])->name('booking.triage')->middleware('throttle:10,1');
});
