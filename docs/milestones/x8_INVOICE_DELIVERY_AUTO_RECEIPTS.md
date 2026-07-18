# MS8 - Invoice Delivery & Auto Receipts

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-11-12 through 2025-11-17.
Canonical requirements descendant: [`docs/specs/NOTIFICATIONS.md`](../specs/NOTIFICATIONS.md)

> Reconstructed on 2026-07-18 from the prospective Invoice Delivery spec, implementation commit, tests, historical PLAN, and the changelog. The three phases below separate distinct implementation concerns that landed together; they were not formal contemporaneous phase gates.

## Objective

Let invoice owners queue a public-link email to a client, keep an auditable delivery record, send the original automatic paid receipt, and prevent pre-production mail from reaching real recipients.

## Reconstruction basis

The prospective delivery contract landed in `7a087a5`. The delivery ledger, send workflow, queue job, mailables, paid event/listener, receipt preference, public-host setting, alias containment, and tests landed together in `dd8092e` on the historical `codex/invoice-delivery` workstream. Historical PLAN and the 2025-11-15 changelog entry record the completed outcome.

## Phase Rollup

### [x] Phase 1 - Delivery Workflow & Ledger

Persist delivery attempts, authorize and validate manual invoice sends, queue an auditable send intent, and expose delivery history on the invoice. See [`x8.1_DELIVERY_WORKFLOW_LEDGER.md`](../strategies/x8.1_DELIVERY_WORKFLOW_LEDGER.md).

### [x] Phase 2 - Queued Mail & Automatic Receipts

Render and send invoice mail through a queue-backed job, dispatch the paid event, and queue the original automatic client receipt when its eligibility rules pass. See [`x8.2_QUEUED_MAIL_AUTO_RECEIPTS.md`](../strategies/x8.2_QUEUED_MAIL_AUTO_RECEIPTS.md).

### [x] Phase 3 - Pre-Production Mail Safety

Use an explicit public URL in recipient-facing links, rewrite recipients through a configurable catch-all in non-production, and verify both enabled and disabled alias paths. See [`x8.3_PREPRODUCTION_MAIL_SAFETY.md`](../strategies/x8.3_PREPRODUCTION_MAIL_SAFETY.md).

## Exit Criteria

- [x] An authorized owner can queue an invoice email when the client email and public share are available.
- [x] Manual sends and receipts create delivery-history rows with recipient, type, status, timing, and error fields.
- [x] Queue jobs render the appropriate invoice or receipt mailable and update delivery outcome.
- [x] The original paid transition can queue one client receipt when the user preference is enabled.
- [x] Public links in email use the explicitly configured recipient-facing host.
- [x] Pre-production aliasing can redirect both To and CC recipients without changing the disabled path.

## Historical boundary

MS8 shipped automatic paid receipts as the original policy. Later mail milestones expanded notification classes, hardened idempotency and retries, and replaced automatic client receipts with owner-reviewed manual receipt sending for RC1. The current notifications spec is canonical for that descendant behavior; this milestone records what MS8 actually delivered.
