@component('mail::message', ['invoice' => $invoice])
# Payment confirmed — Invoice {{ $invoice->number ?? $invoice->id }} overpaid

The client's payment is now confirmed on the network, about **{{ number_format($invoice->overpaymentPercent() ?? 0, 1) }}%** above the invoice total.

Decide whether to keep it as a tip, credit the client, or record a manual adjustment/refund.

@component('mail::button', ['url' => route('invoices.show', $invoice)])
Review invoice
@endcomponent

Thanks for using CryptoZing
@endcomponent
