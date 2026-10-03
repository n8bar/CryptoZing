@php
    $lineAmounts = $invoice->lineAmounts();
@endphp
@if ($invoice->lines->isNotEmpty())
| Description | Qty | Rate | Amount |
|:--|--:|--:|--:|
@foreach ($invoice->lines as $line)
| {{ str_replace('|', '/', $line->description) }} | {{ $line->kind === 'item' ? rtrim(rtrim(number_format((float) $line->quantity, 4, '.', ''), '0'), '.') : '' }} | {{ $line->kind === 'percentage' ? rtrim(rtrim(number_format((float) $line->rate_usd, 4, '.', ''), '0'), '.') . '%' : ($line->kind === 'subtotal' ? '' : '$' . number_format((float) $line->rate_usd, 2)) }} | ${{ number_format((float) ($lineAmounts[$line->id] ?? 0), 2) }} |
@endforeach
| **Total** | | | **${{ number_format((float) $invoice->amount_usd, 2) }}** |
@endif
