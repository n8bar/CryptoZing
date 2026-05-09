# Finding 2: "Use current rate" button label misrepresents invoice rate behavior

Date: 2026-05-08
Date fixed: 2026-05-08 (commit 33489f2)

## Summary

The "Use current rate" button on the invoice create/edit form fetches the latest BTC/USD rate and updates the form's `btc_rate` input. The label suggests pressing it changes how the *invoice* uses rates — implying the invoice has a frozen rate that the button switches over to current. In reality, every viewed invoice (issuer or client) always displays the current rate; the button only refreshes the issuer's form-input snapshot at creation/edit time.

Root cause: the label describes the button as toggling invoice behavior when it only refreshes a form helper.

## Behavior

- Button location: invoice create page (`/invoices/create`) and edit page (`/invoices/{id}/edit`). Issuer-only surface; not present on public/client views.
- Click action: fetch latest rate via the `invoices.rate` route, drop the rate into the `btc_rate` form input, recalculate the BTC amount field (USD ÷ rate).
- Form-level helper text already states: *"This rate is just for display—each payment uses the USD/BTC rate captured at the moment funds arrive."*
- Invoice viewing (issuer dashboard, public link, print) always shows the current rate, not a stored snapshot.

## Why the current label is misleading

Reading "Use current rate" reasonably implies that *not* pressing it leaves the invoice locked to a stale rate. The form-helper text contradicts this implication, but the button label is the dominant signal. New issuers infer behavior the product doesn't have, then second-guess invoices they've already created.

## Decision

Rename the button to **"Refresh rate"**.

- Concise (three syllables), fits the button visually.
- Accurately describes the action: a fetch-and-update of the form input.
- Does not suggest anything about how the invoice "uses" rates.
- Aligns with the surrounding helper text, which already framed the action as a refresh.

Helper text updated correspondingly: *"Amounts auto-calculate as you type. Press 'Refresh rate' to update."*

## Files Touched

- `resources/views/invoices/create.blade.php` — button label, helper text reference, JS reset text.
- `resources/views/invoices/edit.blade.php` — same three locations.
