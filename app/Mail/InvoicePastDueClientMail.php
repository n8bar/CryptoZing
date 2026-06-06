<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoicePastDueClientMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Invoice $invoice, public InvoiceDelivery $delivery)
    {
    }

    public function envelope(): Envelope
    {
        $number = $this->invoice->number ?? $this->invoice->id;
        $subject = match ($this->slot()) {
            2 => "2nd past-due notice — invoice {$number} is 1 week overdue",
            3 => "3rd past-due notice — invoice {$number} is 2 weeks overdue",
            default => "Reminder: invoice {$number} is past due",
        };

        return new Envelope(
            subject: $subject,
            replyTo: [new Address($this->invoice->user->email, $this->invoice->user->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice-past-due-client',
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
