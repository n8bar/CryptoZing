# MS11 - Observability & Safety

Status: Complete (retrospective reconstruction).
Historical execution date: 2025-11-21.

> Reconstructed on 2026-07-18 from the detailed PLAN expansion, one cohesive implementation commit, and the completed PLAN rollup. MS11 was compact enough for its phases and detailed checklists to remain in this milestone doc; there are no separate strategy docs. The phase boundaries are retrospective execution groupings, not contemporaneous phase gates.

## Objective

Add enough operational signals and guarded failure paths to diagnose payment, rate, delivery, public-access, database, cache, and wallet-derivation failures without exposing sensitive invoice data.

## Reconstruction basis

PLAN defined the observability and safety pass in `05dde78` and added wallet validation in `7d9e29a`. The implementation landed as one cohesive commit, `e1aaef7`. Commits `6e0e0e4` and `083a15b` then marked and moved the milestone complete.

## Phase 1. [x] Define the operational signal boundary.

   1. [x] Identify payment detection, BTC-rate retrieval, queued/delivered mail, and public-link access as the first structured-log surfaces.
   2. [x] Require invoice, delivery, transaction, status, and request context where appropriate.
   3. [x] Avoid logging raw public tokens by recording a one-way token hash.
   4. [x] Define database and cache checks for the health endpoint.
   5. [x] Add wallet-key validation and derivation failure handling to the milestone before implementation.

## Phase 2. [x] Instrument core payment, rate, delivery, and public flows.

   1. [x] Log detected payment identity, sats, invoice status, and outstanding sats.
   2. [x] Log failed BTC-rate responses and exceptions.
   3. [x] Log successful rate-cache refreshes with source and value context.
   4. [x] Log queued and sent invoice deliveries with invoice, delivery, type, and recipient context.
   5. [x] Log public-print access with invoice ID, hashed token, active state, and request IP.

## Phase 3. [x] Replace the static health response with dependency checks.

   1. [x] Add an invokable health controller.
   2. [x] Probe the database with a lightweight query.
   3. [x] Probe cache by writing and reading a short-lived key.
   4. [x] Return `200` only when both checks pass; return `500` with per-check booleans otherwise.
   5. [x] Log dependency-check failures without returning exception details to the caller.

## Phase 4. [x] Guard wallet derivation failures.

   1. [x] Keep syntactic public-key validation in the wallet request.
   2. [x] Derive an address before accepting a saved wallet key so invalid inputs fail early.
   3. [x] Preserve submitted input and return a field-level wallet error on derivation failure.
   4. [x] Catch invoice-creation derivation failures around the transaction.
   5. [x] Redirect to Wallet Settings with a friendly corrective message instead of surfacing an exception.

## Phase 5. [x] Close the milestone.

   1. [x] Record structured logging, health checks, public/error safeguards, and xpub validation in completed PLAN.
   2. [x] Leave richer metrics, dashboards, and wallet guidance to later milestones.
   3. [x] Preserve existing external-service timeout and mail-alias safeguards while adding the new signals.

## Exit Criteria

- [x] Core payment, rate, delivery, and public-access paths emit structured operational logs.
- [x] Public access logs do not contain the raw share token.
- [x] `/health` reflects database and cache availability with an appropriate HTTP status.
- [x] Invalid public wallet keys are rejected by a real derivation attempt before save.
- [x] Invoice derivation failures return the user to Wallet Settings with recoverable input and guidance.

## Historical boundary

MS11 was a baseline instrumentation pass, not a complete observability platform. The implementation commit added no dedicated regression-test file for the new health/logging behavior, and the planned metrics counters were not shipped. Later milestones added support dashboards, service monitoring, queue safeguards, and more rigorous wallet validation.
