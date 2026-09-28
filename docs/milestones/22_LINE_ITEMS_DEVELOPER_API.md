# MS22 — Invoice Line Items and Developer API

Status: Draft for review; milestone direction agreed, detailed feature requirements and phases not approved for implementation.
Drafted: 2026-09-28.
Parent: [PLAN.md](../PLAN.md)

## Outcome

An issuer can create an itemized, USD-denominated invoice through the app. A developer can then use an owner-scoped API to create and optionally send those invoices, obtain their public payment links, and read current payment state for an external order. The first API release uses polling: the external system periodically requests invoice status. It does not require a CryptoZing payment-event webhook consumer.

## Scope Boundary

- Preserve the watch-only wallet model, dedicated invoice addresses, USD-canonical totals, and existing payment-attribution caveats in [PRODUCT_SPEC.md](../PRODUCT_SPEC.md).
- Phase 1 delivers line items across create/edit, issuer, public, print, and mail surfaces. Multiple description, quantity, and USD rate rows determine the total; the approved line-item spec must settle tax, discount, subtotal, migration, and manual/in-person settlement behavior before code work.
- The API serves developers building their own consumer. It supplies authenticated invoice creation, optional queued email delivery, an invoice/public link response, and a read endpoint with payment and delivery state. A client may poll the read endpoint until payment reaches its required state.
- The API must support safe creation retries, owner isolation, credential revocation, bounded request rates, stable documented errors, and explicit distinction between queued mail, provider delivery, detected payment, and confirmed settlement. Exact contracts belong in an approved feature spec.
- Do not imply that a detected unconfirmed payment is safe to fulfill. The consumer decides fulfillment policy from the documented confirmed state and handles partial, late, overpaid, corrected, or uncertain payment states.
- Outbound payment-event webhooks are a candidate for [M23](23_OPEN_BETA_PRODUCT_GROWTH.md). They are not required for the first API release. No payment custody, Lightning, conversion, refund automation, or hosted storefront is promised here.
- Approve canonical feature requirements before implementation. This draft sets the milestone boundary, not the final API schema or behavior spec.

## Current Focus

- Active phase: **None — this draft awaits review.**
- Next action after approval: write and approve the line-item feature spec, then the M22.1 implementation strategy.

## Phase Rollup

### [ ] Phase 1 — Itemized invoice foundation

Approve the line-item spec, implement the data and total model plus all affected invoice surfaces, and verify migration, payment presentation, mail, accessibility, and regression behavior on dev.

### [ ] Phase 2 — Developer API contract and access

Approve the API feature spec: credential lifecycle and scopes, request/response contract, idempotency, ownership, error and rate-limit behavior, status semantics, and polling guidance. Implement and verify the access boundary and contract.

### [ ] Phase 3 — Create, send, read, and integration verification

Implement the documented endpoints using the same invoice and delivery rules as the app. Verify safe retries, queued-send outcomes, payment-state reads, an external order polling scenario, documentation examples, and the full Sail and applicable browser/UX suite.

### [ ] Phase 4 — Controlled release and live validation

Record a dev verdict and migration/backout plan; obtain rollout approval; release and verify itemized invoices, API access, create/send/read behavior, service health, and the content-promises catalog on production.

## Exit Criteria

- [ ] Approved line-item and API specs define the release behavior before code work starts.
- [ ] Itemized invoices preserve USD-canonical totals and behave consistently across app, public, print, mail, and payment surfaces.
- [ ] A credential limited to one issuer can create and optionally queue-send an itemized invoice, retrieve its public link and current state, and cannot access another issuer's data.
- [ ] Retrying an API create request cannot duplicate an invoice; API responses and documentation distinguish queued mail, delivery outcome, detected payment, and confirmed settlement.
- [ ] A documented consumer can reconcile an external order by polling, including partial or uncertain payment states, without requiring outbound webhooks.
- [ ] Targeted, regression, full Sail, and applicable browser/mobile/accessibility checks pass; dev and production verdicts are recorded after approved rollout.
- [ ] The [content promises catalog](../CONTENT_PROMISES.md) is checked against all M22 public claims before closure.
