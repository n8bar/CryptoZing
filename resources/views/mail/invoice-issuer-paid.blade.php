@php
    $settlementPayments = $settlementPayments ?? collect();
@endphp

@component('mail::message', ['invoice' => $invoice])
# Invoice {{ $invoice->number ?? $invoice->id }} paid

Good news — this invoice is now marked **paid**.

- **Client:** {{ $invoice->client->name ?? 'N/A' }}
- **Amount:** ${{ number_format($invoice->amount_usd ?? 0, 2) }} ({{ $invoice->amount_btc ?? '—' }} BTC)
- **Paid at:** {{ optional($invoice->paid_at)->format('D, M j, Y g:i:s A') ?? now()->format('D, M j, Y g:i:s A') }}

@if ($settlementPayments->isNotEmpty())
**On-chain settlement:**

@foreach ($settlementPayments as $payment)
- <span style="word-break: break-all; font-family: monospace;">{{ $payment->txid }}</span> — {{ number_format($payment->sats_received) }} sats / ${{ number_format((float) $payment->fiat_amount, 2) }}
@endforeach
@endif

@component('mail::button', ['url' => route('invoices.show', $invoice)])
Review invoice
@endcomponent

Thanks for using CryptoZing
@endcomponent
