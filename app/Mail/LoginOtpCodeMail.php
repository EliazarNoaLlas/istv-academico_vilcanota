<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginOtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public string $codigo,
        public int $minutosExpiracion,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de verificación - ISTV Vilcanota',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.login-otp',
        );
    }
}
