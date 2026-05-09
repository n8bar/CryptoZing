# node_scripts/

- `derive-address.cjs` is the BTC HD-derivation helper, invoked from PHP via `App\Services\HdWallet`.
- It handles xpub-derived addresses only (watch-only). Do not extend it to accept private keys, seed phrases, or signing material — that violates the watch-only invariant in `AGENTS.md`.
- Direct `node` invocations are for debugging; production calls go through the PHP service.
