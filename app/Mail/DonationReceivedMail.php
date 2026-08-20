<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationReceivedMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Donation $donation)
    {
    }

    public function envelope(): Envelope
    {
        // SerializesModels re-fetches the donation at send time, so the
        // subject reflects its confirmation state when the mail goes out.
        $state = $this->donation->status === 'paid' ? 'received' : 'seen (unconfirmed)';

        return new Envelope(
            subject: 'Donation ' . $state . ' — ' . number_format((int) $this->donation->sats_received) . ' sats',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.donation-received',
            with: [
                'donation' => $this->donation,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
