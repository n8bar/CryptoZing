# MS20 - Mainnet Cutover & Alpha Gate

Status: Defined at kickoff — not yet begun; the start is gated on the content-publish gate (see Kickoff preconditions). Phase strategy docs (`20.1`–`20.3`) written as each phase becomes active.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/OB_ROLLOUT_CHECKLIST.md`](../ops/OB_ROLLOUT_CHECKLIST.md)

## Milestone Objectives
- Take CryptoZing live on mainnet **for real, privately**, before the public open beta: flip the production environment to mainnet on a clean baseline, onboard real watch-only xpubs, and prove the full payment pipeline with a real self-sent Bitcoin payment (an invoice and a donation) — network fee paid and all.
- Gate access so the registration experience stays fully live but new accounts cannot log in until manually approved — an invite-only alpha window (realistically the operator alone at first) held closed until the doors open at MS21.
- Produce a cutover runbook proven by execution — env flips, wallet validation, mail sanity, and a backout/halt path — that MS21's public deploy runs rather than improvises.

## Decisions recorded
- **This is a real cutover, not a rehearsal.** MS20 flips to mainnet and validates with a real self-sent payment (real network fee); the "rehearse/pretend" framing is explicitly rejected. The line between MS20 and MS21 is *public*, not *real*: MS20 proves the product on real Bitcoin privately; MS21 opens it to the public (DNS cutover, placeholder swap, mail-alias off).
- **Environment: the production box.** The real mainnet run happens on the same server that will serve `cryptozing.app` — flipped to mainnet but not yet public (no DNS cutover; the GitHub Pages placeholder stays at `/`). This proves the actual environment and shrinks MS21 to "make it public."
- **Two xpubs, never one.** The operator invoice xpub and the donation xpub stay separate — a shared xpub would drive both derivation cursors down the same address chain, collide invoice and donation addresses, and destroy attribution (the exact wallet-reuse failure MS14 hardened against). Whether the two share one seed (two accounts) or live in two separate wallets is an operational choice, deferred; the app only ever sees the two xpubs.
- **Clean mainnet baseline.** Flipping `WALLET_NETWORK=mainnet` strands every testnet-derived address already in the database. Since the app holds only seed/test data — no real customers — the cutover starts from a fresh mainnet baseline rather than migrating stale testnet rows.
- **Alpha access gate via the support dashboard.** New registrations land in a pending state and cannot authenticate until approved from a "Pending approvals" list in the existing support UI (`/support`, gated by `EnsureSupportAgent`). Registration UX stays unchanged. Chosen over an artisan-only command so the gate survives past an audience of one.
- **Findings tracking:** the M19 GitHub-Issues convention is now standing — new findings/bugs/todos open as GitHub Issues (closed via `Fixes #N` on the merging PR); post-MVP feature work goes to [`docs/BACKLOG.md`](../BACKLOG.md), not Issues. Reflected in [`docs/DOC_ROLES.md`](../DOC_ROLES.md#findings-conventions).

## Kickoff preconditions
- **Content-publish gate (unmet — clear before MS20 begins):** per [`docs/PLAN.md`](../PLAN.md) Milestone Conventions, a fresh [`docs/CONTENT_PLAN.md`](../CONTENT_PLAN.md) article must be published before Phase 1 execution begins — prior-milestone content does not clear the transition gate. Content is a parallel track; this transition is the checkpoint.

## Phases
_(Phase strategy docs written when each phase becomes active.)_

### Phase 1 — Alpha Access Gate
New registrations create an account in a pending state and cannot log in until approved. Approval happens from a "Pending approvals" list added to the existing support dashboard (`/support`, `EnsureSupportAgent`), with approve and revoke actions. The registration flow itself is untouched and stays fully live; the gate sits at login, after credentials and 2FA. Independent of mainnet, so this phase can lead. Strategy doc: `20.1_ALPHA_ACCESS_GATE` (when active).

