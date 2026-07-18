# MS4 - Rate & Currency Correctness

Status: Complete (retrospective reconstruction).
Historical execution date: 2025-11-07.
Canonical requirements descendant: [`docs/specs/RATES.md`](../specs/RATES.md)

> Reconstructed on 2026-07-18 from one cohesive implementation/specification pass. MS4 was small enough for its phases and detailed checklists to remain in this milestone doc; there are no separate strategy docs. The phase boundaries are retrospective execution groupings, not contemporaneous phase gates.

## Objective

Make USD the stable invoice source amount, derive BTC consistently at satoshi precision, and make cached/refresh rate behavior predictable across show, print, and public output.

## Reconstruction basis

The primary evidence is commit `754a955` (PR #21), which introduced the original `docs/RATES.md`, refactored display/rate behavior, and expanded feature tests. The 2025-11-07 changelog entry and historical PLAN commit `a961df0` independently record the milestone complete.

## Phase 1. [x] Lock source-of-truth and formatting rules.

   1. [x] Treat entered USD as canonical.
   2. [x] Derive displayed BTC from USD divided by the applicable rate.
   3. [x] Round BTC to no more than eight decimal places.
   4. [x] Format USD with two decimal places.
   5. [x] Keep BIP21 and QR output aligned with the displayed BTC amount.

## Phase 2. [x] Normalize rate caching and refresh.

   1. [x] Normalize cached payloads to `rate_usd`, `as_of`, and `source`.
   2. [x] Reuse cached entries only inside the one-hour TTL.
   3. [x] Refresh stale or missing entries through the live-rate path.
   4. [x] Keep invoice output usable when both cache and live fetch fail.
   5. [x] Keep explicit refresh display-only rather than replacing stored USD.

## Phase 3. [x] Share formatting across surfaces.

   1. [x] Extract a common invoice-display formatter.
   2. [x] Use it for authenticated show, private print, and public print.
   3. [x] Expose the model BTC formatter for shared use.
   4. [x] Keep public output fresh-rate-first with cached fallback.

## Phase 4. [x] Verify and document the rules.

   1. [x] Extend rate tests for cache freshness, refresh, and fetch failure.
   2. [x] Assert eight-decimal BTC behavior and matching BIP21 output.
   3. [x] Add tracked rate/currency documentation and link it from PLAN/README.
   4. [x] Record Rate & Currency Correctness complete in PLAN and the changelog.

## Exit Criteria

- [x] USD remains canonical across invoice display surfaces.
- [x] BTC formatting never exceeds satoshi precision.
- [x] Cached and refreshed rates follow one documented lifecycle.
- [x] Show, print, public, BIP21, and QR values use shared display rules.
- [x] Regression tests cover the correctness and failure paths.

## Historical boundary

MS4 corrected the original single-payment display model. It did not define per-payment USD snapshots, floating outstanding BTC, or confirmation-aware settlement; MS7 and MS12 added those later. The current rates spec is the canonical descendant and contains later requirements.
