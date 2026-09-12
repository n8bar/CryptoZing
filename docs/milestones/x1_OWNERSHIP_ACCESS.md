# MS1 - Ownership & Access

Status: Complete (retrospective reconstruction).
Historical execution date: 2025-11-06.

> Reconstructed on 2026-07-18 from git history, the changelog, historical PLAN snapshots, and contemporaneous tests. MS1 was small enough for its phases and detailed checklists to remain in this milestone doc; there are no separate strategy docs. The phase boundaries are retrospective execution groupings, not contemporaneous phase gates.

## Objective

Enforce strict per-user boundaries around clients and invoices, including trashed-resource actions, and return a safe, consistent denied-state experience.

## Reconstruction basis

The primary evidence is commits `97ce5ba` and `984f664` (PRs #12 and #13), the 2025-11-07 PR #13 entry in [`docs/CHANGELOG.log`](../CHANGELOG.log), and the completed-milestone summary recorded in historical PLAN commit `a961df0`. Where summaries and implementation differ, the shipped code and tests take precedence.


## Phase 1. [x] Establish policy boundaries.

   1. [x] Add `ClientPolicy` and `InvoicePolicy`. %<7492>
   2. [x] Permit authenticated creation/listing while restricting view, update, delete, restore, and force-delete to the owning user. %<7493>
   3. [x] Register both policies with Laravel's authorization layer. %<7494>
   4. [x] Apply resource authorization to normal controller actions. %<7495>
   5. [x] Keep index and trash queries scoped to the authenticated user. %<7496>
   6. [x] Explicitly authorize restore and force-delete after loading trashed records by ID. %<7497>

## Phase 2. [x] Standardize denied-state handling.

   1. [x] Route authorization failures through shared exception handling. %<7501>
   2. [x] Add a shared `403` view that does not expose the protected resource. %<7502>
   3. [x] Align its visible message with the literal copy asserted by the feature test. %<7503>
   4. [x] Follow the initial PR #12 policy pass with the PR #13 controller cleanup and denial-copy correction. %<7504>

## Phase 3. [x] Verify the milestone.

   1. [x] Prove one user cannot view another user's client. %<7506>
   2. [x] Prove one user cannot view another user's invoice. %<7507>
   3. [x] Assert both requests return `403` and render the friendly denial copy. %<7508>
   4. [x] Record Ownership & Access as complete in PLAN. %<7509>

## Exit Criteria

- [x] Client and invoice policies enforce owner-only resource access. %<7511>
- [x] Trash restore and permanent-delete paths authorize the loaded record. %<7512>
- [x] Cross-user client and invoice requests return the shared safe `403` response. %<7513>
- [x] The shipped behavior is represented by regression tests. %<7514>

## Historical boundary

The original feature test covered cross-user viewing, not an exhaustive mutation matrix. MS3 later added force-delete ownership coverage. Historical source code used `owner`; the later MS17 issuer-language sweep did not change what MS1 originally implemented.
