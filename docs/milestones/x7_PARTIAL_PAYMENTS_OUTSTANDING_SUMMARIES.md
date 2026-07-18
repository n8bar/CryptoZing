# MS7 - Partial Payments & Outstanding Summaries

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-11-13 through 2025-11-17.
Canonical requirements descendant: [`docs/specs/PARTIAL_PAYMENTS.md`](../specs/PARTIAL_PAYMENTS.md)

> Reconstructed on 2026-07-18 from the prospective partial-payments spec, implementation commits, historical PLAN snapshots, tests, and the changelog. The three phases below describe evidence-backed implementation clusters; they were not named or closed as formal phases at the time.

## Objective

Replace the one-payment invoice summary with an append-only payment ledger, represent partial settlement truthfully, and make every invoice surface target and explain the remaining balance.

## Reconstruction basis

The prospective spec landed in `219e489`; the ledger and status foundation landed in `b1ec935`; payment history and outstanding-balance presentation landed in `fe84d67`; and the legacy-payment backfill, payment notes, and final completed PLAN rollup landed in `dd8092e`. The changelog recorded the core milestone complete on 2025-11-14.

## Phase Rollup

### [x] Phase 1 - Payment Ledger & Partial Status

Persist one row per detected transaction, capture a USD snapshot, aggregate received sats, and transition eligible invoices through partial and paid states. See [`x7.1_PAYMENT_LEDGER_PARTIAL_STATUS.md`](../strategies/x7.1_PAYMENT_LEDGER_PARTIAL_STATUS.md).

### [x] Phase 2 - Outstanding Balance Presentation

Add USD-first payment summaries, transaction history, and outstanding-targeted BIP21/QR output across authenticated, print, and public invoice surfaces. See [`x7.2_OUTSTANDING_BALANCE_PRESENTATION.md`](../strategies/x7.2_OUTSTANDING_BALANCE_PRESENTATION.md).

### [x] Phase 3 - Legacy Backfill, Notes & Handoff

Backfill legacy invoice-level transaction metadata into the ledger, add owner notes, and verify the completed ledger model before handing it to delivery and later correctness work. See [`x7.3_LEGACY_BACKFILL_NOTES_HANDOFF.md`](../strategies/x7.3_LEGACY_BACKFILL_NOTES_HANDOFF.md).

## Exit Criteria

- [x] Every detected invoice transaction can be represented by a distinct `invoice_payments` row.
- [x] Payment rows preserve sats, transaction identity, detection/confirmation metadata, and the detection-time USD snapshot.
- [x] Invoice state and outstanding totals refresh after payment detection.
- [x] Show, print, and public output expose payment history and the remaining balance.
- [x] BIP21 and QR output target the remaining balance instead of the original total.
- [x] Legacy invoice-level payment metadata can be migrated without duplicating existing ledger rows.

## Historical boundary

MS7's original settlement calculation counted detected sats and could mark an unconfirmed total paid; MS12 replaced that with confirmation-gated, USD-canonical settlement. Manual adjustments and proactive notification work landed as same-era follow-ups and was later folded into the evolving partial-payments spec, but those additions were not required to close the core MS7 ledger-and-summary milestone.
