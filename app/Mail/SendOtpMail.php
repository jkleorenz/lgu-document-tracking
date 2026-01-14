<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SendOtpMail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $otp
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Password Reset OTP - LGU Document Tracking',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.send-otp',
            with: [
                'user' => $this->user,
                'otp' => $this->otp,
            ],
        );
    }
}
