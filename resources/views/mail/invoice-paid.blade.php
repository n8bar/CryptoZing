@php
    $settlementPayments = $settlementPayments ?? collect();
    $multiplePayments = $settlementPayments->count() > 1;
@endphp

<x-mail::message :invoice="$invoice">
# Receipt for Invoice {{ $invoice->number ?? $invoice->id }}

Hi {{ $client->name ?? 'there' }},

Your payment is confirmed. **${{ number_format((float) $invoice->amount_usd, 2) }} USD** received{{ $multiplePayments ? ' across ' . $settlementPayments->count() . ' on-chain payments' : '' }}.

@if ($settlementPayments->isNotEmpty())
<x-mail::panel>
@foreach ($settlementPayments as $payment)
**TXID:** <span style="word-break: break-all; font-family: monospace;">{{ $payment->txid }}</span>
{{ number_format($payment->sats_received) }} sats / ${{ number_format((float) $payment->fiat_amount, 2) }} (confirmed {{ optional($payment->confirmed_at)->toDayDateTimeString() }})

@endforeach
</x-mail::panel>
@endif

@if ($publicUrl)
<x-mail::button :url="$publicUrl">
View Paid Invoice
</x-mail::button>
@endif

Thanks,<br>
{{ $invoice->user->name ?? config('app.name') }}
</x-mail::message>
