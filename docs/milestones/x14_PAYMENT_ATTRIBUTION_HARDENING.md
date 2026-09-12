# MS14 - On-Chain Payment Attribution Hardening

Status: Complete as of 2026-03-26.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting docs: [`docs/PRODUCT_SPEC.md`](../PRODUCT_SPEC.md), [`docs/specs/PAYMENT_CORRECTIONS.md`](../specs/PAYMENT_CORRECTIONS.md), [`docs/specs/PARTIAL_PAYMENTS+CONFIRMATIONS.md`](../specs/PARTIAL_PAYMENTS+CONFIRMATIONS.md), [`docs/specs/WALLET_XPUB_UX_SPEC.md`](../specs/WALLET_XPUB_UX_SPEC.md), [`docs/specs/ONBOARD_SPEC.md`](../specs/ONBOARD_SPEC.md), [`docs/qa/Finding1.md`](../qa/Finding1.md)

This is the milestone execution doc for MS14. It tracks milestone-level objectives plus phase-level progress only.

## Milestone Objectives
- Make invoice attribution key-aware so wallet-key changes do not reuse old receive history or cursors incorrectly.
- Preserve invoice-level key lineage for attribution, auditability, and debugging.
- Detect unsupported shared-wallet reuse without over-flagging legitimate stale-address wrong-invoice cases.
- Reinforce the dedicated receiving-account requirement across wallet setup, onboarding, and help surfaces.
- Provide auditable owner correction tooling for wrongly attributed on-chain payments.

## Current Focus
- MS14 is complete.
- Phase 5 follow-up Browser QA passed on 2026-03-26.
- Canonical Phase 5 requirements: [`docs/specs/PAYMENT_CORRECTIONS.md`](../specs/PAYMENT_CORRECTIONS.md)

## Phase Rollup
1. [x] Phase 1 - Historical Data Risk Mitigation %<8679>
   Reset/reseeded the controlled MS14 baseline with funded `testnet4` scenarios and the duplicate-key collision fixture.
2. [x] Phase 2 - Key Lineage + Cursor Model %<8680>
   Shipped per-key cursor tracking and invoice-bound lineage for invoice creation, reassignment, and watcher flows.
3. [x] Phase 3 - Unsupported Configuration Detection + Flagging %<8681>
   Shipped proactive and evidence-based unsupported-state handling with invoice-level scoping, warning UI, and repair guidance.
4. [x] Phase 4 - Dedicated-Wallet UX Hardening %<8682>
   Shipped dedicated-account guidance across wallet, onboarding, and Helpful Notes, with Browser QA complete.
5. [x] Phase 5 - Correction Tooling + Safeguards %<8683>
   Shipped ignore/restore/reattribute correction tooling, follow-up fixes for validation recovery and reversal/undo flows, and completed the targeted Browser QA rerun.

## Exit Criteria
- [x] False-attribution root cause is structurally mitigated through key-aware lineage and cursor behavior. %<8940>
- [x] Unsupported wallet reuse can be detected and flagged without hard-blocking the owner, while stale-address wrong-invoice cases remain correction work rather than unsupported-wallet evidence by default. %<8941>
- [x] Wallet, onboarding, and help UX clearly communicate the dedicated receiving-account requirement. %<8942>
- [x] Owners and operators can recover from wrong-invoice attribution through auditable correction tooling. %<8943>
- [x] Verification reproduces the historical failure mode and confirms the supported flagging and recovery paths end-to-end. %<8944>
