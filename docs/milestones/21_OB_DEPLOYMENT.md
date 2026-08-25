# MS21 - CryptoZing.app Deployment (Open Beta)

> **Stub** — high-level scope and decisions recorded. Phase strategy docs and detailed exit criteria to be written when this milestone becomes active.

Status: Not started.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/PRODUCTION_OPS.md`](../ops/PRODUCTION_OPS.md)

## Milestone Objectives
- Deploy the open beta under `cryptozing.app`.
- Replace the GitHub Pages placeholder at `/` with the live app landing page without breaking the SEO baseline established in MS15 and extended in MS18.
- Remove temporary mail aliasing.
- Complete rollout verification per the rollout checklist.
- Activate the legal layer drafted in MS19 Phase 5: resolve the remaining placeholders (effective date, Privacy Policy links — the entity name is already final: CryptoZing LLC), publish ToS and Privacy Policy to live URLs, and wire footer ToS/Privacy links.

## Decisions recorded (during MS18 Phase 1)
- **Content site architecture:** Static content served on the same domain as the app, with no PHP involved for content routes. The MS18 Eleventy selection was re-evaluated at MS20 Phase 2 and kept: the site moves to its own repo and container, and the app image carries no CryptoZing public content, so a self-hosted instance never serves it.
- **URL structure:** Content lives at `cryptozing.app/learn/*`. Articles authored in `site/learn/`, Eleventy outputs to `public/content/learn/`. GitHub Pages serves these URLs pre-open-beta; DNS cutover at MS21 points `cryptozing.app` at nginx, which serves the same paths unchanged. SEO value built pre-open-beta is fully preserved — URLs never move.
- **Cutover mechanics:** Only `cryptozing.app/` changes at cutover — the placeholder is replaced by the Laravel landing page. Everything under `/learn/` continues serving from the same URLs, just via nginx instead of GitHub Pages.
- **Staging:** Dev server (`public/content/` via Sail) is the staging environment during MS18–MS20. At open beta deployment, the built `public/content/` output is what nginx serves. Post-open-beta staging options to be decided post-open-beta.
- **GitHub Pages retirement:** GitHub Pages is retired at DNS cutover — not deleted, just no longer the DNS target. No redirects needed; URLs are preserved by the nginx serving the same paths.
- **GitHub nav link:** Remove the GitHub link from the site nav before open beta deployment — it's pre-release framing. Keep the footer link as-is; consider updating copy post-open-beta if it no longer fits.
- **Legal-layer activation:** ToS, Privacy Policy, disclaimer copy, and footer ToS/Privacy link scaffolding are drafted in MS19 Phase 5 with the final entity name (CryptoZing LLC, formed in MS19 Phase 3). Resolving the remaining placeholders (effective date, Privacy Policy links) and publication to live URLs happen at deploy time within Phase 2 of this milestone — not as a separate phase. Treat as a finishing step in the deploy/cutover work.

## Phases
_(Phase strategy docs to be written when this milestone becomes active.)_

- Phase 1 — Pre-deploy verification: env, wallet, mail, DNS, SEO baseline check, self-host verification
- Phase 2 — Deploy and cutover
- Phase 3 — Post-deploy verification and rollout sign-off

### Cutover sequence (fold into the phase docs when active)

Carried from the MS20 cutover as it actually ran. Several of these fail **silently** out of sequence — the deploy looks fine and the damage surfaces later. Standing operational knowledge that outlives this milestone — how the box is driven, backups, and the halt procedure — lives in [`docs/ops/PRODUCTION_OPS.md`](../ops/PRODUCTION_OPS.md).

**Before touching anything**

1. [ ] Take a fresh database dump and verify it opens. A halt is only as good as the backup it restores.
2. [ ] Confirm the `age` identity is in hand — the box cannot restore itself.
3. [ ] Note the deployed `CZ_TAG`. That sha is the rollback target and its image is already cached on the box.

**Environment flips, in the order they have to happen** → Phase 2

1. [ ] DNS resolves the new hostname **and TLS is issued** before anything points at it. Mail already sent with links to a certless host is in someone's inbox; no amount of env editing recalls it.
2. [ ] Switch the certbot renewal authenticator to `webroot` if the hostname was issued standalone. Standalone renewals fail silently once nginx owns port 80, and the failure surfaces sixty days later.
3. [ ] Set `CZ_SERVER_NAME` to the new hostname so the nginx template renders for it.
4. [ ] Set `APP_URL`, with `APP_PUBLIC_URL` following it. Everything in outbound mail derives from this.
5. [ ] Set `ALPHA_GATE_ENABLED=false` to end the invite-only window.
6. [ ] Turn prod-side aliasing off: `MAIL_ALIAS_ENABLED=false`, clear `MAIL_ALIAS_DOMAIN`. Non-prod keeps aliasing as its containment.
7. [ ] Run migrations before services come up on the new image.
8. [ ] Recreate **every** service, not just the app — each holds its own copy of the environment.

**Wallet validation, before any funds move** → Phase 1

1. [ ] `wallet:check-config` exits clean. It fails the deploy when the donation xpub is malformed, is signing material, belongs to the other network, or is the same account key as an onboarded invoice wallet. A shared key derives the same addresses on both chains, so payments collide and attribution is lost.
2. [ ] Derive the first several invoice addresses on the box and compare index-by-index against the source wallet, diffing a fresh derivation rather than an earlier transcript.
3. [ ] Derive donation addresses and compare the same way.
4. [ ] Confirm the app does not flag the key as an unsupported wallet configuration.
5. [ ] Confirm the invoice and donation chains advance independently.
6. [ ] Any mismatch stops the cutover. Do not proceed to mail.

**Mail sanity** → Phase 1

1. [ ] `MAIL_*` credentials valid for the production sending domain, `MAILGUN_ENDPOINT` matching the provider region.
2. [ ] **Re-register the Mailgun webhook against the new hostname** for `delivered`, `failed`, and `permanent_fail`. The webhook is registered per-URL; changing the public host silently orphans the old registration and delivery status stops flowing back, leaving the log stuck on queued.
3. [ ] Set `MAILGUN_WEBHOOK_SIGNING_KEY` from the dashboard.
4. [ ] Send a real invoice email and a paid receipt to a real inbox.
5. [ ] Confirm delivery status flows back onto the delivery log rather than staying queued.
6. [ ] Confirm every link resolves to `APP_PUBLIC_URL` — not localhost, not the apex, not the previous private hostname.
7. [ ] Confirm SPF, DKIM, and DMARC pass and are strictly aligned, read from a message that actually arrived.

**Post-deploy verification and sign-off** → Phase 3

1. [ ] [Agent] `docker compose ps` healthy per service.
2. [ ] [Agent] No pending migrations remain.
3. [ ] [Agent] Smoke the core path: dashboard loads, create an invoice, enable its share link, load it signed out, send a delivery.
4. [ ] [Agent] Spot-check invoices and clients for ownership and auth anomalies.
5. [ ] [Agent] Watcher run stamp fresh and the stale tile quiet.
6. [ ] [Agent] Spot-check the payment ledger against the chain: address, sats, block, settlement time.
7. [ ] [Agent] Public links carry the right host and retain noindex headers.
8. [ ] [Agent] Logs and alerts reviewed for mail, watcher, and error rates.
9. [ ] [User] Invoice and receipt mail read in a real client — headers, sender, rendering.
10. [ ] [User] Derived receive addresses appear as expected in the source wallets.
11. [ ] [User] Final sign-off that the deployment is fit to be public.

Carried items (fold into phase docs when active):
- [ ] [#81](https://github.com/n8bar/CryptoZing/issues/81) Re-run mail stress testing when the mailer service is upgraded or switched — likely lands at or after the production mail cutover. Deferred-test list in [`x19.1_NOTIFICATION_COVERAGE_AUDIT.md`](../strategies/x19.1_NOTIFICATION_COVERAGE_AUDIT.md) §6.
- [ ] Donation env vars set in production at deploy: `DONATION_WALLET_XPUB` (mainnet swap tracked in MS20), `DONATION_NOTIFY_EMAIL`, `DONATION_MAX_UNPAID_ADDRESSES`. See [`x19.8_MICRO_MONETIZE.md`](../strategies/x19.8_MICRO_MONETIZE.md) §1.
- [ ] Retire GitHub Pages at cutover, once the site container serves `/learn/*` from the production box. Eleventy moves with the content repo in MS20 Phase 2 per [`x20.2_PRODUCTION_HOSTING.md`](../strategies/x20.2_PRODUCTION_HOSTING.md) §4 and stays as that repo's build.

## Exit Criteria
_(To be detailed when active.)_

- [ ] Open beta deployed and reachable under `cryptozing.app`.
- [ ] Live app landing page replaces GitHub Pages placeholder at `/`; SEO baseline intact (canonical, sitemap, robots, indexed URLs).
- [ ] Temporary mail aliasing removed; outbound mail routes through production config.
- [ ] Self-host deployment verified — a clean instance can be stood up independently from the production environment.
- [ ] Legal layer activated: drafted ToS and Privacy Policy from MS19 Phase 5 published with the actual LLC entity name; footer ToS/Privacy links functional on every page; disclaimer copy live at signup, wallet onboarding, and invoice/payment surfaces.
- [ ] Content promises catalog checked — no work in this milestone introduced or violated an entry.
- [ ] All rollout checklist items completed and signed off.
