# MS19 - RC Hardening & Ops

Status: Active — running in parallel with MS18 (no hard dependencies between the two; MS18 is blocked on Rachel's video through the 2026-05-31 hard cap, MS19 phases are independent of that work). Phase 1 reopened 2026-05-15 for end-to-end verification (audit closed; verification pass appended). Phase 4 (Visual Identity Polish) added 2026-05-15 — favicon overhaul + open-beta copy refactor + og:image bundled as one visual pass; renumbered downstream phases accordingly. Phase 3 / Phase 5 corrected to independent parallel tracks (no LLC → Legal Layer prerequisite). `.ics` updated to reflect the expanded scope.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/DOCS_DX.md`](../ops/DOCS_DX.md)

## Milestone Objectives
- Document notification coverage so the full outbound mail surface is explicitly accounted for before RC.
- Add auth and password policy hardening: 419-to-login redirect, site-wide session expiry logout.
- Keep contributor docs current.
- Form a single-member LLC in Arizona with EIN, business bank account, and operating agreement, so the legal layer's ToS protections actually shield the operator personally.
- Put a minimum legal layer in place before mainnet cutover: Terms of Service draft, Privacy Policy draft, disclaimer copy at key user touchpoints, monetization-neutral copy review across existing UI and mail, and UI placement (disclaimer surfaces + footer ToS/Privacy Policy links).
- Reconcile the content promises catalog against the finished product — confirm every open entry is honored or trigger a content/product revision.
- Refactor public-facing copy from "pre-release" / "Release Candidate" to "open beta" across all published pages.
- Land a coherent visual identity polish before open beta — favicon set across all surfaces (theme-cohesive yet purpose-distinguishable), og:image / social previews, bundled with the open-beta copy refactor.
- Add 2FA capability for RC: email-based 2FA as the baseline, TOTP opportunistically if MS19 time allows, with a recommendation surface for users without 2FA enabled.

## Decisions recorded
- **Legal approach:** No lawyer for RC1. Self-drafted ToS and Privacy Policy covering the essential bases — not financial advice, no custody of funds, user responsibility for keys, no warranty on BTC/USD values.
- **Disclaimer surfaces:** Account signup, wallet onboarding, and invoice/payment screens are the three required touchpoints. Footer links to ToS and Privacy Policy on every page.
- **Monetization-neutral language:** Avoid language that permanently forecloses pricing options ("always free," "no fees ever"). Leave room for future paid tiers or feature gating without requiring a ToS rewrite.
- **Findings tracking trial:** Through M19, new findings/bugs/todos go to GitHub Issues (closed via `Fixes #N` on the merging PR) instead of new `docs/qa/Finding*.md` docs. Existing finding docs stay put. M20 kickoff decides whether to keep, revert, or hybridize. See [`docs/DOC_ROLES.md`](../DOC_ROLES.md#findings-conventions).

## Current Focus
- Active phase: Phase 1 (reopened — verification pass) and Phase 2 (next-up auth hardening). Other phases pre-flight.
- Phase 1: [`docs/strategies/19.1_NOTIFICATION_COVERAGE_AUDIT.md`](../strategies/19.1_NOTIFICATION_COVERAGE_AUDIT.md)
- Phase 2: [`docs/strategies/19.2_AUTH_HARDENING.md`](../strategies/19.2_AUTH_HARDENING.md)
- Phase 3: [`docs/strategies/19.3_LLC_FORMATION.md`](../strategies/19.3_LLC_FORMATION.md)
- Phase 4: [`docs/strategies/19.4_VISUAL_IDENTITY_POLISH.md`](../strategies/19.4_VISUAL_IDENTITY_POLISH.md)
- Phase 5: [`docs/strategies/19.5_LEGAL_LAYER.md`](../strategies/19.5_LEGAL_LAYER.md)
- Phase 6: [`docs/strategies/19.6_CONTENT_PROMISES_RECONCILIATION.md`](../strategies/19.6_CONTENT_PROMISES_RECONCILIATION.md)
- Phase 7: [`docs/strategies/19.7_CONTRIBUTOR_DOCS.md`](../strategies/19.7_CONTRIBUTOR_DOCS.md)
- Phase 8: [`docs/strategies/19.8_TWO_FACTOR_AUTHENTICATION.md`](../strategies/19.8_TWO_FACTOR_AUTHENTICATION.md)

## Phase Rollup

### [ ] Phase 1 — Notification Coverage Audit & Verification
Document every outbound mail type — trigger, recipient, delivery-log behavior — so the full mail surface is explicitly accounted for before open beta. Audit (§1–§4) closed 2026-05-09. Reopened 2026-05-15 to add §5–§7 end-to-end verification (every notice class triggered in the running stack against realistic scenarios including time-advanced past-due flows; rendered email confirmed at intended recipients; catch-all alias flipped off so the rest of MS19 emits mail under prod-like routing). See [`19.1_NOTIFICATION_COVERAGE_AUDIT.md`](../strategies/19.1_NOTIFICATION_COVERAGE_AUDIT.md).

### [ ] Phase 2 — Auth/Password Policy Hardening
Implement 419-to-login redirect and site-wide session-expiry logout.

### [ ] Phase 3 — LLC Formation
Form a single-member LLC in Arizona; obtain EIN; open a business bank account; draft and sign an operating agreement; update CryptoZing references to reflect the entity. Provides the entity backing the Phase 5 legal-layer ToS protections need to actually shield the operator personally. Phase 3 (LLC) and Phase 5 (Legal Layer) run as **independent parallel tracks** — drafting/UI work in Phase 5 does not gate on LLC status; only the deploy-time entity-name swap at MS21 needs the formed entity. Both must land before MS21.

### [ ] Phase 4 — Visual Identity Polish
Land the visual/brand polish pass before open beta: favicon set across all surfaces (theme-cohesive yet purpose-distinguishable), og:image / social previews, and the open-beta copy refactor (formerly Phase 4 §5 / now Phase 5 §5 of the prior layout — moved here so it bundles with the favicon and social-preview work as one coherent visual pass). No dependency on Phase 3; slotted here so subsequent phases review their surfaces in their final visual state.

### [ ] Phase 5 — Legal Layer
Draft ToS, Privacy Policy, disclaimer copy; review existing UI/mail copy for monetization-neutral language; place all in the UI. Drafting + UI work has no dependency on Phase 3 (LLC) — runs in parallel. Final entity-name swap and publication is deferred to MS21 deploy time.

### [ ] Phase 6 — Content Promises Reconciliation
Walk every open entry in `CONTENT_PROMISES.md` against the finished product; resolve each as honored, content-revised, or product-revised.

### [ ] Phase 7 — Contributor Docs Review
Refresh AGENTS.md, CLAUDE.md, AgentRoles/, and contributor-facing ops docs for currency before RC.

### [ ] Phase 8 — Two-Factor Authentication
Add 2FA to the RC. Email-based 2FA as the baseline; TOTP / authenticator-app 2FA opportunistically if MS19 time allows (deferred to the 2028 release otherwise). Includes a non-blocking recommendation surface for users without 2FA enabled. **Positionally last by design** — if additional phases are ever added to MS19, this one stays at the end.

## Exit Criteria

- [ ] Notification coverage documented AND verified end-to-end: every outbound mail type accounted for with intended trigger, recipient, and delivery-log behavior, and every notice class observed firing correctly in the running stack against realistic scenarios (including time-advanced past-due flows). Catch-all alias disabled; later MS19 phases run under prod-like mail routing.
- [ ] 419-to-login redirect implemented and tested.
- [ ] Site-wide session expiry logout implemented and tested.
- [ ] LLC formed in Arizona; EIN obtained; business bank account opened; operating agreement signed; CryptoZing references updated to reflect the entity.
- [ ] Favicon set generated and wired across all surfaces (Laravel app, marketing site, any other distinct surface) with theme-cohesive yet purpose-distinguishable per-surface variants.
- [ ] og:image / social-preview meta tags wired and validated against major platform preview tools.
- [ ] User-facing "pre-release" / "Release Candidate" copy replaced with "open beta" framing; internal docs unchanged.
- [ ] ToS and Privacy Policy drafted and published to the live site.
- [ ] Disclaimer copy present at signup, wallet onboarding, and invoice/payment surfaces; footer links to ToS and Privacy Policy on every page.
- [ ] Existing UI and mail copy reviewed for overstatements, financial advice language, and pricing commitments — issues resolved.
- [ ] Monetization-safe language guide produced for future copy decisions.
- [ ] Content promises catalog reconciled — every open entry confirmed honored or resolved (content revised or product adjusted).
- [ ] Contributor docs reviewed and current.
- [ ] Email 2FA available as opt-in; recovery flow per the Phase 8 decision in place.
- [ ] Recommendation surface for users without 2FA enabled is shipped.
- [ ] TOTP shipped if MS19 time-cutoff met; otherwise explicitly deferred to the 2028 release.
