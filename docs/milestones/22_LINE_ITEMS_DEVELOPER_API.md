# MS22 — Invoice Line Items and Developer API

Status: Draft for review; milestone direction agreed, detailed feature requirements and phases not approved for implementation.
Drafted: 2026-09-28.
Parent: [PLAN.md](../PLAN.md)

## Outcome

An issuer can build a USD invoice from line items in the app. A developer can create and send those invoices through an API, get each one's public payment link, and poll its payment state. This release needs no webhooks.

## Scope Boundary

- Keep the watch-only wallet, dedicated invoice addresses, USD-canonical totals, and the payment-attribution caveats in [PRODUCT_SPEC.md](../PRODUCT_SPEC.md).
- Approve each feature spec before code. This draft sets the boundary, not the design.
- Out of scope: payment-event webhooks (an [M23](23_OPEN_BETA_PRODUCT_GROWTH.md) candidate), custody, Lightning, conversion, refund automation, and a hosted storefront.

## Current Focus

- Active phase: **None — this draft awaits review.**
- Next action after approval: write and approve the line-item feature spec, then the M22.1 implementation strategy.

## Phase Rollup

### [ ] Phase 1 — Itemized invoice foundation %<2963>

Approve the line-item spec, then build the data and total model and every invoice surface: create/edit, issuer view, public page, print, and mail. Each row is a description, quantity, and USD rate; rows sum to the total. The spec settles tax, discount, subtotal, migration of existing invoices, and manual/in-person settlement before code starts. Verify migration, payment presentation, mail, accessibility, and regression on dev.

### [ ] Phase 2 — Developer API contract and access %<2964>

Approve the API spec, then build and verify the access boundary and contract. The API serves developers building their own consumer. The spec covers credential lifecycle and scopes, request/response shapes, safe retries, owner isolation, revocation, rate limits, stable documented errors, status meanings, and polling guidance. Status must tell apart queued mail, delivered mail, detected payment, and confirmed settlement. A detected but unconfirmed payment is not safe to fulfill; the consumer decides from the confirmed state and handles partial, late, overpaid, corrected, or uncertain payments.

### [ ] Phase 3 — Create, send, read, and integration verification %<2965>

Build the documented endpoints on the same invoice and delivery rules as the app: create an invoice, optionally queue its email, return its public link, and read its payment and delivery state. A consumer polls the read endpoint until payment reaches the state it needs. Verify safe retries, queued-send outcomes, payment-state reads, an external-order polling scenario, documentation examples, and the full Sail and applicable browser/UX suite.

### [ ] Phase 4 — Controlled release and live validation %<2966>

Record a dev verdict and migration/backout plan; obtain rollout approval; release and verify itemized invoices, API access, create/send/read behavior, service health, and the content-promises catalog on production.

## Exit Criteria

- [ ] Approved line-item and API specs define the release behavior before code work starts. %<2968>
- [ ] Itemized invoices preserve USD-canonical totals and behave consistently across app, public, print, mail, and payment surfaces. %<2969>
- [ ] A credential limited to one issuer can create and optionally queue-send an itemized invoice, retrieve its public link and current state, and cannot access another issuer's data. %<2970>
- [ ] Retrying an API create request cannot duplicate an invoice; API responses and documentation distinguish queued mail, delivery outcome, detected payment, and confirmed settlement. %<2971>
- [ ] A documented consumer can reconcile an external order by polling, including partial or uncertain payment states, without requiring outbound webhooks. %<2972>
- [ ] Targeted, regression, full Sail, and applicable browser/mobile/accessibility checks pass; dev and production verdicts are recorded after approved rollout. %<2973>
- [ ] The [content promises catalog](../CONTENT_PROMISES.md) is checked against all M22 public claims before closure. %<2974>
