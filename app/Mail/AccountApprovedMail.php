<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when a support agent approves a pending alpha account. Queued so the
 * approve action never blocks on SMTP.
 */
class AccountApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your '.config('app.name').' account is approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.account-approved',
            with: [
                'user' => $this->user,
                // Public host so the link never points at an internal hostname.
                'loginUrl' => rtrim(config('app.public_url', config('app.url')), '/').'/login',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
