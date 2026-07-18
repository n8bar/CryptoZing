# MS9 - Print & Public Polish

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-11-17 through 2025-11-18.
Canonical requirements descendant: [`docs/specs/PRINT_PUBLIC_POLISH.md`](../specs/PRINT_PUBLIC_POLISH.md)

> Reconstructed on 2026-07-18 from the prospective print/public spec, implementation commits, historical PLAN, and feature tests. The three phases below describe evidence-backed implementation clusters; they were not named or checked off as formal phases at the time.

## Objective

Make private print and public invoice output present one coherent billing identity, expose payable versus unavailable states clearly, and give owners controlled per-invoice branding overrides.

## Reconstruction basis

The prospective spec landed in `0428384`. Branding defaults and per-invoice overrides landed in `318156f`; active/disabled public-state presentation landed in `2372100`; and the branding heading, collapsible authoring controls, final tests, and completed PLAN rollup landed through `c4280cf`, `d6845a2`, and `b9ac793`.

## Phase Rollup

### [x] Phase 1 - Branding Model & Overrides

Add owner-level billing defaults, invoice-level overrides, and shared resolution of the effective billing identity used by invoice output. See [`x9.1_BRANDING_MODEL_OVERRIDES.md`](../strategies/x9.1_BRANDING_MODEL_OVERRIDES.md).

### [x] Phase 2 - Print & Public State Presentation

Align private/public invoice presentation, preserve payment/rate context for active shares, and hide payment details behind a friendly unavailable state when a share is disabled or expired. See [`x9.2_PRINT_PUBLIC_STATE_PRESENTATION.md`](../strategies/x9.2_PRINT_PUBLIC_STATE_PRESENTATION.md).

### [x] Phase 3 - Authoring UX & Verification

Add the customizable heading, place branding fields in a collapsible create/edit section, lock QR presentation, and verify private/public rendering. See [`x9.3_AUTHORING_UX_VERIFICATION.md`](../strategies/x9.3_AUTHORING_UX_VERIFICATION.md).

## Exit Criteria

- [x] Owners can define billing identity and footer defaults.
- [x] Individual invoices can override those defaults without changing the owner profile.
- [x] Print and active public output render the same effective billing details.
- [x] Disabled or expired public shares reveal no payment details and provide a friendly contact path.
- [x] Payment state, outstanding context, rate timing, and QR presentation remain legible.
- [x] Feature tests cover customizable fields and public unavailable-state behavior.

## Historical boundary

MS9 polished the then-current print/public templates. MS13 later consolidated those surfaces into a shared rendering structure and performed a broader UX pass. Logo uploads, rich templates, and multilingual templates remained out of scope.
