# MS19 - RC Hardening & Ops

> **Stub** — high-level scope and decisions recorded. Phase strategy docs and detailed exit criteria to be written when this milestone becomes active.

Status: Not started.
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

## Phases
_(Phase strategy docs to be written when this milestone becomes active.)_

- Phase 1 — Notification coverage audit
- Phase 2 — Auth/password policy hardening
- Phase 3 — Legal layer: ToS draft, Privacy Policy draft, disclaimer copy, monetization-neutral copy review, UI placement
- Phase 4 — Content promises reconciliation
- Phase 5 — Contributor docs review and update

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
