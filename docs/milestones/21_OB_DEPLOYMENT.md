# MS21 - CryptoZing.app Deployment (Open Beta)

Status: Strategy drafts ready for review; execution not started.
Drafted: 2026-09-05.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/RUNNING_THE_SERVER.md`](../ops/RUNNING_THE_SERVER.md)

## Milestone Objectives

- Open the existing mainnet deployment to the public under `cryptozing.app`.
- Replace the GitHub Pages placeholder at `/` with the live app landing page, preserving the SEO baseline established in M15 and extended in M18.
- Verify production mail reaches intended recipients with working public links and delivery feedback.
- Activate the legal layer drafted in M19.5 and complete rollout verification and operator sign-off.
- Verify the release remains independently self-hostable using the published Docker recipe.

## Kickoff and current focus

- **Content-publish gate met:** the custodial-versus-noncustodial article published on 2026-09-04 satisfies the M21 gate; see [`CONTENT_PLAN.md`](../CONTENT_PLAN.md).
- **Inherited baseline:** [M20](x20_MAINNET_CUTOVER_ALPHA_GATE.md) closed with the app running privately on mainnet, separate invoice and donation wallets verified, real payments and mail proven, and backup/restore and halt procedures exercised. M21 preserves that live data and wallet lineage.
- **Current focus:** review the three phase strategies below. They propose execution sequencing; publication of these drafts does not approve implementation or begin cutover.
- **Next action:** review [M21.1](../strategies/21.1_PRE_DEPLOY_VERIFICATION.md), then the cutover and sign-off handoffs in M21.2 and M21.3.

## Deployment decisions

- **Content boundary:** the static site lives in `n8bar/cryptozing-site`, with Eleventy as its build and a separate production container. Content work happens in that repo. The app image and the generic self-host recipe carry no CryptoZing article site.
- **URL continuity:** the public site keeps `https://cryptozing.app/learn/*` and its existing assets and redirects. The apex `/` becomes the Laravel landing page. The site's build/staging workflow belongs to the content repo; the old app-repo `site/` and `public/content/` staging descriptions no longer apply.
- **Indexing:** the apex landing page and published content retain their canonical/indexing signals. Alpha and staging remain excluded; public invoice links retain noindex. The private nginx overlay must be prepared for this split before cutover.
- **GitHub Pages retirement:** retire Pages as the serving target after the VPS serves the public paths successfully. Preserve the source and release artifacts needed for recovery; Eleventy remains the site's build.
- **Navigation:** remove the pre-release GitHub link from the content site's main nav before open beta; retain its footer link.
- **Legal activation:** publish the approved CryptoZing LLC terms, privacy policy, and disclaimers during Phase 2, with the actual effective date and resolved links. Preserve [M19.5's placement rules](../strategies/x19.5_LEGAL_LAYER.md): app/guest/site footers show both policies, public invoice pages show Privacy only, and print has the disclaimer without a link footer.
- **Analytics:** aim to move the content site to self-hosted Umami before legal publication. This is a non-gating carried item per the 2026-08-25 [changelog decision](../CHANGELOG.log). A deferral must keep the actual provider disclosed and have a named follow-up owner; it must not publish first-party-only wording while Cloud still receives events.
- **Mail:** production aliasing was already disabled in M20 and must remain off. Non-prod retains aliasing across all outbound mail. [#81](https://github.com/n8bar/CryptoZing/issues/81) remains conditional on a mailer upgrade or switch; a public-host change alone does not establish that trigger.

## Phase Rollup

### [ ] Phase 1 — Pre-deploy Verification

Establish readiness for the apex release: recovery, wallet and mail checks, routing/indexing preparation, analytics disposition, legal preparation, and a clean self-host verification. Hands Phase 2 verified release artifacts and a reviewed cutover plan.

Strategy: [`21.1_PRE_DEPLOY_VERIFICATION.md`](../strategies/21.1_PRE_DEPLOY_VERIFICATION.md).

### [ ] Phase 2 — Deploy and Cutover

Publish the legal layer, move the apex to the production stack, verify TLS and delivery callbacks, then open access with the final production configuration. Hands Phase 3 a reachable open beta with initial smoke checks passed and recovery available.

Strategy: [`21.2_DEPLOY_AND_CUTOVER.md`](../strategies/21.2_DEPLOY_AND_CUTOVER.md).

### [ ] Phase 3 — Post-deploy Verification and Rollout Sign-off

Verify the public issuer/payer journey, production mail, payment integrity, discovery signals, and ongoing operations. Resolve rollout findings and obtain operator acceptance before closing M21.

Strategy: [`21.3_ROLLOUT_VERIFICATION_SIGNOFF.md`](../strategies/21.3_ROLLOUT_VERIFICATION_SIGNOFF.md).

## Exit Criteria

Phase completion and detailed acceptance checks live in the strategy docs and roll up through the three checkoffs above.

- [ ] Check the [content promises catalog](../CONTENT_PROMISES.md) against the shipped milestone; resolve any introduced or violated promise before closing M21.
