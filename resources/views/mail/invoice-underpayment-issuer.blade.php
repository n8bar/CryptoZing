@component('mail::message', ['invoice' => $invoice])
# Payment confirmed — Invoice {{ $invoice->number ?? $invoice->id }} underpaid

The client's payment is now confirmed on the network, leaving **${{ number_format($invoice->outstanding_usd, 2) }}** still outstanding.

Consider following up with the client or recording a manual adjustment if you've already reconciled it elsewhere.

@component('mail::button', ['url' => route('invoices.show', $invoice)])
Review invoice
@endcomponent

Thanks for using CryptoZing
@endcomponent
