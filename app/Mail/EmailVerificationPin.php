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
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

final class EmailVerificationPin extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

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
        // Add MailerSend personalization
        $this->mailersend(
            template_id: null,
            tags: ['verification', 'pin'],
            personalization: [
                new Personalization($this->user->email, [
                    'name' => $this->user->name,
                    'pin' => $this->pin,
                    'expires_at' => Carbon::now()->addMinutes(10)->format('H:i:s'),
                ])
            ],
        );

        return new Content(
            view: 'emails.verification-pin',
            text: 'emails.verification-pin-text'
        );
    }
} 