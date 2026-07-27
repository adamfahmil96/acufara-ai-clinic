<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Jaring pengaman jika notifikasi booking via Telegram gagal terkirim.
 *
 * Sengaja berupa Mailable, bukan Mail::raw(): Mail::raw() adalah no-op di bawah
 * Mail::fake() sehingga jalur ini tidak akan pernah bisa diuji — buruk untuk
 * kode yang justru hanya berjalan ketika sesuatu sudah rusak.
 *
 * Tidak memakai Queueable: tidak ada queue worker di Cloud Run, sehingga email
 * yang di-queue akan menumpuk di tabel jobs tanpa pernah terkirim.
 */
class BookingNotificationFallbackMail extends Mailable
{
    public function __construct(
        public Appointment $appointment,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Acufara] Booking Baru #{$this->appointment->id} (notifikasi Telegram gagal)",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.booking-notification-fallback',
            with: ['body' => $this->body],
        );
    }
}
