# database/migrations/

- Spec-first: align on the requirement in `docs/specs/**` before writing a migration, not after.
- New schema changes need accompanying tests; run `./vendor/bin/sail artisan test` before commit.
- Watch-only invariant applies (see `AGENTS.md`): migrations and seeders must not embed private keys, seed phrases, or signing material — even for testnet scenarios. Funding keys belong in untracked `.cybercreek/`.
