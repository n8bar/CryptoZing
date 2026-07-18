# MS6 - Blockchain Payment Detection

Status: Complete (retrospective reconstruction).
Historical execution window: 2025-11-08 through 2025-11-11.

> Reconstructed on 2026-07-18 from implementation commits, tests, historical PLAN, and the changelog. The three phases below describe evidence-backed implementation clusters; they were not named or closed as formal phases at the time.

## Objective

Poll public blockchain data for each derived invoice address, record detected transaction state, update invoices automatically, and keep the watcher running on the Laravel schedule without introducing signing keys.

## Reconstruction basis

Payment state landed in `8d6da28`, mempool detection and the watcher in `9efc37f`, command registration in `4c75c31`, and scheduling in `a961df0`. The 2025-11-10 changelog entries record the command integration and scheduling outcome. Implementation and contemporaneous tests take precedence where a later PLAN summary overstates confirmation behavior.

## Phase Rollup

### [x] Phase 1 - Payment-Tracking State

Add invoice fields and initial UI/test coverage for detected transaction amounts, confirmations, block height, and timestamps. See [`x6.1_PAYMENT_TRACKING_STATE.md`](../strategies/x6.1_PAYMENT_TRACKING_STATE.md).

### [x] Phase 2 - Mempool Detection & Watcher

Integrate mempool.space, detect full expected payments to invoice addresses, and add the `wallet:watch-payments` command with faked-API feature tests. See [`x6.2_MEMPOOL_DETECTION_WATCHER.md`](../strategies/x6.2_MEMPOOL_DETECTION_WATCHER.md).

### [x] Phase 3 - Scheduling & Operationalization

Register the command and schedule it every minute with overlap protection, background execution, and schedule regression coverage. See [`x6.3_SCHEDULING_OPERATIONALIZATION.md`](../strategies/x6.3_SCHEDULING_OPERATIONALIZATION.md).

## Exit Criteria

- [x] Invoice addresses can be checked through a public blockchain API without signing material.
- [x] Matching transactions record sats, txid, confirmation metadata, and detection timestamps.
- [x] The watcher supports all eligible invoices plus an optional single-invoice filter.
- [x] Command and scheduler behavior have feature coverage.
- [x] The every-minute schedule prevents overlap and runs in the background.

## Historical boundary and later correction

MS6 exposed `BLOCKCHAIN_CONFIRMATIONS_REQUIRED` and recorded confirmation metadata, but the original state transition still marked an invoice `paid` when its matching transaction was unconfirmed; the original test explicitly required that behavior. MS7 added partial-payment records and outstanding summaries. MS12, including `5b8a6f9`, later implemented confirmation-aware payment state. This milestone doc preserves the original boundary rather than attributing later guarantees to MS6.
