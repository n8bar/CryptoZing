# MS20 - Mainnet Cutover & Alpha Gate

Status: Not Started.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/OB_ROLLOUT_CHECKLIST.md`](../ops/OB_ROLLOUT_CHECKLIST.md)

## Milestone Objectives
- Stand up production hosting and take CryptoZing live on mainnet before the public open beta: deploy to the prod server, flip to mainnet on a clean baseline, onboard real watch-only xpubs, and prove the payment pipeline with real Bitcoin payments — an invoice and a donation.
- Gate access so registration stays fully live but new accounts cannot log in until manually approved — an invite-only alpha window. Testers beyond the operator are possible but unlikely.

## Kickoff preconditions
- **Content-publish gate (unmet):** per [`docs/PLAN.md`](../PLAN.md) Milestone Conventions, a fresh [`docs/CONTENT_PLAN.md`](../CONTENT_PLAN.md) article must publish before MS20 begins; prior-milestone content does not clear it.

## Phases

### Phase 1 — Alpha Access Gate
New registrations create a pending account that cannot log in until approved. Approve and revoke from a "Pending approvals" list in the support dashboard (`/support`, `EnsureSupportAgent`). Registration UX is unchanged; the gate sits at login, after credentials and before the second factor. Independent of mainnet — can lead. Strategy: [`20.1_ALPHA_ACCESS_GATE.md`](../strategies/20.1_ALPHA_ACCESS_GATE.md).

### Phase 2 — Production Hosting
Provision the production server that will serve `cryptozing.app` and deploy the app to it, running private. Migrate the article site off GitHub Pages onto this hosting so it ships with the app. Strategy: [`20.2_PRODUCTION_HOSTING.md`](../strategies/20.2_PRODUCTION_HOSTING.md).

### Phase 3 — Mainnet Environment & Wallets
Bring the deployed app onto a clean mainnet baseline and configure it for the private window. Needs a mainnet donation wallet from the operator before it can start. Onboard two watch-only xpubs — operator invoice and donation, kept separate (never one shared xpub — MS14) — and verify derived addresses against the source wallets before any funds move. No seed or private keys in the product boundary. Carries the mainnet donation-xpub swap from [`x19.8_MICRO_MONETIZE.md`](../strategies/x19.8_MICRO_MONETIZE.md) §1. Strategy: [`20.3_MAINNET_ENVIRONMENT_WALLETS.md`](../strategies/20.3_MAINNET_ENVIRONMENT_WALLETS.md).

### Phase 4 — Live Mainnet Validation & Backout
Prove the pipeline with real self-sent mainnet payments — an invoice and a donation, each end to end. Exercise the MS14 correction tooling against a wrong-attribution scenario on live data. Confirm mail sanity on mainnet. Capture a cutover runbook and a backout/halt procedure; MS21 runs the runbook. Strategy: [`20.4_LIVE_MAINNET_VALIDATION_BACKOUT.md`](../strategies/20.4_LIVE_MAINNET_VALIDATION_BACKOUT.md).

## Exit Criteria
The Phase level criteria move to their respective strategy doc when strategies are created.

**Phase 1 — Alpha Access Gate**
- [ ] New registrations create a pending account; registration UX unchanged and fully functional.
- [ ] Pending accounts cannot authenticate; login is blocked with clear messaging until approval, and the check interoperates with password auth and 2FA.
- [ ] Support dashboard shows a "Pending approvals" list with approve and revoke actions, gated by `EnsureSupportAgent`.
- [ ] Pending → approved → login transition covered by migration and feature tests.

**Phase 2 — Production Hosting**
- [ ] Production server provisioned and running the app, private.
- [ ] Article content migrated off GitHub Pages onto the new hosting and serving correctly there.

**Phase 3 — Mainnet Environment & Wallets**
- [ ] App on a clean mainnet baseline (no testnet-derived rows carried over); `WALLET_NETWORK=mainnet`, chain/mempool provider on mainnet, `APP_PUBLIC_URL` and mail set for the private window.
- [ ] Operator invoice xpub and a mainnet donation xpub onboarded as separate watch-only keys (`DONATION_WALLET_XPUB` swapped) — `/donate` exists only where that xpub is set ([#135](https://github.com/n8bar/CryptoZing/issues/135)).
- [ ] Watch-only verified — no seed or private keys in repo, config, database, or app flows.
- [ ] Derived invoice and donation addresses verified against the source wallets before funds move.

**Phase 4 — Live Mainnet Validation & Backout**
- [ ] Real self-sent mainnet invoice validated end to end: derived → paid → detected → confirmed → marked paid → receipt mail.
- [ ] Real self-sent mainnet donation validated end to end: derived → paid → recorded on the donation row (never as an invoice) → receipt → operator notification mail.
- [ ] MS14 correction tooling (ignore/restore/reattribute) exercised against a wrong-attribution scenario on live mainnet data; outcome documented.
- [ ] Mail sanity on mainnet: correct `APP_PUBLIC_URL` links and headers; DKIM/SPF/DMARC confirmed for the sending domain.
- [ ] Cutover runbook complete and proven by execution — env flips, wallet validation, mail sanity, backout/halt; MS21 executes it.

**Milestone-wide**
- [ ] Content promises catalog checked — no work in this milestone introduced or violated a [`docs/CONTENT_PROMISES.md`](../CONTENT_PROMISES.md) entry.
