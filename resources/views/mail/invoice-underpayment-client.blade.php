@component('mail::message', ['invoice' => $invoice])
# Payment confirmed — balance still due on Invoice {{ $invoice->number ?? $invoice->id }}

Your payment is now confirmed on the network, but **${{ number_format($invoice->outstanding_usd, 2) }}** remains outstanding on this invoice.

Please use the button below to view the invoice and send the remaining amount. If you believe this is in error, reply to this email.

@component('mail::button', ['url' => $invoice->public_url])
Pay remaining balance
@endcomponent

Thank you!
@endcomponent
