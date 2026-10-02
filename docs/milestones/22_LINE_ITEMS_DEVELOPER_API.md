# MS22 — Invoice Line Items and Developer API

Status: Approved; Phase 1 active.
Parent: [PLAN.md](../PLAN.md)

## Outcome

An issuer can build a USD invoice from line items in the app. A developer can create and send those invoices through an API, get each one's public payment link, and poll its payment state. This release needs no webhooks.

## Scope Boundary

- Keep the watch-only wallet, dedicated invoice addresses, USD-canonical totals, and the payment-attribution caveats in [PRODUCT_SPEC.md](../PRODUCT_SPEC.md).
- Approve each feature spec before code. This draft sets the boundary, not the design.
- Out of scope: payment-event webhooks (an [M23](23_OPEN_BETA_PRODUCT_GROWTH.md) candidate), custody, Lightning, conversion, refund automation, and a hosted storefront.

## Current Focus

- Active phase: **Phase 1 — Itemized invoice foundation.**
- Strategy: [M22.1](../strategies/22.1_ITEMIZED_INVOICES.md).

## Phase Rollup

### [ ] Phase 1 — Itemized invoice foundation %<2963>

Approve the line-item spec, then build it. A line is a description, a quantity, and a USD rate. Lines add up to the total. The spec settles tax, discounts, subtotals, old invoices, and in-person payment first. Line items show everywhere an invoice does: edit, issuer view, public page, print, and mail. Check it all on dev.

### [ ] Phase 2 — Developer API contract and access %<2964>

Approve the API spec, then build the keys and the contract. A key belongs to one issuer, can be revoked, and is rate limited. Retrying a request never makes a second invoice. Errors are documented and stable. Status tells apart mail queued, mail delivered, payment seen, and payment confirmed. Only confirmed is safe to fulfill; the developer handles partial, late, overpaid, corrected, or unclear payments.

### [ ] Phase 3 — Create, send, read, and integration verification %<2965>

Build the endpoints on the same rules as the app: create an invoice, send it by email if asked, return its public link, and read its mail and payment state. The developer polls that read until they get the state they need. Test retries, sending, reads, a full order-by-polling run, the doc examples, and the whole suite.

### [ ] Phase 4 — Controlled release and live validation %<2966>

Record the dev verdict and a backout plan. Get the go for rollout. Release, then check line items, API keys, create/send/read, service health, and the content promises on prod.

## Exit Criteria

- [ ] Approved line-item and API specs define the release behavior before code work starts. %<2968>
- [ ] Itemized invoices preserve USD-canonical totals and behave consistently across app, public, print, mail, and payment surfaces. %<2969>
- [ ] A credential limited to one issuer can create and optionally queue-send an itemized invoice, retrieve its public link and current state, and cannot access another issuer's data. %<2970>
- [ ] Retrying an API create request cannot duplicate an invoice; API responses and documentation distinguish queued mail, delivery outcome, detected payment, and confirmed settlement. %<2971>
- [ ] A documented consumer can reconcile an external order by polling, including partial or uncertain payment states, without requiring outbound webhooks. %<2972>
- [ ] Targeted, regression, full Sail, and applicable browser/mobile/accessibility checks pass; dev and production verdicts are recorded after approved rollout. %<2973>
- [ ] The [content promises catalog](../CONTENT_PROMISES.md) is checked against all M22 public claims before closure. %<2974>
