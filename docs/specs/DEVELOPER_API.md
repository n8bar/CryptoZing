# Developer API

Status: Draft.
Parent: [MS22](../milestones/22_LINE_ITEMS_DEVELOPER_API.md), Phases 2 and 3.

## 1. Goals

1. A developer creates an invoice for an issuer without using the app.
2. The developer can have it sent by email and gets its public payment link.
3. The developer reads its mail and payment state until it reaches the state they need.

## 2. Keys

1. A key belongs to one issuer.
2. The issuer makes and revokes keys in the app.
3. Requests are rate limited per key.

## 3. Creating an invoice

1. A request carries the client, the lines, and whether to send the invoice.
2. The invoice follows the same rules as one made in the app.
3. Retrying a request never makes a second invoice.
4. The response carries the invoice, its public link, and its state.

## 4. Reading an invoice

1. A read returns the invoice, its public link, and its state.
2. Mail state: not sent, queued, delivered, or failed.
3. Payment state: none, seen, confirmed, partial, overpaid, or corrected.
4. Only confirmed is safe to fulfill. The developer decides what to do with every other state.

## 5. Changing an invoice

1. A draft can be edited or deleted.
2. Once the public link is on, the invoice cannot change. Delete it and create a new one.
3. Deleting keeps the record and any payments on it.

## 6. Errors

1. Errors are documented and stable.
2. An error names the field or the rule that failed.

## 7. Out of scope

1. Webhooks and other push notifications.
2. Anything other than invoices.