### Phase 2 — Mainnet Environment & Wallets
Stand up the production box and bring the app onto a clean mainnet baseline: flip `WALLET_NETWORK=mainnet`, point the chain/mempool provider at mainnet, and set `APP_PUBLIC_URL` and mail config for the private window per the rollout checklist. Onboard the operator invoice xpub as a real mainnet watch-only key at `/wallet/settings`, swap `DONATION_WALLET_XPUB` to a mainnet donation xpub (the carried item from [`x19.8_MICRO_MONETIZE.md`](../strategies/x19.8_MICRO_MONETIZE.md) §1), and verify derived addresses against the source wallets before any funds move. The watch-only guarantee holds throughout — no seed or private keys anywhere in the product boundary. Strategy doc: `20.2_MAINNET_ENVIRONMENT_WALLETS` (when active).

### Phase 3 — Live Mainnet Validation & Backout
The real proof. Self-send a real mainnet payment against a real invoice and run it end to end — address derived → paid → watcher detects → confirmations tracked → invoice marked paid → receipt mail delivered — then do the same for a real donation, confirming it records on the donation row (never as an invoice) with the operator notification mail. Exercise the MS14 correction tooling (ignore/restore/reattribute) against a real or plausible wrong-attribution scenario on live mainnet data — the first opportunity to test it under real on-chain conditions. Confirm mail sanity on mainnet (links, headers, deliverability). Capture the results as a cutover runbook plus a backout/halt procedure for when validation surfaces a problem; MS21 executes this runbook. Strategy doc: `20.3_LIVE_MAINNET_VALIDATION_BACKOUT` (when active).

## Exit Criteria

**Phase 1 — Alpha Access Gate**
- [ ] New registrations create an account in a pending state; the registration experience is unchanged and fully functional.
- [ ] Pending accounts cannot authenticate — login is blocked with clear messaging until approval; the check interoperates with 2FA and password auth.
- [ ] Support dashboard shows a "Pending approvals" list with approve and revoke actions, gated by `EnsureSupportAgent`.
- [ ] Approval enables login and the pending → approved → login transition is covered by migration and feature tests.

**Phase 2 — Mainnet Environment & Wallets**
- [ ] Production box stood up and running the app on a clean mainnet baseline (no testnet-derived rows carried over).
- [ ] `WALLET_NETWORK=mainnet`; chain/mempool provider pointed at mainnet; `APP_PUBLIC_URL` and mail config set per the rollout checklist for the private (pre-public) window.
- [ ] Operator invoice xpub onboarded as a real mainnet watch-only key at `/wallet/settings`; `DONATION_WALLET_XPUB` swapped to a mainnet donation xpub.
- [ ] Watch-only guarantee verified — no seed or private keys in the repo, config, database, or application flows.
- [ ] Derived addresses (invoice and donation) verified against the source wallets before any funds move.

**Phase 3 — Live Mainnet Validation & Backout**
- [ ] Real self-sent mainnet invoice payment validated end to end: address derived → paid → watcher detects → confirmations tracked → invoice marked paid → receipt mail delivered.
- [ ] Real self-sent mainnet donation validated end to end: donation address derived → paid → recorded on the donation row (never as an invoice) → thank-you/receipt → operator notification mail.
- [ ] MS14 correction tooling (ignore/restore/reattribute) exercised against a real or plausible wrong-attribution scenario on live mainnet data; outcome documented (pass or gaps found).
- [ ] Mail sanity on mainnet: outbound mail renders with correct `APP_PUBLIC_URL` links and headers; DKIM/SPF/DMARC confirmed for the sending domain.
- [ ] Cutover runbook complete and proven by execution — env flips, wallet validation, mail sanity, and a backout/halt procedure; MS21 executes this runbook.

**Milestone-wide**
- [ ] Content promises catalog checked — no work in this milestone introduced or violated a [`docs/CONTENT_PROMISES.md`](../CONTENT_PROMISES.md) entry.
