# tests/

- Run tests through Sail: `./vendor/bin/sail artisan test`.
- Detailed testing standards: [`docs/qa/tests/TESTING_STANDARDS.md`](../docs/qa/tests/TESTING_STANDARDS.md).
- Test fixtures, factories, and seed data follow the watch-only invariant (see `AGENTS.md`) — no private keys or seed phrases in tracked test data. Testnet funding keys belong in untracked `.cybercreek/`, never under `tests/`.
