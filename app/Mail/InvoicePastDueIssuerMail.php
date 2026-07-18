<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePastDueIssuerMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Invoice $invoice, public InvoiceDelivery $delivery)
    {
    }

    public function envelope(): Envelope
    {
        $number = $this->invoice->number ?? $this->invoice->id;
        $subject = match ($this->slot()) {
            2 => "2nd past-due notice sent — invoice {$number} 1 week overdue",
            3 => "3rd past-due notice sent — invoice {$number} 2 weeks overdue",
            default => "Reminder sent: invoice {$number} past due",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice-past-due-issuer',
            with: [
                'invoice' => $this->invoice,
                'slot' => $this->slot(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function slot(): int
    {
        if (preg_match('/^past_due_(\d+)$/', (string) ($this->delivery->context_key ?? ''), $m)) {
            return (int) $m[1];
        }

        return 1;
    }
}
