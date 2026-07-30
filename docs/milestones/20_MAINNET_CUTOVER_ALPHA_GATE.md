# MS20 - Mainnet Cutover & Alpha Gate

Status: Not Started.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/OB_ROLLOUT_CHECKLIST.md`](../ops/OB_ROLLOUT_CHECKLIST.md)

## Milestone Objectives
- Stand up production hosting and take CryptoZing live on mainnet before the public open beta: deploy to the prod server, flip to mainnet on a clean baseline, onboard real watch-only xpubs, and prove the payment pipeline with real Bitcoin payments — an invoice and a donation.
- Gate access so registration stays fully live but new accounts cannot log in until manually approved — an invite-only alpha window. Testers beyond the operator are possible but unlikely.

## Kickoff preconditions
- **Content-publish gate (unmet):** per [`docs/PLAN.md`](../PLAN.md) Milestone Conventions, a fresh [`docs/CONTENT_PLAN.md`](../CONTENT_PLAN.md) article must publish before MS20 begins; prior-milestone content does not clear it.

## Phase Rollup

### [ ] Phase 1 — Alpha Access Gate
New registrations create a pending account that cannot log in until approved. Approve and revoke from a "Pending approvals" list in the support dashboard (`/support`, `EnsureSupportAgent`). Registration UX is unchanged; the gate sits at login, after credentials and before the second factor. Independent of mainnet — can lead. Strategy: [`20.1_ALPHA_ACCESS_GATE.md`](../strategies/20.1_ALPHA_ACCESS_GATE.md).

### [ ] Phase 2 — Production Hosting
Provision the production server that will serve `cryptozing.app` and deploy the app to it, running private, on the same Docker recipe published for self-hosters. The article site moves off GitHub Pages to its own repo and container on that hosting, so the app image carries no CryptoZing public content. Strategy: [`20.2_PRODUCTION_HOSTING.md`](../strategies/20.2_PRODUCTION_HOSTING.md).

### [ ] Phase 3 — Mainnet Environment & Wallets
Bring the deployed app onto a clean mainnet baseline and configure it for the private window. Needs a mainnet donation wallet from the operator before it can start. Onboard two watch-only xpubs — operator invoice and donation, kept separate (never one shared xpub — MS14) — and verify derived addresses against the source wallets before any funds move. No seed or private keys in the product boundary. Carries the mainnet donation-xpub swap from [`x19.8_MICRO_MONETIZE.md`](../strategies/x19.8_MICRO_MONETIZE.md) §1. Strategy: [`20.3_MAINNET_ENVIRONMENT_WALLETS.md`](../strategies/20.3_MAINNET_ENVIRONMENT_WALLETS.md).

### [ ] Phase 4 — Live Mainnet Validation & Backout
Prove the pipeline with real self-sent mainnet payments — an invoice and a donation, each end to end. Exercise the MS14 correction tooling against a wrong-attribution scenario on live data. Confirm mail sanity on mainnet. Capture a cutover runbook and a backout/halt procedure; MS21 runs the runbook. Strategy: [`20.4_LIVE_MAINNET_VALIDATION_BACKOUT.md`](../strategies/20.4_LIVE_MAINNET_VALIDATION_BACKOUT.md).

## Exit Criteria
- [ ] Content promises catalog checked — no work in this milestone introduced or violated a [`docs/CONTENT_PROMISES.md`](../CONTENT_PROMISES.md) entry.
