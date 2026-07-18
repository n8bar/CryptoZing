# MS19 - Open Beta Hardening & Ops

Status: Active — Phases 1–5 complete; Phase 6 (Content Promises Reconciliation) next. Phase 9 (2FA) is positionally last by design.
Parent execution doc: [`docs/PLAN.md`](../PLAN.md)
Supporting ops doc: [`docs/ops/DOCS_DX.md`](../ops/DOCS_DX.md)

## Milestone Objectives
- Document notification coverage so the full outbound mail surface is explicitly accounted for before open beta.
- Add auth and password policy hardening: 419-to-login redirect, site-wide session expiry logout.
- Keep contributor docs current.
- Form the parent and subsidiary LLCs in Arizona, obtain EINs, sign operating agreements, and update entity references so the legal layer's ToS protections actually shield the operator personally. Business banking and E&O insurance are revenue-gated Phase 8 work.
- Put a minimum legal layer in place before mainnet cutover: Terms of Service draft, Privacy Policy draft, disclaimer copy at key user touchpoints, monetization-neutral copy review across existing UI and mail, and UI placement (disclaimer surfaces + footer ToS/Privacy Policy links).
- Reconcile the content promises catalog against the finished product — confirm every open entry is honored or trigger a content/product revision.
- Refactor public-facing copy from "pre-release" / "Release Candidate" to "open beta" across all published pages.
- Land a coherent visual identity polish before open beta — a single CryptoZing favicon set site-wide, the favicon-matching og:image card, the RC→open-beta copy cleanup, and branded error pages (#96).
- Add 2FA capability for open beta: email-based 2FA as the baseline, TOTP opportunistically if MS19 time allows, with a recommendation surface for users without 2FA enabled.

## Decisions recorded
- **Legal approach:** No lawyer for RC1. Self-drafted ToS and Privacy Policy covering the essential bases — not financial advice, no custody of funds, user responsibility for keys, no warranty on BTC/USD values.
- **Disclaimer surfaces:** Account signup, wallet onboarding, and invoice/payment screens are the three required touchpoints. Footer links to ToS and Privacy Policy on every page.
- **Monetization-neutral language:** Avoid language that permanently forecloses pricing options ("always free," "no fees ever"). Leave room for future paid tiers or feature gating without requiring a ToS rewrite.
- **Findings tracking trial:** Through M19, new findings/bugs/todos go to GitHub Issues (closed via `Fixes #N` on the merging PR) instead of new `docs/qa/Finding*.md` docs. Existing finding docs stay put. M20 kickoff decides whether to keep, revert, or hybridize. See [`docs/DOC_ROLES.md`](../DOC_ROLES.md#findings-conventions).

## Current Focus
- Active phase: Phase 6 (Content Promises Reconciliation). Phases 1–5 complete.
- Phase 1: [`docs/strategies/x19.1_NOTIFICATION_COVERAGE_AUDIT.md`](../strategies/x19.1_NOTIFICATION_COVERAGE_AUDIT.md) ✓
- Phase 2: [`docs/strategies/x19.2_AUTH_HARDENING.md`](../strategies/x19.2_AUTH_HARDENING.md) ✓
- Phase 3: [`docs/strategies/x19.3_LLC_FORMATION.md`](../strategies/x19.3_LLC_FORMATION.md) ✓
- Phase 4: [`docs/strategies/x19.4_VISUAL_IDENTITY_POLISH.md`](../strategies/x19.4_VISUAL_IDENTITY_POLISH.md) ✓
- Phase 5: [`docs/strategies/x19.5_LEGAL_LAYER.md`](../strategies/x19.5_LEGAL_LAYER.md) ✓
- Phase 6: [`docs/strategies/19.6_CONTENT_PROMISES_RECONCILIATION.md`](../strategies/19.6_CONTENT_PROMISES_RECONCILIATION.md)
- Phase 7: [`docs/strategies/19.7_CONTRIBUTOR_DOCS.md`](../strategies/19.7_CONTRIBUTOR_DOCS.md)
- Phase 8: [`docs/strategies/19.8_MICRO_MONETIZE.md`](../strategies/19.8_MICRO_MONETIZE.md)
- Phase 9: [`docs/strategies/19.9_TWO_FACTOR_AUTHENTICATION.md`](../strategies/19.9_TWO_FACTOR_AUTHENTICATION.md)

## Phase Rollup

### [x] Phase 1 — Notification Coverage Audit & Verification
Document every outbound mail type — trigger, recipient, delivery-log behavior — so the full mail surface is explicitly accounted for before open beta. Includes end-to-end verification of every notice class in the running stack against realistic scenarios (time-advanced past-due flows included) and a stress-readiness check; catch-all alias stays flipped off so the rest of MS19 emits mail under prod-like routing. See [`x19.1_NOTIFICATION_COVERAGE_AUDIT.md`](../strategies/x19.1_NOTIFICATION_COVERAGE_AUDIT.md).

### [x] Phase 2 — Auth/Password Policy Hardening
Implement 419-to-login redirect and site-wide session-expiry logout, with return-to-page after re-auth. See [`x19.2_AUTH_HARDENING.md`](../strategies/x19.2_AUTH_HARDENING.md).

### [x] Phase 3 — LLC Formation
Formed parent **CyberCreek LLC** then subsidiary **CryptoZing LLC** (member: CyberCreek) in Arizona; obtained both EINs, signed operating agreements, completed publication and AZCC affidavit filing, and updated CryptoZing entity references. Business banking and E&O insurance moved to revenue-gated Phase 8 work. The entity name is final in the Phase 5 drafts; see [`x19.3_LLC_FORMATION.md`](../strategies/x19.3_LLC_FORMATION.md).

### [x] Phase 4 — Visual Identity Polish
Visual/brand polish pass before open beta: a single CryptoZing favicon set site-wide (faithful potrace of the real logo), the favicon-matching og:image card, the RC→"open beta" terminology cleanup, and branded guest-safe error pages (#96). Shipped and verified (app surfaces + live marketing site); see [`x19.4_VISUAL_IDENTITY_POLISH.md`](../strategies/x19.4_VISUAL_IDENTITY_POLISH.md).

### [x] Phase 5 — Legal Layer
Drafted the ToS, Privacy Policy, and disclaimer copy under CryptoZing LLC; placed disclaimer surfaces and footer ToS/PP link scaffolding; completed the monetization-neutral copy review with a language guide added to `UX_GUARDRAILS.md`; swept every open issue to a fix or durable todo home (#71, #72, #73, #123, #126, #128 fixed; #81, #120 rehomed as trackers). Publication (effective dates + live links) lands at MS21 deploy. See [`x19.5_LEGAL_LAYER.md`](../strategies/x19.5_LEGAL_LAYER.md).

### [ ] Phase 6 — Content Promises Reconciliation
Walk every open entry in `CONTENT_PROMISES.md` against the finished product; resolve each as honored, content-revised, or product-revised.

### [ ] Phase 7 — Contributor Docs Review
Refresh AGENTS.md, CLAUDE.md, AgentRoles/, and contributor-facing ops docs for currency before open beta.

### [ ] Phase 8 — Micro-Monetize
CryptoZing's first revenue surface — a donation page (BTC native; fiat a consideration). **Stub — scope undecided** (BTC-only vs. BTC+fiat); may spin out to its own milestone rather than stay an MS19 phase. The business bank account moves here from Phase 3, deferred until a first fiat inflow warrants opening. Before starting: re-enable the `frontend-design` plugin. See [`19.8_MICRO_MONETIZE.md`](../strategies/19.8_MICRO_MONETIZE.md).

### [ ] Phase 9 — Two-Factor Authentication
Add 2FA to the open beta. Email-based 2FA as the baseline; TOTP / authenticator-app 2FA opportunistically if MS19 time allows (deferred to the 2028 release otherwise). Includes a non-blocking recommendation surface for users without 2FA enabled. **Positionally last by design** — if additional phases are ever added to MS19, this one stays at the end.

## Exit Criteria

- [x] Notification coverage documented AND verified end-to-end: every outbound mail type accounted for with intended trigger, recipient, delivery-log behavior, and realistic end-to-end verification (including time-advanced past-due flows). Catch-all alias disabled; later MS19 phases run under prod-like mail routing.
- [x] 419-to-login redirect implemented and tested.
- [x] Site-wide session expiry logout implemented and tested.
- [x] LLCs formed in Arizona; EINs obtained; operating agreements signed; publication affidavits filed; CryptoZing references updated to reflect the entity. (Business bank account and E&O insurance moved to Phase 8.)
- [x] Single CryptoZing favicon set generated and wired site-wide (Laravel app + marketing/Pages site) — one mark, no per-surface variants.
- [x] og:image card + social-preview meta wired on the marketing site and validated against the platform preview tools.
- [x] No "RC" / "Release Candidate" in user-facing copy (deployed RC is publicly "open beta"); "pre-release" kept where accurate; internal docs/comments unchanged.
- [x] Branded, guest-safe error pages — 404/500/503/429 plus 403 migrated off the auth-assuming layout; 500 leaks no debug detail (#96).
- [ ] ToS and Privacy Policy drafted and published to the live site.
- [x] Disclaimer copy present at signup, wallet onboarding, and invoice/payment surfaces; footer links to ToS and Privacy Policy on every page.
- [x] Existing UI and mail copy reviewed for overstatements, financial advice language, and pricing commitments — issues resolved.
- [x] Monetization-safe language guide produced for future copy decisions.
- [ ] Content promises catalog reconciled — every open entry confirmed honored or resolved (content revised or product adjusted).
- [ ] Contributor docs reviewed and current.
- [ ] Phase 8 (Micro-Monetize) scoped (BTC-only vs. BTC+fiat decided); donation surface live if kept in MS19; AFCU business account opened when a first fiat inflow warrants it — see [`19.8`](../strategies/19.8_MICRO_MONETIZE.md). (Stub; may spin out to its own milestone.)
- [ ] Email 2FA available as opt-in; recovery flow per the Phase 9 decision in place.
- [ ] Recommendation surface for users without 2FA enabled is shipped.
- [ ] TOTP shipped if MS19 time-cutoff met; otherwise explicitly deferred to the 2028 release.
