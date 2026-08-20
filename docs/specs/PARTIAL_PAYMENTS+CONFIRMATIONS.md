# Partial Payments, Confirmations & Adjustment Spec

This doc is canonical for:
- the payment ledger
- outstanding-balance math
- confirmation-gated status behavior
- RBF and dropped-transaction cleanup
- manual adjustments and over/underpayment handling

Ignore/restore correction handling for wrongly attributed on-chain rows is defined separately in [`docs/specs/PAYMENT_CORRECTIONS.md`](PAYMENT_CORRECTIONS.md). That flow preserves original `invoice_payments` rows for audit and is distinct from manual adjustments.

Verification status, the test plan, mechanics, and open questions live in [`docs/qa/AUDIT_PARTIAL_PAYMENTS+CONFIRMATIONS.md`](../qa/AUDIT_PARTIAL_PAYMENTS+CONFIRMATIONS.md). This doc states required behavior only.

## Goals
- Record every on-chain payment that hits an invoice address, even if the amount is below the invoice total.
- Surface a `partial` status so issuers and clients know funds have arrived but more is due.
- Preserve the BTC/USD rate at the moment each payment is detected, so receipts and statements reflect what the money was worth when it arrived.
- Carry enough payment history for receipts, delivery logs, and dashboards to reference.

## Ledger
- Every payment to an invoice address is recorded individually, with the sats received and the USD value at the moment it was first detected. The same transaction is never counted twice; multiple outputs of one transaction to the same address count once, summed.
- `amount_usd` is canonical. The BTC amount shown when an invoice is created is a display snapshot — expected and outstanding are USD.
- Outstanding USD is expected minus confirmed value, with each payment valued at its own captured rate. Outstanding BTC is derived from the latest available rate at view time, never locked at creation.
- Payments that arrive while an invoice is still `draft` are recorded when they arrive. Money that reached the address is tracked whatever the invoice's status; the status governs only what is displayed.

## Tolerance
- ±100 sats, valued at the payment's captured rate, applies wherever confirmed value is compared against expected.
- It absorbs rounding, not residuals. The small-balance control starts at $1.00 and owns everything above the noise.

## Status Flow
- `sent`: no payments detected.
- `pending`: unconfirmed payments detected, awaiting confirmations.
- `partial`: confirmed value received but still below expected less tolerance.
- `paid`: confirmed value meets or exceeds expected less tolerance, once the confirmation threshold is satisfied.
- `paid_at` is the settlement timestamp — the `confirmed_at` of the confirmation that most recently crosses the cumulative confirmed total from below the expected total to at-or-above it: the time everyone finally agreed the invoice was paid. Among surviving confirmed payments the cumulative is monotonic, so this is the payment that first reaches the expected total; a later, redundant payment is not the crossing.
- `paid_at` is set only on a confirmed transition, and cleared if the invoice leaves `paid`.

## Confirmation Gate
- Required confirmations scale with the invoice's value at creation: higher-value invoices require more confirmations before a payment counts as confirmed.
- Tier boundaries and their confirmation counts are operator-configurable.
- Donations confirm at the fewest-confirmations tier.

## RBF and Dropped Transactions
- A replacement supersedes the transaction it replaces rather than adding to it.
- An unconfirmed transaction that is no longer in the mempool stops counting toward the invoice. A confirmed transaction is never dropped.
- Totals, outstanding balance, and status always reflect the surviving set.
- Dropped and replaced transactions are logged. Notifying the issuer about them is optional.

## USD Snapshot
- The rate captured when a payment is first detected is the rate that payment keeps. BTC volatility never retroactively changes settled USD, and multiple partials can each carry a different rate.
- Each payment reduces the outstanding USD balance at its own captured rate, so issuers see dollars knocked off as of the moment funds arrived.
- Issuer and public summaries present USD first (`Expected: $500.00 (0.0123 BTC)`, `Outstanding: $125.00 (~0.0031 BTC at current rate)`). Outstanding shows the exact residual, with no display-side tolerance masking.
- Payment requests target the current outstanding USD balance converted at the latest available rate at view time. Once nothing is outstanding, the request carries no amount.

## Payment Visibility
- Issuers see every payment that has arrived: amount, transaction, detection and confirmation times, and the USD value it was captured at.
- Clients see the same history on the public view, and an outstanding-balance note until the invoice is paid. The paid watermark appears only at `paid`.
- Issuers can annotate any payment with a short note.

## Interactions with Invoice Delivery
- Receipt emails enumerate the payments and the total settled amount.
- The delivery log records which notices fired and when, including whether a receipt followed full payment or a partial-payment update.

## Small Balance Resolution
- Outstanding USD and BTC display the exact residual, with no UI masking for dust.
- When the residual is below the small-balance threshold, issuers get an explicit "Resolve small balance" control that records a manual credit adjustment for the remaining USD at the latest available rate and marks the invoice paid. The adjustment is logged as an `is_adjustment` row for auditability. Threshold: `max($1.00, min(1% of expected USD, $50.00 cap))`.
- Residuals are never auto-settled. Issuers opt in via the control.

## Manual Adjustments and Reversal
- Manual adjustments are append-only ledger entries. Issuers cannot edit or delete a recorded amount or direction in place.
- Recording an adjustment that would push the settled total past the client overpayment-alert threshold requires an explicit issuer confirmation that states the amount by which the total would exceed the invoice.
- Reversing an adjustment creates a second adjustment on the same invoice with equal-and-opposite values, cancelling the original accounting effect exactly.
- Reversal rows preserve the original adjustment's USD/BTC snapshot rather than repricing at the latest rate, and carry a `reversal of {txid}` note naming the row they cancel.
- Both rows stay visible in payment history. Reversal is the supported issuer-facing correction path for manual adjustments.

## Over and Underpayment
- A surplus is recorded and treated as a tip by default, and the invoice stays `paid`.
- Surplus within noise (≤ $10 USD equivalent or ≤ 1% of the invoice) appears in payment history without alerts.
- Surplus beyond that is flagged to the issuer, and client messaging makes clear that overpayments are treated as gratuities unless the sender says otherwise. Issuer guidance may suggest a refund or credit when a surplus looks accidental, without being prescriptive about intentional tips.
- Over and underpayments beyond 15% of the invoice total alert both the issuer and the client.
- Over/underpayment alerts judge the client's payments. An issuer ledger adjustment is not a client payment: an adjustment may clear an alert condition, but never creates one and never generates client mail.
- Client-facing payment alerts describe only what is true of the payment at the time they are sent, including whether it is confirmed.
