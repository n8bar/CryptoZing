# PLAN
_Last updated: 2026-07-18_

This is the human-facing execution dashboard for open-beta work.

Open this doc first when resuming work.
Use [`docs/PRODUCT_SPEC.md`](PRODUCT_SPEC.md) for global product behavior and invariants.
Use milestone docs under `docs/milestones/` when a milestone is large or active enough to need a checklist-bearing execution doc.
Use supporting specs under `docs/specs/` for detailed local requirements.
Use [`docs/BACKLOG.md`](BACKLOG.md) for post-MVP work only.

## Milestone Conventions
- Every milestone's exit criteria must include a content promises check: confirm no work in the milestone introduced or violated a [`docs/CONTENT_PROMISES.md`](CONTENT_PROMISES.md) entry.
- When milestone target dates change or milestones are added/removed, update [`docs/milestones.ics`](milestones.ics) in the same commit. This file feeds a Proton Calendar subscription.
- A new milestone cannot begin unless at least one article from [`docs/CONTENT_PLAN.md`](CONTENT_PLAN.md) has been published since the previous milestone started. Content production is a parallel track; milestone transitions are the checkpoint.

## Current
- Active milestone:
  - **MS19 - Open Beta Hardening & Ops** — in progress; Phases 1–8 complete. Active phase: **Phase 9 (2FA)**, the last MS19 phase.
- Status: `active`
- Next action: merge PR #132 (M19.8 donations) once PR Tests are green; then MS19 Phase 9 kickoff per [`19.9_TWO_FACTOR_AUTHENTICATION.md`](strategies/19.9_TWO_FACTOR_AUTHENTICATION.md).
- Primary next doc: [`docs/milestones/19_OB_HARDENING_OPS.md`](milestones/19_OB_HARDENING_OPS.md)
- Most recently completed milestone doc: [`docs/milestones/x18_PRERELEASE_CONTENT_SEO.md`](milestones/x18_PRERELEASE_CONTENT_SEO.md)

## Published Release Target
- **First public release: mid-to-late 2027.** The open beta milestone timeline (MS21, targeting 2026-08-25) covers the open beta. The published release target accounts for post-open-beta work needed before an official first release.

## Active and Upcoming Milestones
| Status | ID | Milestone | Short intent | Target | Primary doc |
|---|---|---|---|---|---|
| [ ] | 19 | Open Beta Hardening & Ops | Open beta hardening before mainnet cutover: notification coverage audit; auth/password policy hardening (419-to-login redirect, session expiry logout); parent/subsidiary LLC formation (entities, EINs, operating agreements); legal layer (ToS, Privacy Policy, disclaimers, monetization-neutral copy review, UI placement); content promises reconciliation; contributor docs refresh; micro-monetize decision; 2FA (email baseline, opportunistic TOTP). | 2026-08-01 | [`docs/milestones/19_OB_HARDENING_OPS.md`](milestones/19_OB_HARDENING_OPS.md) |
| [ ] | 20 | Mainnet Cutover Preparation | Define and rehearse env flips, wallet validation, mail sanity checks, and backout steps for mainnet cutover. | 2026-08-13 | [`docs/milestones/20_MAINNET_CUTOVER_PREP.md`](milestones/20_MAINNET_CUTOVER_PREP.md) |
| [ ] | 21 | CryptoZing.app Deployment (Open Beta) | Deploy the open beta under `cryptozing.app`, replace the GitHub Pages placeholder at `/` with the live app landing page without breaking the SEO baseline established in MS15, remove temporary mail aliasing, and complete rollout verification. | 2026-08-25 | [`docs/milestones/21_OB_DEPLOYMENT.md`](milestones/21_OB_DEPLOYMENT.md) |
| [ ] | 22 | Thorough SEO & Marketing Strategies | Post-deploy discovery investment: thorough SEO pass across live app + site surfaces and an ongoing SEO strategy with scheduled tasks in the .ics; marketing strategy. Scope at kickoff. | 2026-09-15 | [`docs/milestones/22_SEO_MARKETING.md`](milestones/22_SEO_MARKETING.md) |

