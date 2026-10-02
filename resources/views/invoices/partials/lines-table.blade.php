@php
    $lineAmounts = $invoice->lineAmounts();
    $lineRows = $invoice->lines;
@endphp
@if ($lineRows->isNotEmpty())
<table class="w-full text-sm" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th class="py-1 text-left" style="text-align:left;">Description</th>
            <th class="py-1 text-right" style="text-align:right;">Qty</th>
            <th class="py-1 text-right" style="text-align:right;">Rate</th>
            <th class="py-1 text-right" style="text-align:right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lineRows as $line)
            <tr>
                <td class="py-1 pr-2" style="white-space:pre-line;">{{ $line->description }}</td>
                <td class="py-1 text-right" style="text-align:right;">{{ $line->is_percentage ? '' : rtrim(rtrim(number_format((float) $line->quantity, 4, '.', ''), '0'), '.') }}</td>
                <td class="py-1 text-right" style="text-align:right;">{{ $line->is_percentage ? rtrim(rtrim(number_format((float) $line->rate_usd, 4, '.', ''), '0'), '.') . '%' : '$' . number_format((float) $line->rate_usd, 2) }}</td>
                <td class="py-1 text-right" style="text-align:right;">${{ number_format((float) ($lineAmounts[$line->id] ?? 0), 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
