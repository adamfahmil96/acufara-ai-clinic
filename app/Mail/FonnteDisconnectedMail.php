<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FonnteDisconnectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private array $status
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ALERT] Fonntee WhatsApp Gateway Terputus',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fonnte-disconnected',
            with: [
                'fonnteConnected' => $this->status['connected'],
                'fonnteMessage' => $this->status['message'],
                'fonnteCheckedAt' => $this->status['checked_at'],
                'fonnteAppUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
