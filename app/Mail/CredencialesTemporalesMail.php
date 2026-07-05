<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesTemporalesMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public string $passwordTemporal,
        public string $motivo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Credenciales de acceso - ISTV Vilcanota',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.credenciales-temporales',
        );
    }
}
