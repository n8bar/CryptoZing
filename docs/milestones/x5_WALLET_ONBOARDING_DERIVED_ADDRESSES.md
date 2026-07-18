# MS5 - Wallet Onboarding & Derived Addresses

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-11-07 through 2025-11-08.
Current UX requirements: [`docs/specs/WALLET_XPUB_UX_SPEC.md`](../specs/WALLET_XPUB_UX_SPEC.md)

> Reconstructed on 2026-07-18 from git history, historical PLAN, and the changelog. The two phases below describe evidence-backed implementation clusters; they were not named or closed as formal phases at the time.

## Objective

Move invoice payment destinations from manually entered addresses to watch-only wallet onboarding and a unique BIP84-derived receive address for each invoice, then migrate legacy invoices onto that model.

## Reconstruction basis

Runtime onboarding and derivation landed in `10d257e` on `codex/phase-a-wallet`; legacy backfill and handoff landed in `19a05bf`. Historical PLAN and the 2025-11-08 changelog entry record `/wallet/settings`, Node-based derivation, and the backfill command as the completed milestone outcome.

## Phase Rollup

### [x] Phase 1 - Wallet Onboarding & Runtime Derivation

Add per-user watch-only wallet settings, derive a BIP84 receive address during invoice creation, and advance the wallet cursor transactionally. See [`x5.1_WALLET_ONBOARDING_RUNTIME_DERIVATION.md`](../strategies/x5.1_WALLET_ONBOARDING_RUNTIME_DERIVATION.md).

### [x] Phase 2 - Legacy Backfill & Handoff

Add dry-run-capable tooling and public-key-only fixtures to assign derived addresses to invoices created before the runtime derivation path. See [`x5.2_LEGACY_BACKFILL_HANDOFF.md`](../strategies/x5.2_LEGACY_BACKFILL_HANDOFF.md).

## Exit Criteria

- [x] Users can configure a watch-only public extended key and network.
- [x] Invoice creation requires wallet onboarding and assigns a unique derived receive address/index.
- [x] Runtime derivation and cursor advancement occur transactionally.
- [x] Legacy invoices can be previewed and backfilled in stable order.
- [x] No private key or seed-phrase path is introduced into the tracked product.

## Historical boundary

MS5 shipped one mutable cursor attached to the current user wallet key and reset that cursor when the key changed. It did not preserve per-key lineage or historical cursors; MS14 later introduced key-aware lineage/cursor safety. The current wallet UX spec contains substantial onboarding and validation work that postdates MS5.
