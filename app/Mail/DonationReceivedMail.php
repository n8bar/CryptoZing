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
        return new Envelope(
            subject: 'Donation received — ' . number_format((int) $this->donation->sats_received) . ' sats',
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
