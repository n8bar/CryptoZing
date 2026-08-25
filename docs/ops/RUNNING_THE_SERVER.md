# Running the Server

Operating a live CryptoZing server and recovering it when something goes wrong. Read it when something is wrong, not on a schedule.

Standing up a server in the first place is a different job — see [`get-live/README.md`](get-live/README.md).

Everything here applies to any CryptoZing instance. It holds no deployment's specifics; those belong with whoever owns that deployment.

---

## What runs, and what that means

The app, queue worker, and scheduler all run from the same image, and **each holds its own copy of the environment**. Changing a value in `.env` and recreating only the app leaves the workers running the old configuration — an app that reports the new setting while a worker still acts on the old one. Recreate every service, not just the app.

Recreating is also what makes an `.env` change take at all: the container rebuilds its config cache at boot, so editing the file without recreating changes nothing.

**The payment watcher is not a service.** It is a per-minute scheduled run inside the scheduler. Stopping the scheduler stops the watcher.

### Reading health honestly

`docker compose ps` reports health per service: the app answers a PHP-FPM ping, while the queue and scheduler answer on a heartbeat their worker loops refresh — so a worker still running but wedged reads `unhealthy` rather than fine.

Two things that look like faults and are not:

- **Healthy containers are not proof that scheduled work is running.** The watcher's own run stamp is the liveness signal, and the support dashboard's stale tile reads from it. Watch the stamp advance.
- **A high restart count on the queue container is expected.** The worker runs with `--max-time=3600` and exits hourly by design, so roughly one restart per hour of uptime is normal. Compare the count against uptime before treating it as a symptom.

---

## Backups

Whatever schedule you keep, two properties matter more than frequency.

**Encrypt dumps, and hold the key somewhere the server cannot reach.** That is what makes a stolen server not also a stolen database. It also means **the server cannot restore itself** — a restore runs from wherever the decrypting identity lives. Know which machine that is before you need it.

**Keep `APP_KEY` somewhere safe and separate.** Backups are unreadable without it.

**Dump `cache`, `cache_locks`, and `sessions` schema-only.** They are regenerable, and capturing their rows causes real damage: the scheduler holds a `withoutOverlapping()` mutex in `cache_locks` while the payment watcher runs, so a dump cut at that moment captures the lock. Restoring it reinstates the lock, and the watcher silently does not run until it expires — up to a day, with every container reading healthy ([#174](https://github.com/n8bar/CryptoZing/issues/174)). Restoring `sessions` likewise revives logins from the backup's moment.

### Restoring

1. Decrypt the dump on the machine that holds the identity.
2. **Confirm the dump is newer than any repair you care about.** A nightly is up to 24 hours stale and will happily undo the same day's fixes.
3. Restore into a scratch database first and check it: table count, row counts, and one value you know the answer to.
4. Restore over live only after that check passes.
5. If the dump predates the schema-only change above, clear stale scheduler locks afterward — `DELETE FROM cache_locks WHERE \`key\` LIKE '%framework/schedule%'`.

---

## Halt procedure

Run this when something has gone wrong and the priority is to stop the bleeding, not to diagnose. Diagnose after the server is quiet.

This sequence has been rehearsed end to end against a live server. The notes marked **rehearsal** are things that went wrong while proving it, not hypotheticals.

**1. Stop the watcher and queue worker**, so nothing auto-acts on bad state.
Stop the `queue` and `scheduler` services. Leave the app up; a reachable app that does nothing is easier to reason about than a dark server.

**2. Set `MAIL_OUTBOUND_ENABLED=false`** and recreate the app so it holds the change.
Mail is the one side effect that leaves your control entirely, so it stops first among the things still running.

**3. Roll back to the previous image tag.**
Set `CZ_TAG` to the previously deployed sha and bring the stack up. Prior images are cached locally, so this needs no registry pull and no network. Check first whether the rollback crosses a migration boundary — older code against a newer schema is its own outage.

> **rehearsal:** bringing the stack up restarts the queue and scheduler that step 1 stopped. Stop them again immediately after and confirm it, or the workers act on the state you are still repairing.

**4. Restore the database** per *Restoring* above, if the fault reached the data.

**5. Bring the server back up** and confirm service health before letting anything resume.
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

- Standing a server up, updating, and backup basics: [`get-live/README.md`](get-live/README.md)
- Correction tooling behavior and its audit trail: [`../specs/PAYMENT_CORRECTIONS.md`](../specs/PAYMENT_CORRECTIONS.md)
