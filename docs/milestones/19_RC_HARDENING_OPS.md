# MS19 - RC Hardening & Ops

Status: Not started — phase strategy doc skeletons in place; decisions pending before flesh-out.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/DOCS_DX.md`](../ops/DOCS_DX.md)

## Milestone Objectives
- Document notification coverage so the full outbound mail surface is explicitly accounted for before RC.
- Add auth and password policy hardening: 419-to-login redirect, site-wide session expiry logout.
- Keep contributor docs current.
- Put a minimum legal layer in place before mainnet cutover: Terms of Service draft, Privacy Policy draft, disclaimer copy at key user touchpoints, monetization-neutral copy review across existing UI and mail, and UI placement (disclaimer surfaces + footer ToS/Privacy Policy links).
- Reconcile the content promises catalog against the finished product — confirm every open entry is honored or trigger a content/product revision.
- Refactor public-facing copy from "pre-release" / "Release Candidate" to "open beta" across all published pages.

## Decisions recorded
- **Legal approach:** No lawyer for RC1. Self-drafted ToS and Privacy Policy covering the essential bases — not financial advice, no custody of funds, user responsibility for keys, no warranty on BTC/USD values.
- **Disclaimer surfaces:** Account signup, wallet onboarding, and invoice/payment screens are the three required touchpoints. Footer links to ToS and Privacy Policy on every page.
- **Monetization-neutral language:** Avoid language that permanently forecloses pricing options ("always free," "no fees ever"). Leave room for future paid tiers or feature gating without requiring a ToS rewrite.

## Current Focus
- Active phase: _(MS19 begins on activation — pending MS18 close-out.)_
- Phase 1: [`docs/strategies/19.1_NOTIFICATION_COVERAGE_AUDIT.md`](../strategies/19.1_NOTIFICATION_COVERAGE_AUDIT.md)
- Phase 2: [`docs/strategies/19.2_AUTH_HARDENING.md`](../strategies/19.2_AUTH_HARDENING.md)
- Phase 3: [`docs/strategies/19.3_LEGAL_LAYER.md`](../strategies/19.3_LEGAL_LAYER.md)
- Phase 4: [`docs/strategies/19.4_CONTENT_PROMISES_RECONCILIATION.md`](../strategies/19.4_CONTENT_PROMISES_RECONCILIATION.md)
- Phase 5: [`docs/strategies/19.5_CONTRIBUTOR_DOCS.md`](../strategies/19.5_CONTRIBUTOR_DOCS.md)

## Phase Rollup

### [ ] Phase 1 — Notification Coverage Audit
Document every outbound mail type — trigger, recipient, delivery-log behavior — so the full mail surface is explicitly accounted for before RC.

### [ ] Phase 2 — Auth/Password Policy Hardening
Implement 419-to-login redirect and site-wide session-expiry logout.

### [ ] Phase 3 — Legal Layer
Draft ToS, Privacy Policy, disclaimer copy; review existing UI/mail copy for monetization-neutral language; place all in the UI.

### [ ] Phase 4 — Content Promises Reconciliation
Walk every open entry in `CONTENT_PROMISES.md` against the finished product; resolve each as honored, content-revised, or product-revised.

### [ ] Phase 5 — Contributor Docs Review
Refresh AGENTS.md, CLAUDE.md, AgentRoles/, and contributor-facing ops docs for currency before RC.

## Exit Criteria
_(To be detailed when active.)_

- [ ] Notification coverage documented: every outbound mail type accounted for with intended trigger, recipient, and delivery log behavior.
- [ ] 419-to-login redirect implemented and tested.
- [ ] Site-wide session expiry logout implemented and tested.
- [ ] ToS and Privacy Policy drafted and published to the live site.
- [ ] Disclaimer copy present at signup, wallet onboarding, and invoice/payment surfaces; footer links to ToS and Privacy Policy on every page.
- [ ] Existing UI and mail copy reviewed for overstatements, financial advice language, and pricing commitments — issues resolved.
- [ ] Monetization-safe language guide produced for future copy decisions.
- [ ] Content promises catalog reconciled — every open entry confirmed honored or resolved (content revised or product adjusted).
- [ ] Contributor docs reviewed and current.
