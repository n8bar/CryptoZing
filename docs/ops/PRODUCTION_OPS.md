# Production Operations

How the CryptoZing production deployment is operated and recovered. Standing reference for the maintainer — read it when something is wrong, not on a schedule.

This is not the open-beta cutover. That is a one-time milestone operation and lives with MS21.

Self-hosting your own CryptoZing instance is a different job — see [`get-live/README.md`](get-live/README.md).

---

## How the box is driven

Production is driven with one-shot `ssh deploy@<box> '<command>'` invocations from `/opt/cryptozing`. Never a lingering remote shell.

**Every `docker compose` command on our deployment takes both compose files:**

```
docker compose -f compose.production.yaml -f compose.alpha.yaml <command>
```

`compose.production.yaml` alone is the recipe published for self-hosters. Our box adds `compose.alpha.yaml`, which contributes the article-site container and swaps the front nginx to the `/learn`-proxying, noindexing config. An `up -d` that omits the overlay drops the site container and silently reconfigures nginx. `ps` and `exec` read the running containers either way, which is what makes the omission easy to miss until it bites.

### Services

The app, queue worker, and scheduler all run from the same image, each holding **its own copy of the environment**. An app reporting new config while a worker still runs the old one is the failure mode behind "recreate every service, not just the app."

The payment watcher is a per-minute scheduled run inside the scheduler, not a service of its own. Stopping the scheduler stops the watcher.

### Health semantics

`docker compose ps` reports health per service: the app answers a PHP-FPM ping, while the queue and scheduler answer on a heartbeat their worker loops refresh — so a worker still running but wedged reads `unhealthy` rather than fine.

Healthy containers are not proof that scheduled work is running. The watcher's own run stamp is the liveness signal, and the support dashboard's stale tile reads from it.

A high restart count on the queue container is not by itself a fault: the worker runs with `--max-time=3600` and exits hourly by design, so roughly one restart per hour of uptime is expected. Compare the count against uptime before treating it as a symptom.

---

## Backups

A nightly systemd timer dumps the database on the box, `age`-encrypted to a key held off-box, and the dev machine pulls the dumps on its own timer. The box holds no credentials toward the backup store.

Two consequences worth knowing before you need them:

- **The box cannot restore itself.** The decrypting identity lives off-box in the password manager, so a restore runs from the dev machine.
- **Backups are unreadable without `APP_KEY`.** It is in the password manager. Keep it there.

`cache`, `cache_locks`, and `sessions` are dumped schema-only. They are regenerable, and capturing their rows caused a real outage: a dump cut while the watcher held its `withoutOverlapping()` mutex reinstated that lock on restore and silently stopped the watcher until it expired ([#174](https://github.com/n8bar/CryptoZing/issues/174)).

### Restoring

1. Decrypt the dump on the dev machine with the `age` identity.
2. **Confirm the dump is newer than any repair you care about.** A nightly is up to 24 hours stale and will happily undo the same day's fixes.
3. Restore into a scratch database first and check it: table count, row counts, and one value you know the answer to.
4. Restore over live only after that check passes.
5. If the dump predates the #174 fix, clear stale scheduler locks afterward — `DELETE FROM cache_locks WHERE \`key\` LIKE '%framework/schedule%'`. Dumps cut since carry no lock rows.

---

## Halt procedure

Run this when something has gone wrong and the priority is to stop the bleeding, not to diagnose. Diagnose after the box is quiet.

Rehearsed end to end against production. The notes marked **rehearsal** are things that went wrong while proving it, not hypotheticals.

**1. Stop the watcher and queue worker**, so nothing auto-acts on bad state.
`stop queue scheduler`. Leave the app up; a reachable app that does nothing is easier to reason about than a dark box.

**2. Set `MAIL_OUTBOUND_ENABLED=false`** and recreate the app so it holds the change.
Mail is the one side effect that leaves your control entirely, so it stops first among the things still running. Recreating is what makes this take — the container rebuilds its config cache at boot, so editing `.env` alone changes nothing.

**3. Roll back to the previous image tag.**
Set `CZ_TAG` to the previously deployed sha and `up -d` **with both compose files**. Prior images are cached on the box, so this needs no registry pull and no network. Check first whether the rollback crosses a migration boundary — older code against a newer schema is its own outage.

> **rehearsal:** `up -d` restarts the queue and scheduler that step 1 stopped. Re-run `stop queue scheduler` immediately after and confirm it, or the workers act on the state you are still repairing.

**4. Restore the database** per *Restoring* above, if the fault reached the data.

**5. Bring the box back up** and confirm service health before letting anything resume.
Watch for the watcher's run stamp to actually advance — healthy containers are not proof the scheduled work resumed.

### What a halt cannot undo

State these plainly before starting, because a halt believed to undo more than it does causes the second incident.

- **Coins already sent.** Bitcoin does not have a rollback. A restored database will not know about a payment that arrived after the dump, and the watcher will re-detect it when it resumes.
- **Mail already delivered.** Every notice that reached an inbox stays there, including any that was wrong.
- **Webhook events already accepted** by the provider, and any third party that has already acted on them.
- **Anything a customer already saw** — a public invoice link open in a browser, a page already loaded.
- **Time.** A restore returns the database to the dump, so anything recorded between the dump and the halt is gone unless it is re-derivable from the chain.

---

## Reference

- Deployment recipe, including first start, updating, and backups: [`get-live/README.md`](get-live/README.md)
- Correction tooling behavior and its audit trail: [`../specs/PAYMENT_CORRECTIONS.md`](../specs/PAYMENT_CORRECTIONS.md)
