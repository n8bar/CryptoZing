# MS2 - Invoice UX Foundations

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-10-18 through 2025-11-04.

> Reconstructed on 2026-07-18 from git history and historical PLAN snapshots. The four phases below describe evidence-backed implementation clusters; they were not named or closed as formal phases at the time.

## Objective

Establish the first usable client and invoice lifecycle: application/client foundations, invoice CRUD and status behavior, payment/print output, and controlled public sharing with usable rate presentation.

## Reconstruction basis

The primary evidence runs from project bootstrap commit `aa0d045` through the client/invoice UI merge `4f40d97` and rate/show follow-ups `23205a2`, `9fbb216`, and `5208f6b`. Historical PLAN first grouped these already-shipped capabilities into MS2 on 2025-11-07 and recorded the milestone complete by commit `a961df0`.

MS2's number is not chronological relative to MS1: most MS2 implementation landed before the ownership-policy pass was later labeled MS1.

## Phase Rollup

### [x] Phase 1 - Platform & Client Foundations

Bootstrap the authenticated Laravel/Sail application, establish the per-user client/invoice domain, and ship client CRUD plus trash recovery. See [`x2.1_PLATFORM_CLIENT_FOUNDATIONS.md`](../strategies/x2.1_PLATFORM_CLIENT_FOUNDATIONS.md).

### [x] Phase 2 - Invoice Lifecycle

Ship invoice CRUD, trash recovery, status transitions, per-user numbering, invoice dates, and the initial USD/BTC form behavior. See [`x2.2_INVOICE_LIFECYCLE.md`](../strategies/x2.2_INVOICE_LIFECYCLE.md).

### [x] Phase 3 - Payment & Print UX

Add BIP21 payment instructions, copy controls, server-rendered QR output, and the first printable invoice. See [`x2.3_PAYMENT_PRINT_UX.md`](../strategies/x2.3_PAYMENT_PRINT_UX.md).

### [x] Phase 4 - Public Sharing & Rate Stabilization

Add expiring public-share links and stabilize cached/refresh rate presentation across show, print, and public output. See [`x2.4_PUBLIC_SHARING_RATE_STABILIZATION.md`](../strategies/x2.4_PUBLIC_SHARING_RATE_STABILIZATION.md).

## Exit Criteria

- [x] Authenticated users can manage clients and invoices through complete CRUD/trash flows.
- [x] Invoices support the initial lifecycle statuses, numbering, dates, and USD/BTC presentation.
- [x] Show and print surfaces provide BIP21 and QR payment instructions.
- [x] Public invoice links support enable, disable, rotate, and expiry controls with noindex handling.
- [x] Rate refresh and cached display behavior are usable enough for the subsequent correctness pass.

## Historical boundary

MS2 established the first product surface before the later strategy-doc and feature-test discipline. MS3 added broad regression coverage, MS4 tightened currency/rate rules, and MS5 replaced manual payment-address handling with derived addresses. Those later guarantees are not attributed to MS2.
