# MS10 - User Settings

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-11-18 through 2025-11-27.

> Reconstructed on 2026-07-18 from git history, historical PLAN, tests, and the changelog. MS10 was compact enough for its phases and detailed checklists to remain in this milestone doc; there are no separate strategy docs. The phase boundaries are retrospective execution groupings, not contemporaneous phase gates.

## Objective

Add reusable invoice defaults, establish a dedicated invoice-settings surface, retain additional watch-only wallet accounts for future use, and make wallet network selection configuration-driven.

## Reconstruction basis

The milestone was added to PLAN in `57b8def`. Invoice defaults and additional-wallet storage landed in `74342df`; the dedicated Invoice Settings page landed in `5cccad5`; immediate mainnet-first wallet stabilization landed in `7addc60`; and `888d206` explicitly deferred remaining settings polish to MS13.

## Phase 1. [x] Add invoice defaults.

   1. [x] Add per-user default invoice description and payment-term days. %<7933>
   2. [x] Prefill invoice creation from the saved defaults. %<7934>
   3. [x] Apply the defaults server-side when submitted description or due date is absent. %<7935>
   4. [x] Keep an explicitly supplied invoice value authoritative over the default. %<7936>
   5. [x] Add feature coverage for both rendered prefill and persisted fallback behavior. %<7937>

## Phase 2. [x] Store additional watch-only wallet accounts for future use.

   1. [x] Add the `user_wallet_accounts` model, migration, relationship, and factory. %<7945>
   2. [x] Store an owner-scoped label, network, and public extended key. %<7946>
   3. [x] Add owner-authorized create and delete actions. %<7947>
   4. [x] Enforce the original account-count cap. %<7948>
   5. [x] Prove one user cannot delete another user's stored wallet account. %<7949>
   6. [x] Keep additional accounts inactive; do not add invoice wallet selection in this milestone. %<7950>

## Phase 3. [x] Separate invoice settings from profile settings.

   1. [x] Add a dedicated Invoice Settings controller, request, routes, and navigation entry. %<7961>
   2. [x] Move invoice defaults and billing/branding defaults onto the dedicated page. %<7962>
   3. [x] Preserve the existing saved values while changing their editing location. %<7963>
   4. [x] Add feature coverage for updating the dedicated settings page. %<7964>

## Phase 4. [x] Make wallet network configuration-driven.

   1. [x] Read the active network from `WALLET_NETWORK` through wallet configuration. %<7983>
   2. [x] Remove the user-selectable network field from primary and additional wallet forms. %<7984>
   3. [x] Apply the configured network server-side when saving either wallet type. %<7985>
   4. [x] Display the effective network as read-only context in Wallet Settings. %<7986>
   5. [x] Record the mainnet-first behavior in the changelog and completed PLAN rollup. %<7987>

## Phase 5. [x] Close the milestone and hand off UX polish.

   1. [x] Mark User Settings complete in historical PLAN. %<8002>
   2. [x] Keep invoice creation, settings persistence, and wallet-account ownership under feature coverage. %<8003>
   3. [x] Move broader visual/interaction cleanup to the MS13 UX Overhaul instead of expanding this milestone. %<8004>

## Exit Criteria

- [x] Users can save invoice description, term, billing, and branding defaults. %<8011>
- [x] Invoice creation consumes defaults without overriding explicit input. %<8012>
- [x] Invoice settings have a dedicated authenticated surface. %<8013>
- [x] Additional public wallet accounts can be stored and removed only by their owner. %<8014>
- [x] Wallet network comes from deployment configuration rather than user input. %<8015>
- [x] Future multi-wallet invoice selection remains explicitly out of scope. %<8016>

## Historical boundary

Additional wallet accounts were storage groundwork only and were later hidden from the open-beta UI. MS14 replaced the original loose multi-wallet assumptions with key-aware lineage and cursor safety. MS13 later owned the broader settings and wallet UX work.
