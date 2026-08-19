# Partial Payments & Confirmations — Audit, Test Plan, and Implementation Notes

Companion to [`docs/specs/PARTIAL_PAYMENTS+CONFIRMATIONS.md`](../specs/PARTIAL_PAYMENTS+CONFIRMATIONS.md), which is canonical for required behavior. This doc holds what that one should not: verification status, the test plan, mechanics, and open questions.

## Verification status

Audited 2026-08-19 against the code. The spec previously carried these as a ✅ "Completed Tasks" list; three were false, which is how an unenforced settlement rule reached mainnet.

| # | Claim | Verdict |
|---|---|---|
| 1 | `invoice_payments` stores every tx with sats + USD snapshot per detection | True |
| 2 | Watcher records multiple partials and refreshes status/outstanding automatically | True |
| 3 | UI shows payment history, USD-first summary, QR targeting outstanding balance | True |
| 4 | ±100 sat tolerance enforced; detection/confirmation timestamps surface in payment history | **Was false in both halves** |
| 5 | History rows show captured rate/fiat amount; issuers can annotate payments | True |
| 6 | Automatic invoice delivery + paid receipt emails, queue-backed, with profile toggles | **Partially false** |
| 7 | Manual adjustments + 15% over/under alerts to issuer and client, gratuity copy | True |
| 8 | Proactive partial-payment alerts: one-time warning after the second payment attempt | **Partially false** |

**Item 4.** The tolerance was never wired: `Invoice::PAYMENT_SAT_TOLERANCE` was declared and read nowhere, so settlement compared confirmed USD to expected USD with no slack. A real mainnet invoice sat `partial` on a 12.89 sat shortfall (#156, fixed in PR #160). Per-row confirmation timestamps still do not surface — the history table shows a Detected column and only the word `Confirmed`/`Pending` per row.

**Item 6.** Neither invoice delivery nor the client receipt is automatic; both are issuer-initiated. The only automatic reaction to `InvoicePaid` is the issuer notice. `auto_receipt_emails` exists as a column, fillable entry, and cast with zero consumers — the same dead-declaration pattern as item 4. Tracked in #159.

**Item 8.** Acknowledgments fire on every new payment including the first; there is no second-payment counter and no partial-payment warning delivery type. The dedupe keys on txid rather than invoice. Tracked in #159.

**Coverage gaps found in the same pass**

- No test asserts the watcher persists `usd_rate` / `fiat_amount`. Fixtures use a `btc_rate` identical to the faked cache rate, so `paymentFiatValue()`'s fallback produces the same number if the snapshot write were removed.
- `tests/Feature/PublicShareTest.php` asserts `assertDontSee('Send one payment:')` — a string with a colon that appears in no view, so it passes vacuously.
- The zero-balance QR rule (omit `amount` when nothing is outstanding) is implemented but unreachable: outstanding reaches zero only when the invoice is `paid`, and both views hide the QR at `paid`.

## Open questions

- Should payments to a `draft` invoice be blocked rather than recorded? The spec records them, because each invoice address is unique and money that arrives is money that arrived. Carried from the original spec as an unresolved TBD.

## Test plan

- Unit tests for `Invoice` accessors: paid/confirmed USD and sats, outstanding USD/BTC, status transitions.
- Watcher feature tests:
    - First partial payment logged, USD reduced at the payment's rate.
    - Multiple partials summing to paid across different rates.
    - Confirmations updating existing payment rows.
    - Overpayments flagged but still marking the invoice `paid`.
    - Unconfirmed payment does not mark paid, then flips once confirmations meet the threshold.
    - Shortfall inside the tolerance settles; shortfall beyond it stays `partial`.
    - RBF replacement removes the old live tx from totals without double-counting.
    - Dropped unconfirmed tx shrinks totals and reverts status.
- Manual adjustment reversal: the reveal/collapse control flow, equal-and-opposite row creation with the `reversal of {txid}` note, and invoice/payment/alert recomputation afterward.
- Blade tests / snapshots for the payment history table and public view.

## Implementation notes

Mechanics, kept out of the spec. The code is the authority; these describe the shape it took.

**Ledger storage.** `invoice_payments` carries `id`, `invoice_id`, `txid`, `vout_index`, `sats_received`, `detected_at`, `confirmed_at`, `block_height`, `usd_rate`, `fiat_amount`, and an optional `raw_tx` JSON column. A unique index on (`invoice_id`, `txid`) is what prevents double-counting.

**Watcher pass.** Fetch current transactions for the invoice address; record or update a row per txid; delete stored unconfirmed rows no longer present in the mempool; recompute sats, per-payment USD, outstanding, and status; then dispatch any alerts. Multiple outputs of one transaction to the same address are summed into one row.

**Rate acquisition.** Use the cached BTC/USD rate when fresh (within TTL), otherwise refresh. `fiat_amount = sats_received / 1e8 * usd_rate`. The rate is written only when absent, so a payment keeps the rate it was first priced at; later passes recompute `fiat_amount` from the stored rate.

**Tolerance conversion.** 100 sats valued at the rate that priced the money which arrived — the most recent confirmed payment's `usd_rate`, falling back to the invoice's own rate when nothing is confirmed.

**UI surfaces.** Invoice show page: payment summary card (expected vs paid vs outstanding, BTC + USD), payment history table with one row per payment, underpayment alerts. Print/public view mirrors the history in condensed form, with the paid watermark only at `paid` and an outstanding-balance note otherwise. Manual adjustment reversal is a two-click inline control: the first click reveals a confirm control, clicking again before confirming re-hides it.

**API.** No JSON API exposes invoices today. If one is added, payment fragments and totals belong in it.

## Deferred

- Per-user required-confirmations setting (1–6) used by the watcher, with app-default fallback. Post-open-beta.
