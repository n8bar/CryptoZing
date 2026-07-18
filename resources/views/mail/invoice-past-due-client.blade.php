@php
    $summary = $invoice->paymentSummary(\App\Services\BtcRate::current());
    $outstandingUsd = $summary['outstanding_usd'] ?? 0;
    $slot = $slot ?? 1;
    $number = $invoice->number ?? $invoice->id;
@endphp

@component('mail::message', ['invoice' => $invoice])
@if ($slot === 3)
# 3rd past-due notice — invoice {{ $number }} is 2 weeks overdue

This is the third past-due notice. Invoice {{ $number }} has been overdue for two weeks with an outstanding balance of **${{ number_format($outstandingUsd, 2) }} USD** that remains unresolved.

Please remit payment promptly. If you've already paid or there's a reason for the delay, reply to this email so we can resolve it.
@elseif ($slot === 2)
# 2nd past-due notice — invoice {{ $number }} is 1 week overdue

Following up — invoice {{ $number }} has now been past due for a week with an outstanding balance of **${{ number_format($outstandingUsd, 2) }} USD**.

Please settle the balance or reply to this email to confirm payment status.
@else
# Reminder: invoice {{ $number }} is past due

Our records show an outstanding balance of **${{ number_format($outstandingUsd, 2) }} USD**.

Please review the invoice and settle the remaining amount. If you already paid, just reply to this email so we can reconcile it.
@endif

@component('mail::button', ['url' => $invoice->public_url])
View invoice
@endcomponent

Thank you!
@endcomponent
