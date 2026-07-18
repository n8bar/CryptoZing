# MS12 - Payment & Address Accuracy

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-12-01 through 2025-12-06.
Canonical requirements descendant: [`docs/specs/PARTIAL_PAYMENTS.md`](../specs/PARTIAL_PAYMENTS.md)

> Reconstructed on 2026-07-18 from the inserted bug-fix milestone, implementation commits, the original confirmation strategy, tests, live testnet verification notes, historical PLAN, and the changelog. The four phases below describe evidence-backed implementation clusters; they were not named or checked off as formal phases at the time.

## Objective

Repair invoice addresses derived from the wrong branch, make settlement depend on confirmed USD value, clean up dropped unconfirmed payments, and provide an explicit auditable way to settle tiny residual balances.

## Reconstruction basis

MS12 was inserted ahead of UX Overhaul in `fd27880` and labeled a bug fix in `c4c4e85`. Address repair tooling and external-chain derivation tests landed in `05e22a7`; confirmation-aware settlement and the first small-balance control landed in `5b8a6f9`; threshold, notification, and residual-sats follow-ups landed in `9f6c9ca`, `f407ec6`, and `684dc56`; and `fca7d46` plus `a0c5dc7` recorded testnet verification and milestone completion.

## Phase Rollup

### [x] Phase 1 - Address Derivation Audit & Repair

Prove the derivation mismatch, lock external-chain/network behavior with known vectors, and add inspectable tooling to reassign affected invoice addresses safely. See [`x12.1_ADDRESS_DERIVATION_AUDIT_REPAIR.md`](../strategies/x12.1_ADDRESS_DERIVATION_AUDIT_REPAIR.md).

### [x] Phase 2 - Confirmation-Aware Settlement

Make confirmed per-payment USD value authoritative for invoice state, retain unconfirmed activity as pending, and remove dropped unconfirmed rows before recomputing totals. See [`x12.2_CONFIRMATION_AWARE_SETTLEMENT.md`](../strategies/x12.2_CONFIRMATION_AWARE_SETTLEMENT.md).

### [x] Phase 3 - Small-Balance Resolution

Display exact residuals, expose an owner-initiated audited credit for eligible small balances, prevent misleading follow-up alerts, and clamp payment requests after USD settlement. See [`x12.3_SMALL_BALANCE_RESOLUTION.md`](../strategies/x12.3_SMALL_BALANCE_RESOLUTION.md).

### [x] Phase 4 - Testnet Verification & Closeout

Verify corrected derivations against stored public keys, run targeted watcher checks on repaired invoices, record the outcomes, and move MS12 to completed milestones. See [`x12.4_TESTNET_VERIFICATION_CLOSEOUT.md`](../strategies/x12.4_TESTNET_VERIFICATION_CLOSEOUT.md).

## Exit Criteria

- [x] BIP84 external-chain derivation is locked by known testnet and mainnet vectors and rejects a network mismatch.
- [x] A dry-run-first command can inspect and selectively repair existing invoice addresses.
- [x] Paid/partial invoices require explicit options before their addresses or payment history can be reset.
- [x] Unconfirmed payments produce pending state and cannot settle an invoice.
- [x] Confirmed per-payment USD totals drive partial and paid state.
- [x] Missing unconfirmed transactions are removed before totals and state are recomputed.
- [x] Eligible small balances require explicit owner action and leave an adjustment-ledger record.
- [x] Corrected testnet invoices pass derivation comparison and targeted watcher sanity checks.

## Historical boundary

MS12 corrected the immediate address branch and payment-state defects, but it still assumed one current wallet key/cursor and did not solve historical key lineage or address reuse; MS14 addressed those problems. The explicit replacement-transaction scenario was specified, while the contemporaneous watcher test directly proved dropped-unconfirmed cleanup. Later changes further evolved receipt policy, payment corrections, and manual adjustment reversal.
