# Open Beta Rollout Checklist (CryptoZing.app)

This is the doc MS21 executes: the cutover runbook, then the halt procedure if the cutover goes wrong.

The runbook is written from the MS20 mainnet cutover as it actually ran, not from how it was planned. Where a step exists because something bit us, the reason is stated — under pressure, a step with no reason is the first one skipped.

Production is driven with one-shot `ssh deploy@<box> '<command>'` invocations from `/opt/cryptozing`.

**Every `docker compose` command on our deployment takes both compose files:**

```
docker compose -f compose.production.yaml -f compose.alpha.yaml <command>
```

`compose.production.yaml` alone is the recipe published for self-hosters. Our box adds `compose.alpha.yaml`, which contributes the article-site container and swaps the front nginx to the `/learn`-proxying, noindexing config. An `up -d` that omits the overlay drops the site container and silently reconfigures nginx — a bad surprise at any time, and a much worse one mid-halt. `ps` and `exec` read the running containers either way, which is what makes the omission easy to miss until it bites.

---

## Cutover runbook

### 0. Before you touch anything

- [ ] Take a fresh database dump and verify it opens. A halt is only as good as the backup it restores; the nightly is up to 24 hours stale.
- [ ] Confirm the age identity that decrypts the dumps is in hand. It lives off-box in the password manager, so **the box cannot restore itself** — a halt needs the dev machine.
- [ ] Note the currently deployed `CZ_TAG`. That sha is the rollback target, and its image is already cached on the box.
- [ ] Confirm `APP_KEY` is in the password manager. Backups are unreadable without it.

### 1. Environment flips, in the order they have to happen

Order matters here — several of these fail silently if taken out of sequence.

- [ ] DNS resolves the new hostname to the box, and TLS is issued for it **before** anything points at it. Mail sent with links to a hostname that has no certificate is not recoverable by editing env afterward; it is already in someone's inbox.
- [ ] Switch the certbot renewal authenticator to `webroot` if the new hostname was issued standalone. Standalone renewals fail silently once nginx owns port 80, and the failure surfaces sixty days later.
- [ ] Set `CZ_SERVER_NAME` to the new hostname so the nginx template renders for it.
- [ ] Set `APP_URL`, with `APP_PUBLIC_URL` following it, to the new public host. Everything in outbound mail derives from this.
- [ ] Confirm `MAIL_ALIAS_ENABLED=false` and clear `MAIL_ALIAS_DOMAIN`. Aliasing rewrites recipients to the catch-all, which hides exactly the delivery failures this cutover needs to surface.
- [ ] Set `ALPHA_GATE_ENABLED=false` to end the invite-only window.
- [ ] Run migrations before services come up on the new image.
- [ ] Recreate **every** service, not just the app. The queue worker and scheduler each hold their own copy of the environment; an app that reports the new config while a worker still runs the old one is the failure mode this step exists to prevent.

### 2. Wallet validation, before any funds move

Nothing in this section moves money. It runs first precisely so a derivation mismatch costs nothing.

- [ ] `wallet:check-config` exits clean. It fails the deploy when the donation xpub is malformed, is signing material, belongs to the other network, or is the same account key as an onboarded invoice wallet. A shared key derives the same addresses on both chains, so payments collide and attribution is lost — this is the MS14 failure, and the check is the guard against repeating it.
- [ ] Derive the first several invoice addresses on the box and compare them index-by-index against the source wallet. Diff against a fresh derivation, not against a transcript from an earlier step.
- [ ] Derive donation addresses and compare them the same way.
- [ ] Confirm the app does not flag the key as an unsupported wallet configuration.
- [ ] Confirm the invoice and donation chains advance independently — allocating on one key must not move the other's cursor.
- [ ] Any mismatch stops the cutover. Do not proceed to mail.

### 3. Mail sanity

- [ ] `MAIL_*` credentials valid for the production sending domain, with `MAILGUN_ENDPOINT` matching the provider region (`api.mailgun.net` for a US-region domain).
- [ ] **Re-register the Mailgun webhook against the new hostname** for `delivered`, `failed`, and `permanent_fail`. The webhook is registered per-URL; changing the public host silently orphans the old registration and delivery status stops flowing back, leaving the log stuck on queued.
- [ ] Set `MAILGUN_WEBHOOK_SIGNING_KEY` from the dashboard.
- [ ] Send a real invoice email and a paid receipt to a real inbox.
- [ ] Confirm delivery status flows back and lands on the delivery log rather than staying queued.
- [ ] Confirm every link in the sent mail resolves to `APP_PUBLIC_URL` — not localhost, not the apex, not the previous private hostname.
- [ ] Confirm SPF, DKIM, and DMARC all pass and are strictly aligned, read from the `Authentication-Results` header of a message that actually arrived.

### 4. Post-deploy checks, and who signs them off

