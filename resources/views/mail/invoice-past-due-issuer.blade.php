@php
    $summary = $invoice->paymentSummary(\App\Services\BtcRate::current());
    $outstandingUsd = $summary['outstanding_usd'] ?? 0;
    $slot = $slot ?? 1;
    $number = $invoice->number ?? $invoice->id;
@endphp

@component('mail::message', ['invoice' => $invoice])
@if ($slot === 3)
# 3rd past-due notice sent — invoice {{ $number }} 2 weeks overdue

Your client has now received the third past-due notice. The invoice has been overdue for two weeks with no payment recorded.

- **Client:** {{ $invoice->client->name ?? 'N/A' }}
- **Due date:** {{ optional($invoice->due_date)->toDateString() ?? '—' }}
- **Outstanding:** ${{ number_format($outstandingUsd, 2) }}

This may warrant direct outreach, a manual adjustment, or escalation outside the platform.
@elseif ($slot === 2)
# 2nd past-due notice sent — invoice {{ $number }} 1 week overdue

Your client has now received a second past-due notice. The invoice has been overdue for a week.

- **Client:** {{ $invoice->client->name ?? 'N/A' }}
- **Due date:** {{ optional($invoice->due_date)->toDateString() ?? '—' }}
- **Outstanding:** ${{ number_format($outstandingUsd, 2) }}

Consider following up with the client directly or recording a manual adjustment if you've already reconciled this elsewhere.
@else
# Reminder sent: invoice {{ $number }} past due

A past-due reminder went out to your client.

- **Client:** {{ $invoice->client->name ?? 'N/A' }}
- **Due date:** {{ optional($invoice->due_date)->toDateString() ?? '—' }}
- **Outstanding:** ${{ number_format($outstandingUsd, 2) }}

Consider nudging the client directly if you haven't already.
@endif

@component('mail::button', ['url' => route('invoices.show', $invoice)])
Open invoice
@endcomponent

Thanks for using CryptoZing
@endcomponent
