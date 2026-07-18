# MS3 - Test Hardening

Status: Complete (retrospective reconstruction).
Historical execution date: 2025-11-07.
Surviving prospective checklist: [`docs/qa/tests/TEST_HARDENING.md`](../qa/tests/TEST_HARDENING.md)

> Reconstructed on 2026-07-18 from the surviving test draft and its implementation commits. MS3 was small enough for its phases and detailed checklists to remain in this milestone doc; there are no separate strategy docs. The phase boundaries are retrospective execution groupings, not contemporaneous phase gates.

## Objective

Add baseline feature coverage around the highest-risk MS1/MS2 behavior: public links, rate refresh/cache behavior, QR/BIP21 output, and soft-delete recovery.

## Reconstruction basis

The test draft landed in `b67ee67` (PR #16). Implementation is recorded by `2cf23cb`, `110a487`, `aa39b22` (PR #19), and `110f6d4` (PR #20), followed by the completed-milestone summary in historical PLAN commit `a961df0` and the matching 2025-11-07 changelog entry.

## Phase 1. [x] Define the scenarios before implementation.

   1. [x] Record preconditions, requests, and stable assertions.
   2. [x] Cover public-share lifecycle and crawler directives.
   3. [x] Cover rate caching, refresh, and failure behavior.
   4. [x] Cover BIP21/QR output and trash recovery.

## Phase 2. [x] Harden public sharing.

   1. [x] Prove enable, disable, and token rotation mutate the expected fields.
   2. [x] Prove another user cannot toggle sharing.
   3. [x] Prove active public output carries noindex headers.
   4. [x] Prove private print output does not inherit public noindex metadata.
   5. [x] Prove expired public tokens no longer return invoice content.

## Phase 3. [x] Harden rate and payment output.

   1. [x] Prove show reuses a cached rate without a network call.
   2. [x] Prove explicit refresh fetches a new rate and changes rendered output.
   3. [x] Prove rate-fetch failure leaves the invoice usable.
   4. [x] Prove show and print render the expected BIP21/QR controls.

## Phase 4. [x] Harden trash flows.

   1. [x] Prove deleted clients and invoices appear in trash and can be restored.
   2. [x] Prove a non-owner cannot permanently delete another user's client or invoice.

## Phase 5. [x] Close the milestone.

   1. [x] Keep scenarios deterministic with factories, authenticated requests, faked time, and mocked network/rate behavior.
   2. [x] Land the tests in their named feature-test files.
   3. [x] Mark Test Hardening complete in PLAN and the changelog.

## Exit Criteria

- [x] Public-share lifecycle and noindex behavior have feature coverage.
- [x] Rate cache, refresh, and failure paths have feature coverage.
- [x] BIP21/QR show and print output have feature coverage.
- [x] Client/invoice trash, restore, and ownership safeguards have feature coverage.

## Historical boundary

MS3 was a targeted baseline, not a claim of exhaustive application coverage. Later milestones expanded and reshaped these tests as wallet, payment, delivery, notification, and UX behavior evolved.