| | Check | Owner |
|---|---|---|
| [ ] | `docker compose ps` reports healthy per service — the app answers a PHP-FPM ping; queue and scheduler answer on a heartbeat their worker loops refresh, so a wedged-but-running worker reads `unhealthy` rather than fine | Agent |
| [ ] | No pending migrations remain | Agent |
| [ ] | Smoke the core path end to end: dashboard loads, create an invoice, enable its share link, load that link signed out, send a delivery | Agent |
| [ ] | Spot-check invoices and clients for ownership and auth anomalies — no record reachable by an account that should not see it | Agent |
| [ ] | Watcher liveness stamp is fresh, and the support dashboard's stale tile is quiet | Agent |
| [ ] | Spot-check the payment ledger against the chain: address, sats, block, and settlement time | Agent |
| [ ] | Public links carry the right host and retain their noindex headers | Agent |
| [ ] | Logs and alerts reviewed for mail, watcher, and error rates | Agent |
| [ ] | Invoice and receipt mail read in a real client — headers, sender, rendering | User |
| [ ] | Derived receive addresses appear as expected in the source wallets | User |
| [ ] | Final sign-off that the deployment is fit to be public | User |
| [ ] | PLAN and CHANGELOG updated with any scope or operational note the rollout turned up | Agent |

A queue container with a high restart count is not by itself a fault: the worker runs with `--max-time=3600` and exits hourly by design, so roughly one restart per hour of uptime is expected. Compare the count against uptime before treating it as a symptom.

---

## Halt procedure

Run this when the cutover has gone wrong and the priority is to stop the bleeding, not to diagnose. Diagnose after the box is quiet.

This procedure has been rehearsed end to end against production. The notes marked **rehearsal** are things that went wrong while proving it, not hypotheticals.

- [ ] **1. Stop the watcher and queue worker**, so nothing auto-acts on bad state. `stop queue scheduler` — the watcher is a per-minute scheduled run inside the scheduler, not its own service, so stopping the scheduler stops the watcher. Leave the app up; a reachable app that does nothing is easier to reason about than a dark box.
- [ ] **2. Set `MAIL_OUTBOUND_ENABLED=false`** and recreate the app so it holds the change. Mail is the one side effect that leaves your control entirely, so it stops first among the things still running. Recreating is what makes this take — the container rebuilds its config cache at boot, so editing `.env` alone changes nothing.
- [ ] **3. Roll back to the previous image tag.** Set `CZ_TAG` to the sha noted in §0 and `up -d` **with both compose files**. Prior images are cached on the box, so this needs no registry pull and no network. Check first whether the rollback crosses a migration boundary — older code against a newer schema is its own outage.
  - **rehearsal:** `up -d` restarts the queue and scheduler that step 1 just stopped. Re-run `stop queue scheduler` immediately after, and confirm it before moving on, or the workers act on the state you are still repairing.
- [ ] **4. Restore the database from the most recent verified backup.** Dumps are `age`-encrypted with the identity held off-box, so this runs from the dev machine, not from the box. Restore into a scratch database and check it before pointing the app at it: table count, row counts, and one value you know the answer to.
  - Confirm the dump you are restoring is **newer than any repair you care about**. A nightly is up to 24 hours stale and will happily undo the same day's fixes.
  - **rehearsal:** if the dump predates the [#174](https://github.com/n8bar/CryptoZing/issues/174) fix, clear stale scheduler locks after restoring — `DELETE FROM cache_locks WHERE \`key\` LIKE '%framework/schedule%'`. Such a dump captures the watcher's `withoutOverlapping()` mutex if it was cut mid-run, and restoring reinstates it; the watcher then silently does not run until the lock expires, up to ~24 hours, with every container reading healthy. Dumps cut since that fix carry no lock rows and need no manual step.
- [ ] **5. Bring the box back up** and confirm service health before letting anything resume. Watch for the watcher's run stamp to actually advance — healthy containers are not proof the scheduled work resumed.

### What a halt cannot undo

State these plainly before starting, because a halt that is believed to undo more than it does causes the second incident.

- **Coins already sent.** Bitcoin does not have a rollback. A restored database will not know about a payment that arrived after the dump, and the watcher will re-detect it when it resumes.
- **Mail already delivered.** Every notice that reached an inbox stays there, including any that was wrong.
- **Webhook events already accepted** by the provider, and any third party that has already acted on them.
- **Anything a customer already saw** — a public invoice link that was open in a browser, a page already loaded.
- **Time.** A restore returns the database to the dump, so anything recorded between the dump and the halt is gone unless it is re-derivable from the chain.

---

## Reference

- Deployment recipe, including first start, updating, and backups: [`get-live/README.md`](get-live/README.md)
- Correction tooling behavior and its audit trail: [`../specs/PAYMENT_CORRECTIONS.md`](../specs/PAYMENT_CORRECTIONS.md)
