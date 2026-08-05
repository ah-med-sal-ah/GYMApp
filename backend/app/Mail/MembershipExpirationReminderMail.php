<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Gym;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * This Mailable is dispatched from within {@see \App\Jobs\SendMembershipReminderJob},
 * which is itself queued. It is intentionally sent synchronously (Mail::send) from
 * that job so delivery, retries and failure handling all live in a single place.
 */
class MembershipExpirationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Client $client,
        public readonly Gym $gym,
        public readonly int $remainingDays,
    ) {}

    /**
     * Get the message envelope.
     *
     * The email is sent on behalf of the authenticated Gym, not a global
     * system address, so each client sees their own gym as the sender.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->gym->email, $this->gym->name),
            subject: 'YourGYM – Your Membership Will Expire Soon',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.membership-reminder',
            with: [
                'client' => $this->client,
                'gym' => $this->gym,
                'remainingDays' => $this->remainingDays,
            ],
        );
    }
}
