@component('mail::message')
@if ($donation->status === 'paid')
# Donation received

A donation just landed on the donation wallet.
@else
# Donation seen — awaiting confirmation

A payment to the donation wallet is in the mempool and has not confirmed yet. The donation settles on its own at the confirmation gate.
@endif

- **Amount:** {{ number_format((int) $donation->sats_received) }} sats
- **Requested:** {{ $donation->requestedAmountLabel() }}
- **Address:** <span style="word-break: break-all; font-family: monospace;">{{ $donation->address }}</span>
- **Txid:** <span style="word-break: break-all; font-family: monospace;">{{ $donation->txid }}</span>
- **Network:** {{ $donation->network }}
- **Seen at:** {{ optional($donation->paid_at)->format('D, M j, Y g:i:s A') ?? now()->format('D, M j, Y g:i:s A') }}

Thanks for supporting CryptoZing
@endcomponent
