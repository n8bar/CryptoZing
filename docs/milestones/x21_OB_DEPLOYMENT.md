# MS21 — CryptoZing.app Open Beta

Status: Complete.
Drafted: 2026-09-05.
Parent: [PLAN.md](../PLAN.md)

## Outcome

The existing alpha deployment becomes beta at `https://cryptozing.app`. It keeps the same VPS, database, accounts, wallets, and mail service; `alpha.cryptozing.app` is retired. The apex moves from GitHub Pages to the existing production stack, with Laravel serving `/` and the content container retaining the published `/learn/*` URLs.

The [content-publish gate](../CONTENT_PLAN.md) was met on 2026-09-04. Closed 2026-09-06; discovery observations and accepted follow-ups are in [M22](22_OPEN_BETA_PRODUCT_GROWTH.md#handoff-from-m21).

## Accepted baseline

- [M20.2](../strategies/x20.2_PRODUCTION_HOSTING.md) completed hosting, content-container separation, deployment/rollback, TLS renewal, backups, and a clean self-host installation.
- [M20.4](../strategies/x20.4_MAINNET_ENVIRONMENT_WALLETS.md) and [M20.5](../strategies/x20.5_LIVE_MAINNET_VALIDATION_BACKOUT.md) completed mainnet wallet comparisons, real payments, payment integrity, mail delivery, and recovery proof. Production aliasing is already off. M21 does not repeat wallet onboarding, allocation audits, live-payment tests, or mail-provider setup.
- [M19.5](../strategies/x19.5_LEGAL_LAYER.md) completed policy/disclaimer wording and placement review. M21 publishes the effective dates and any analytics disclosure change, and covers the new apex landing surface.

## Scope decisions

- Preserve public content paths, redirects, assets, sitemap, verification files, and indexing signals. The app's landing page needs the public metadata and legal footer before replacing the placeholder.
- Remove the content site's pre-release GitHub nav link; retain its footer link. Content source and Eleventy remain in `n8bar/cryptozing-site`; publishing switches from Pages to site-container releases on the VPS.
- Change app/public URLs and Mailgun callback registrations for the apex. Retire alpha after resolving its remaining links/callbacks; it is not a second deployment to maintain.
- Carry self-hosted Umami before policy publication, with the [2026-08-25 non-gating disposition](../CHANGELOG.log): an incomplete migration retains truthful Cloud disclosure and a named follow-up owner.
- Keep self-hostability: accept M20.2's clean-install proof for unchanged installation paths; verify M21 changes to shared installation artifacts where applicable. Our content/routing customization stays outside the generic recipe.
- No mailer upgrade/switch is planned. [#81](https://github.com/n8bar/CryptoZing/issues/81) remains deferred to that trigger, not a launch checklist.

## Execution references

[AGENTS.md](../../AGENTS.md), [DOC_ROLES.md](../DOC_ROLES.md), and [UX_GUARDRAILS.md](../UX_GUARDRAILS.md) govern the work. [RUNNING_THE_SERVER.md](../ops/RUNNING_THE_SERVER.md) owns operations and recovery.

Our existing stack runs from `/opt/cryptozing` with `compose.production.yaml` plus `compose.alpha.yaml`. The latter contains our nginx/content customization; retiring the alpha hostname does not mean dropping that overlay. Checklist notes hold release IDs and verdicts; private configuration snapshots use the existing local-only storage conventions.

## Phase Rollup

### [x] Phase 1 — Prepare the in-place release

Prepare apex routing, the landing/content changes, analytics/legal publication, and a tested cutover path.

Strategy: [M21.1](../strategies/x21.1_PRE_DEPLOY_VERIFICATION.md).

### [x] Phase 2 — Switch to the apex and open beta

Move public traffic to the existing stack, switch generated links and callbacks, retire the alpha hostname, and remove the approval gate.

Strategy: [M21.2](../strategies/x21.2_DEPLOY_AND_CUTOVER.md).

### [x] Phase 3 — Verify the public result and sign off

Check the changed public entry points and operating state, resolve cutover findings, and obtain acceptance.

Strategy: [M21.3](../strategies/x21.3_ROLLOUT_VERIFICATION_SIGNOFF.md).

## Exit Criteria

Detailed phase acceptance belongs to the strategies and rolls up above.

- [x] Check the [content promises catalog](../CONTENT_PROMISES.md) against M21's changes; resolve any introduced or violated promise before closure. The landing page moved from the content repo to the app with the same claims; Minor 7–11 re-sourced. Policies publish dates and first-party analytics wording only; nothing introduced or violated.
