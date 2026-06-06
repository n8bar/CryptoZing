@component('mail::message', ['invoice' => $invoice])
# Payment confirmed — Invoice {{ $invoice->number ?? $invoice->id }} overpaid

Your payment is now confirmed on the network and came in about **{{ number_format($invoice->overpaymentPercent() ?? 0, 1) }}%** above the invoice total.

Overpayments are treated as gratuities by default, so please reply if this was accidental and we’ll coordinate a refund or credit.

@component('mail::button', ['url' => $invoice->public_url])
View invoice
@endcomponent

Thanks for your business!
@endcomponent
