@component('mail::message')
# Donation received

A donation just landed on the donation wallet.

- **Amount:** {{ number_format((int) $donation->sats_received) }} sats
- **Requested (USD):** ${{ number_format((float) $donation->usd_amount_requested, 2) }}
- **Address:** <span style="word-break: break-all; font-family: monospace;">{{ $donation->address }}</span>
- **Txid:** <span style="word-break: break-all; font-family: monospace;">{{ $donation->txid }}</span>
- **Network:** {{ $donation->network }}
- **Seen at:** {{ optional($donation->paid_at)->format('D, M j, Y g:i:s A') ?? now()->format('D, M j, Y g:i:s A') }}

Thanks for using CryptoZing
@endcomponent
