<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class EmailVerificationPin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $pin
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address - Parkly',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-pin',
            text: 'emails.verification-pin-text',
            with: [
                'name' => $this->user->name,
                'pin' => $this->pin,
                'expires_at' => Carbon::now()->addMinutes(10)->format('H:i:s'),
            ]
        );
    }
} 