## Completed Milestones
| Status | ID | Milestone | Short intent | Primary doc |
|---|---|---|---|---|
| [x] | 1 | Ownership & Access | Enforce strict owner boundaries and safe denied-state UX. | [`docs/milestones/x1_OWNERSHIP_ACCESS.md`](milestones/x1_OWNERSHIP_ACCESS.md) |
| [x] | 2 | Invoice UX Foundations | Establish invoice CRUD, status flow, BTC/USD display, and public sharing basics. | [`docs/milestones/x2_INVOICE_UX_FOUNDATIONS.md`](milestones/x2_INVOICE_UX_FOUNDATIONS.md) |
| [x] | 3 | Test Hardening | Add baseline feature coverage for public/share, rates, and trash/restore flows. | [`docs/milestones/x3_TEST_HARDENING.md`](milestones/x3_TEST_HARDENING.md) |
| [x] | 4 | Rate & Currency Correctness | Lock USD-canonical rate behavior and shared formatting rules. | [`docs/milestones/x4_RATE_CURRENCY_CORRECTNESS.md`](milestones/x4_RATE_CURRENCY_CORRECTNESS.md) |
| [x] | 5 | Wallet Onboarding & Derived Addresses | Add wallet-key onboarding and per-invoice derived receive addresses. | [`docs/milestones/x5_WALLET_ONBOARDING_DERIVED_ADDRESSES.md`](milestones/x5_WALLET_ONBOARDING_DERIVED_ADDRESSES.md) |
| [x] | 6 | Blockchain Payment Detection | Poll chain activity for invoice addresses and update invoice payment state automatically. | [`docs/milestones/x6_BLOCKCHAIN_PAYMENT_DETECTION.md`](milestones/x6_BLOCKCHAIN_PAYMENT_DETECTION.md) |
| [x] | 7 | Partial Payments & Outstanding Summaries | Record multiple payments, preserve USD snapshots, and surface outstanding balance behavior. | [`docs/milestones/x7_PARTIAL_PAYMENTS_OUTSTANDING_SUMMARIES.md`](milestones/x7_PARTIAL_PAYMENTS_OUTSTANDING_SUMMARIES.md) |
| [x] | 8 | Invoice Delivery & Auto Receipts | Add invoice send flow, delivery logging, and automatic paid receipts. | [`docs/milestones/x8_INVOICE_DELIVERY_AUTO_RECEIPTS.md`](milestones/x8_INVOICE_DELIVERY_AUTO_RECEIPTS.md) |
| [x] | 9 | Print & Public Polish | Align print/public output with branding, status, and public-state expectations. | [`docs/milestones/x9_PRINT_PUBLIC_POLISH.md`](milestones/x9_PRINT_PUBLIC_POLISH.md) |
| [x] | 10 | User Settings | Add invoice defaults and stabilize wallet/settings behavior. | [`docs/milestones/x10_USER_SETTINGS.md`](milestones/x10_USER_SETTINGS.md) |
| [x] | 11 | Observability & Safety | Add safety checks, structured logging, and failure-path hardening. | [`docs/milestones/x11_OBSERVABILITY_SAFETY.md`](milestones/x11_OBSERVABILITY_SAFETY.md) |
| [x] | 12 | Payment & Address Accuracy | Correct derivation mismatches and lock confirmation-aware payment accuracy. | [`docs/milestones/x12_PAYMENT_ADDRESS_ACCURACY.md`](milestones/x12_PAYMENT_ADDRESS_ACCURACY.md) |
| [x] | 13 | UX Overhaul | Deliver dashboard/theme/help/onboarding/settings IA and close Task 13 Browser QA. | [`docs/milestones/x13_UX_OVERHAUL.md`](milestones/x13_UX_OVERHAUL.md) |
| [x] | 14 | On-Chain Payment Attribution Hardening | Make attribution key-aware, detect unsupported wallet reuse, reinforce dedicated-account usage, and provide auditable correction tooling. | [`docs/milestones/x14_PAYMENT_ATTRIBUTION_HARDENING.md`](milestones/x14_PAYMENT_ATTRIBUTION_HARDENING.md) |
| [x] | 15 | CryptoZing.app SEO Bootstrap | Get the placeholder/landing page discovered, indexed, and monitored early before go-live. | [`docs/milestones/x15_CRYPTOZING_APP_SEO_BOOTSTRAP.md`](milestones/x15_CRYPTOZING_APP_SEO_BOOTSTRAP.md) |
| [x] | 16 | Mailer & Alerts Polish + Audit | Restore trustworthy outbound mail, delivery safeguards, truthful notification model, sequence-keyed past-due scheduling, persistent queue worker, and Mailgun webhook delivery status feedback. | [`docs/milestones/x16_MAILER_AND_ALERTS_POLISH_AUDIT.md`](milestones/x16_MAILER_AND_ALERTS_POLISH_AUDIT.md) |
| [x] | 17 | Product Readiness | Rationalize the test suite, replace "owner" with "issuer" in all UI and mail copy, add service health monitoring to the support dashboard, and extend the getting-started flow with a post-payment receipt step. | [`docs/milestones/x17_PRODUCT_READINESS.md`](milestones/x17_PRODUCT_READINESS.md) |
| [x] | 18 | Pre-Release Content & SEO | Extend the site from a single placeholder to a lightweight content site with educational articles, adapted Helpful Notes, and a staging path — giving search engines substance to rank before RC1 ships. | [`docs/milestones/x18_PRERELEASE_CONTENT_SEO.md`](milestones/x18_PRERELEASE_CONTENT_SEO.md) |
