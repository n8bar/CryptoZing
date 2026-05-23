@component('mail::message', ['invoice' => $invoice])
# Bitcoin payment detected

Hi {{ $client->name ?? 'there' }},

A Bitcoin payment of **{{ $invoice->formatBitcoinAmount(($payment?->sats_received ?? 0) / \App\Models\Invoice::SATS_PER_BTC) ?? '0' }} BTC** was detected.

@if ($invoice->outstanding_usd > 0)
This appears to be a partial payment — once confirmations settle, **${{ number_format($invoice->outstanding_usd, 2) }}** will still be due on this invoice. We'll follow up if anything else needs your attention.
@else
We're still waiting for the network to confirm — we'll follow up if anything needs your attention.
@endif

The invoice issuer has been notified to review it promptly.

@if ($publicUrl)
@component('mail::button', ['url' => $publicUrl])
View invoice
@endcomponent
@endif

Thanks,<br>
{{ $invoice->user->name ?? config('app.name') }}
@endcomponent
