<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceIssuerPaidNoticeMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Invoice $invoice, public InvoiceDelivery $delivery)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . ($this->invoice->number ?? $this->invoice->id) . ' paid',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice-issuer-paid',
            with: [
                'invoice' => $this->invoice,
                'settlementPayments' => $this->invoice->payments()
                    ->whereNotNull('confirmed_at')
                    ->whereNull('ignored_at')
                    ->where('is_adjustment', false)
                    ->orderBy('confirmed_at')
                    ->get(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
