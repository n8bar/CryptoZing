# PLAN
_Last updated: 2026-09-09_

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
  - **MS22 - Open Beta Product & Growth Iteration** — started 2026-09-07; product-and-growth direction confirmed, revised milestone scope drafted for review. Content-publish gate waived by n8 on the custody article shipped the weekend M21 closed.
- Status: `M22 now covers growth first and late-milestone product expansion; no phase is active until the revised phase scope is reviewed and approved.`
- Next action: Review [`docs/milestones/22_OPEN_BETA_PRODUCT_GROWTH.md`](milestones/22_OPEN_BETA_PRODUCT_GROWTH.md); after approval, draft the M22.1 strategy for the post-launch baseline and full-surface audit.
- Most recently completed milestone doc: [`docs/milestones/x21_OB_DEPLOYMENT.md`](milestones/x21_OB_DEPLOYMENT.md)

## Published Release Target
- **First public release: mid-to-late 2027.** The open beta milestone (MS21, closed 2026-09-06) covers the open beta. The published release target accounts for post-open-beta work needed before an official first release.

## Active and Upcoming Milestones
| Status | ID | Milestone | Short intent | Target | Primary doc |
|---|---|---|---|---|---|
| [ ] | 22 | Open Beta Product & Growth Iteration | Establish the post-launch discovery and marketing practice, then specify, build, test, and release invoice line items plus a bounded late-selected set of backlog features. | 2027-10-06 | [`docs/milestones/22_OPEN_BETA_PRODUCT_GROWTH.md`](milestones/22_OPEN_BETA_PRODUCT_GROWTH.md) |

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
| [x] | 19 | Open Beta Hardening & Ops | Open-beta hardening before mainnet cutover: notification coverage, auth/session hardening, LLC formation, legal layer, content-promises reconciliation, contributor docs, micro-monetize (donations), and 2FA (email + TOTP). | [`docs/milestones/x19_OB_HARDENING_OPS.md`](milestones/x19_OB_HARDENING_OPS.md) |
| [x] | 20 | Mainnet Cutover & Alpha Gate | Provision production hosting, go live on mainnet privately (real self-sent payment) with an invite-only alpha access gate, migrate the article site off GitHub Pages, and prove a cutover runbook with backout before the public open beta. | [`docs/milestones/x20_MAINNET_CUTOVER_ALPHA_GATE.md`](milestones/x20_MAINNET_CUTOVER_ALPHA_GATE.md) |
| [x] | 21 | CryptoZing.app Deployment (Open Beta) | Open the existing mainnet deployment at `cryptozing.app`, retire the alpha hostname, replace the Pages placeholder while preserving content/SEO, publish the legal layer, and complete rollout sign-off. | [`docs/milestones/x21_OB_DEPLOYMENT.md`](milestones/x21_OB_DEPLOYMENT.md) |
