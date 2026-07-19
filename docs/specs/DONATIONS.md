# Donations

Spec for the public donation page — CryptoZing's first revenue surface.

## Problem
CryptoZing has no revenue surface; the monetization posture ("leave room, promise nothing") stays theoretical. A donation page is the low-stakes entry: it accepts support without introducing fees, tiers, or pricing commitments.

## Scope
- A public, guest-safe donation page in the app.
- Donor picks a USD preset or enters a custom USD amount; the page converts to BTC at the live rate under the same rate standard as invoices.
- Each donor session gets its own receive address, not shared with other donors; the address stays with that session until it sees payment.
- The address is shown as a copyable string and a QR code that encodes the chosen amount.
- When payment is seen on the address, the page shows a thank-you state.
- The flow is anonymous end to end: no personal data is requested or stored.
- Donation funds go to a CryptoZing-controlled wallet wholly separate from every user's wallet; the app's watch-only rule holds (no private keys anywhere).
- On-page disclaimer: donations support CryptoZing LLC, are non-refundable, and are not tax-deductible.
- When a donation is seen, a notification email goes to a configured operator address. Donors receive no mail.
- Donations never appear in user-facing invoice surfaces, stats, or mail.

## Out of Scope
- Fiat donations (BACKLOG item 25).
- Email receipts or any donor contact collection.
- Recurring donations and Lightning payments.
