# UX Guardrails (Reference)

Industry-aligned practices we should apply across UX work (esp. MS13). These are based on established frameworks:

- **Nielsen’s 10 Heuristics**: match real-world language, visibility of status, user control, consistency, error prevention/recovery, recognition over recall, efficiency for power users, minimalist copy, clear errors/help.
- **WCAG 2.2 AA**: focus order, keyboard operability, status messages, contrast, target sizes, headings/labels; verify forms respect accessibility for validation states.
- **GovUK / USWDS Form Guidance**: inline errors near fields, preserve input on failure, focus the first errored field, concise hints, no modals for basic validation, readable plain language.
- **Material / Apple HIG Form Patterns**: label + helper text + error slot, calm validation states, adequate spacing and touch targets, avoid layout shift when showing errors.
- **Fintech Trust Cues (Stripe-style)**: clear status/feedback, avoid jargon, cautious irreversible actions, progressive disclosure for advanced details, friendly recovery paths.

Project-specific non-negotiables:
- Mainnet-first wallet UX: network derives from env; show a small testnet helper only when not mainnet; no noisy badges on mainnet.
- Inline guidance over pop-ups: keep help/“Where do I find this?” inline and above the fold; minimal scrolling for critical fields.
- Preserve state: never clear inputs on validation errors; keep CTAs enabled after error; focus on the first error.
- No layout jump: reserve space for helper/error text; avoid page reflow when showing validation.
- Mobile/accessibility: visible focus rings, keyboard navigability for accordions, adequate tap targets, readable on small screens.
- Copy: plain language, avoid jargon, safety reminders (e.g., “receive-only key; never share your seed”), friendly errors.

Monetization-neutral language (copy must leave room for future pricing; CryptoZing is free during beta, but nothing may promise it stays that way):
- Time-bound any free/fee claim: "no fees during beta" is the approved shape; unqualified "free," "always free," "no fees ever" are not.
- State architecture, not pricing: non-custody is a permanent fact and safe to say; absence-of-fees is a pricing decision and isn't.
- Present-tense facts beat forever-promises: "is not a party to this transaction" works; "will never take a cut" doesn't.
- When criticizing competitors, aim at what CZ architecturally can't replicate (custody, chargebacks, settlement delay) — not at fees themselves, which CZ may someday charge.
- Do/don't examples:
  - Don't: "Free — no fees ever." Do: "No fees during beta; long-term pricing hasn't been decided."
  - Don't: "No middleman taking a cut." Do: "Non-custodial — payments go straight to your wallet."
  - Don't: "Keep 100% of what you invoice." Do: "You control the wallet that gets paid."
  - Don't: "We'll never charge for this." Do: "Currently free."
