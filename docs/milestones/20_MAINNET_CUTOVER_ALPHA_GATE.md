# MS20 - Mainnet Cutover & Alpha Gate

Status: In Progress.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/PRODUCTION_OPS.md`](../ops/PRODUCTION_OPS.md)

## Milestone Objectives
- Stand up production hosting and take CryptoZing live on mainnet before the public open beta: deploy to the prod server, flip to mainnet on a clean baseline, onboard real watch-only xpubs, and prove the payment pipeline with real Bitcoin payments — an invoice and a donation.
- Gate access so registration stays fully live but new accounts cannot log in until manually approved — an invite-only alpha window. Testers beyond the operator are possible but unlikely.

## Kickoff preconditions
- **Content-publish gate (met):** cleared by "How to Receive Bitcoin" ([`docs/CONTENT_PLAN.md`](../CONTENT_PLAN.md)).

## Phase Rollup

### [x] Phase 1 — Alpha Access Gate
New registrations land pending and cannot log in until approved from the support dashboard. Registration stays open and unchanged. Independent of mainnet, so it can lead. Also carries the gate-independent account ban (§8) so the abuse lever exists before open beta. Strategy: [`x20.1_ALPHA_ACCESS_GATE.md`](../strategies/x20.1_ALPHA_ACCESS_GATE.md).

### [x] Phase 2 — Production Hosting
Provision the production server and deploy the app to it, running private, on the same Docker recipe published for self-hosters. The article site moves off GitHub Pages into its own repo and container, so the app image carries no CryptoZing public content. Strategy: [`x20.2_PRODUCTION_HOSTING.md`](../strategies/x20.2_PRODUCTION_HOSTING.md).

### [x] Phase 3 — Taproot Wallet Support
The watch-only wallet layer gains Taproot (BIP86) receive support — script-type-aware key onboarding and bech32m derivation — so the operator wallets and merchant onboarding are Taproot-capable at cutover, not retrofitted after. Spec-first; code rides a PR. Strategy: [`x20.3_TAPROOT_WALLET_SUPPORT.md`](../strategies/x20.3_TAPROOT_WALLET_SUPPORT.md).

### [x] Phase 4 — Mainnet Environment & Wallets
Clean mainnet baseline on the deployed box, with separate watch-only invoice and donation xpubs (never one shared key — MS14) verified before any funds move. Needs a mainnet donation wallet from the operator before it can start. Carries the mainnet donation-xpub swap from [`x19.8_MICRO_MONETIZE.md`](../strategies/x19.8_MICRO_MONETIZE.md) §1. Strategy: [`x20.4_MAINNET_ENVIRONMENT_WALLETS.md`](../strategies/x20.4_MAINNET_ENVIRONMENT_WALLETS.md).

### [x] Phase 5 — Live Mainnet Validation & Backout
Real self-sent mainnet payments end to end, correction tooling against live data, and mail sanity on mainnet. Leaves behind the cutover runbook and halt procedure MS21 executes. Strategy: [`x20.5_LIVE_MAINNET_VALIDATION_BACKOUT.md`](../strategies/x20.5_LIVE_MAINNET_VALIDATION_BACKOUT.md).

## Exit Criteria
- [ ] Content promises catalog checked — no work in this milestone introduced or violated a [`docs/CONTENT_PROMISES.md`](../CONTENT_PROMISES.md) entry.
