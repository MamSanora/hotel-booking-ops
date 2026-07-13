<?php

namespace App\Mail;

use App\Models\GuestAuth;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * GuestPasswordRecoveryOtp
 *
 * Sends the 6-digit OTP to a guest who has initiated a password recovery.
 * When MAIL_MAILER=log (development), the email body is written to laravel.log.
 * When MAIL_MAILER=smtp (production), it is sent via the configured SMTP server.
 */
class GuestPasswordRecoveryOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly GuestAuth $guestAuth,
        public readonly string $otpCode,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Password Recovery Code — Dara Meas Hotel',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.guest.password-recovery-otp',
        );
    }
}
