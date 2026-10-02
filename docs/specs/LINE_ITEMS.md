# Invoice Line Items

Status: Draft skeleton for structural review.
Parent: [MS22](../milestones/22_LINE_ITEMS_DEVELOPER_API.md), Phase 1.

## 1. Goals

1. An issuer builds an invoice from lines instead of one amount.
2. The invoice total is the sum of its lines and stays the USD amount that settlement uses.
3. Lines appear everywhere the invoice does.

## 2. Lines

1. A line has a description, a quantity, and a USD rate.
2. A line's total is quantity times rate, shown in USD.
3. An invoice has at least one line.
4. Lines keep the order the issuer gave them.

## 3. Subtotal, discounts, and tax

_Decision: do lines alone make the total, or can an invoice also carry a discount and a tax line?_

## 4. Total and settlement

1. The total is the sum of the lines, rounded to cents.
2. The total is the canonical USD amount. Payment, partial payment, overpayment, and corrections keep their existing rules.
3. The BTC request derives from the total per [RATES.md](RATES.md).

## 5. Surfaces

1. Create and edit: add, remove, and reorder lines; the total updates as lines change.
2. Issuer invoice view.
3. Public page.
4. Print.
5. Mail: invoice-ready, paid, and receipt messages.
6. Lists and dashboard show the total only.

## 6. Existing invoices

_Decision: how does an invoice made before line items read afterward?_

## 7. In-person settlement

_Decision: does recording a manual payment change with line items, or stay a whole-invoice amount?_

## 8. Out of scope

1. Per-line payment or per-line status.
2. Product catalogs or saved line presets.
3. Currencies other than USD.
