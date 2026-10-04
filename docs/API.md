# CryptoZing API

Status: Approved.
Spec: [DEVELOPER_API.md](specs/DEVELOPER_API.md).

Base URL: `https://cryptozing.app/api/v1`. Every request carries `Authorization: Bearer <key>` and `Accept: application/json`. Bodies are JSON. Times are ISO 8601 in UTC. Money is a string with two decimals for USD and eight for BTC.

## 1. Endpoints

| Method | Path | Does |
|---|---|---|
| POST | `/invoices` | Create an invoice, and send it if asked |
| GET | `/invoices/{id}` | Read an invoice |
| PATCH | `/invoices/{id}` | Edit a draft |
| DELETE | `/invoices/{id}` | Delete an invoice |
| POST | `/invoices/{id}/send` | Email the invoice to the client |

## 2. Create

`POST /invoices` with an `Idempotency-Key` header. Repeating the same key returns the first invoice; the same key with a different body is a `conflict`.

```json
{
  "client": { "name": "Acme Co", "email": "billing@acme.test" },
  "lines": [
    { "description": "Design", "quantity": 2, "rate_usd": "100.00" },
    { "description": "Subtotal", "kind": "subtotal" },
    { "description": "Sales tax", "kind": "percentage", "rate_usd": "8.5", "applies_to": [1] }
  ],
  "due_date": "2026-11-01",
  "send": true
}
```

`kind` is `item` (default), `percentage`, or `subtotal`. `applies_to` names earlier lines by position, starting at 0. The client is matched by email or created.

Response `201` with the invoice (§6).

## 3. Read

`GET /invoices/{id}` returns `200` with the invoice. Poll it until `payment_state` is `confirmed`.

## 4. Edit and delete

`PATCH /invoices/{id}` takes any of `client`, `lines`, `due_date` and returns `200` with the invoice. It is refused with `not_draft` once the public link is on; delete and create instead.

`DELETE /invoices/{id}` returns `204`. The record and any payments stay on file.

## 5. Send

`POST /invoices/{id}/send` queues the email and returns `202` with the invoice. `mail_state` moves from `queued` to `delivered` or `failed`.

## 6. Invoice

```json
{
  "id": "inv_8f3k2",
  "number": "INV-0007",
  "status": "open",
  "client": { "name": "Acme Co", "email": "billing@acme.test" },
  "lines": [
    { "description": "Design", "kind": "item", "quantity": "2.0000", "rate_usd": "100.00", "amount_usd": "200.00" },
    { "description": "Subtotal", "kind": "subtotal", "amount_usd": "200.00" },
    { "description": "Sales tax", "kind": "percentage", "rate_usd": "8.5", "applies_to": [1], "amount_usd": "17.00" }
  ],
  "total_usd": "217.00",
  "paid_usd": "0.00",
  "public_url": "https://cryptozing.app/p/3f9a…",
  "payment": {
    "address": "bc1q…",
    "amount_btc": "0.00251000",
    "uri": "bitcoin:bc1q…?amount=0.00251",
    "rate_usd_per_btc": "86454.18"
  },
  "mail_state": "queued",
  "payment_state": "none",
  "due_date": "2026-11-01",
  "created_at": "2026-10-03T18:20:00Z"
}
```

`mail_state`: `none`, `queued`, `delivered`, `failed`.
`payment_state`: `none`, `seen`, `confirmed`. Only `confirmed` is safe to fulfill. Compare `paid_usd` with `total_usd` to see partial or over payment.

## 7. Errors

```json
{ "error": { "code": "validation", "message": "The rate (USD) field is required.", "field": "lines.0.rate_usd" } }
```

| Status | Code | When |
|---|---|---|
| 401 | `unauthorized` | Missing, unknown, or revoked key |
| 404 | `not_found` | No such invoice for this key's issuer |
| 409 | `conflict` | Idempotency key reused with a different body |
| 409 | `not_draft` | Edit attempted after the public link went on |
| 422 | `validation` | A field failed; `field` names it |
| 429 | `rate_limited` | Too many requests; `Retry-After` says when |
