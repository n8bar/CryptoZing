# MS20 - Mainnet Cutover & Alpha Gate

Status: In Progress.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/OB_ROLLOUT_CHECKLIST.md`](../ops/OB_ROLLOUT_CHECKLIST.md)

## Milestone Objectives
- Stand up production hosting and take CryptoZing live on mainnet before the public open beta: deploy to the prod server, flip to mainnet on a clean baseline, onboard real watch-only xpubs, and prove the payment pipeline with real Bitcoin payments — an invoice and a donation.
- Gate access so registration stays fully live but new accounts cannot log in until manually approved — an invite-only alpha window. Testers beyond the operator are possible but unlikely.

## Kickoff preconditions
- **Content-publish gate (met):** cleared by "How to Receive Bitcoin" ([`docs/CONTENT_PLAN.md`](../CONTENT_PLAN.md)).

## Phase Rollup

### [ ] Phase 1 — Alpha Access Gate
New registrations land pending and cannot log in until approved from the support dashboard. Registration stays open and unchanged. Independent of mainnet, so it can lead. Also carries the gate-independent account ban (§8) so the abuse lever exists before open beta. Strategy: [`20.1_ALPHA_ACCESS_GATE.md`](../strategies/20.1_ALPHA_ACCESS_GATE.md).

### [ ] Phase 2 — Production Hosting
Provision the production server and deploy the app to it, running private, on the same Docker recipe published for self-hosters. The article site moves off GitHub Pages into its own repo and container, so the app image carries no CryptoZing public content. Strategy: [`20.2_PRODUCTION_HOSTING.md`](../strategies/20.2_PRODUCTION_HOSTING.md).

### [ ] Phase 3 — Mainnet Environment & Wallets
Clean mainnet baseline on the deployed box, with separate watch-only invoice and donation xpubs (never one shared key — MS14) verified before any funds move. Needs a mainnet donation wallet from the operator before it can start. Carries the mainnet donation-xpub swap from [`x19.8_MICRO_MONETIZE.md`](../strategies/x19.8_MICRO_MONETIZE.md) §1. Strategy: [`20.3_MAINNET_ENVIRONMENT_WALLETS.md`](../strategies/20.3_MAINNET_ENVIRONMENT_WALLETS.md).

### [ ] Phase 4 — Live Mainnet Validation & Backout
Real self-sent mainnet payments end to end, correction tooling against live data, and mail sanity on mainnet. Leaves behind the cutover runbook and halt procedure MS21 executes. Strategy: [`20.4_LIVE_MAINNET_VALIDATION_BACKOUT.md`](../strategies/20.4_LIVE_MAINNET_VALIDATION_BACKOUT.md).

## Exit Criteria
- [ ] Content promises catalog checked — no work in this milestone introduced or violated a [`docs/CONTENT_PROMISES.md`](../CONTENT_PROMISES.md) entry.
