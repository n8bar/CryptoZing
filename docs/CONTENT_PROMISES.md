# Content Promises Catalog

A living catalog of promises and expectations that customer-facing content sets about CryptoZing — both directly and implicitly. Maintained as content is written so the product can be trued up to it before RC (see MS19).

## Why this exists

Every article, landing page, and piece of marketing copy makes commitments — sometimes deliberate, often accidental. If we ship a product that contradicts what we have already said in public, we look dishonest. The fix is to catch these as we write them, so the product team has a clear list to design and verify against.

## When to add an entry

Add to this catalog whenever you write or revise content that:
- Makes a direct claim about how CryptoZing works
- Implies a behavior or capability through framing
- Omits something the reader will reasonably assume isn't there
- Criticizes a competitor or pattern in a way that binds CZ by contrast

Do not add facts about Bitcoin or other external systems that the product cannot affect — those are not promises, they are statements of fact.

This catalog is curated, not exhaustive. Entries that describe core architectural commitments CZ would only violate through a deliberate, conscious redesign (e.g., going custodial, adding chargebacks) are intentionally excluded — cataloging them adds noise without reducing risk. If a future scan surfaces a promise that isn't listed here, check whether it was already considered and excluded before adding it.

## Four flavors of promise

1. **Direct claim** — explicit assertion about CZ. _"CryptoZing does not hold your funds."_
2. **Implicit framing** — language that implies a behavior without stating it. _"You choose your fee" implies user-controlled fee selection._
3. **Critical omission** — silence on something a reader will assume. _Not mentioning a service fee implies there isn't one._
4. **Potential hypocrisy** — criticizing a behavior in others that, if CZ does the same thing, would make the content hypocritical. The most insidious flavor — easy to commit while focused on landing the criticism, and tends to bind the product more strictly than positive promises.

## Major vs Minor

**Major** entries could bite us through inattention, drift, or an unresolved product decision. These need individual reconciliation in MS19 — walk each one, confirm the product honors it or revise the content.

**Minor** entries describe core functionality or criticize patterns CZ would only replicate through deliberate, conscious action. Verify in bulk during MS19 (a single pass confirming the product still does what it does), but don't burn time on individual reconciliation.

## Entry format

Each entry gets a stable ID. IDs are never reused or renumbered after assignment, so references from MS19 audits, commits, and conversations remain valid.

Major entries use the full format:

```
**ID [Flavor]** — short summary
- Source: file:line (and quoted text if useful)
- Binding detail (flavor-dependent: Implies/We criticized/etc.)
- Required product behavior or resolution path
- Status: [open | committed | content revised]
```

Minor entries use the abbreviated format:

```
**ID [Flavor]** — short summary
- Source: file:line
```

## Resolution philosophy

When an entry is a **critical omission** about a future product decision (for example, silence on whether CZ charges a fee), the default resolution is **proactive acknowledgment in content** — add neutral language now that leaves room for the future decision, rather than waiting and revising later. Reactive resolution looks like changing tune; proactive resolution sets expectations honestly upfront.

## Reconciliation

Every major entry needs individual reconciliation before RC. Minor entries get a bulk verification pass. MS19 will include a phase to walk this catalog and either confirm the product honors each entry or trigger a content revision. New entries added after MS19 reconciliation will need to be caught in the next pre-release pass.

---

# Catalog

## 1. Major

Entries with real concern — product decisions pending, content that could contradict the product through drift, or unresolved questions that need active reconciliation in MS19.

**1. [Potential hypocrisy]** — "taking a cut they expect you to absorb"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:17`
- We criticized: traditional payment processors for taking hidden, absorbed fees
- Corollary that binds CZ: if CZ takes a per-transaction cut the same way, we are hypocritical
- Resolution path: (a) make any CZ fee structurally different — customer-visible, or non-per-transaction (subscription, freemium, etc.); or (b) narrow the criticism in content
- Status: open — pending monetization decision

**2. [Direct claim]** — CZ charges 0% fee (beta only)
- Source: `site/learn/btcpay-server-alternatives.md:129` (comparison table: "0% (beta)"), `:124` (_"No fees during beta. Long-term pricing hasn't been decided; free is still on the table, and if not, the intent is to keep it very low."_)
- Required product behavior: CZ must not charge fees during beta. Post-beta pricing is explicitly left open in copy.
- Product verification: confirmed — no fee logic in codebase
- Status: open — pending monetization decision. Related to 1. Copy now scoped to beta only.

**3. [Direct claim]** — stated current limitations
- Source: `site/learn/btcpay-server-alternatives.md:124`, `:129` (comparison table)
- Lists: no Lightning, no CSV/JSON export, no recurring invoices, no QuickBooks, basic client management, dashboard-only reporting
- Required product behavior: if and when any of these ship, update the article to match
- Status: open

## 2. Minor

Low-concern entries — core product behavior or patterns CZ would only violate through deliberate action. Verify in bulk during MS19.

**1. [Potential hypocrisy]** — criticism of "applying chargeback rules"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:17`

**2. [Potential hypocrisy]** — criticism of "deciding when you actually get your money"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:17`

**3. [Implicit framing]** — USD-denominated invoicing as the standard solution
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:31`

**4. [Implicit framing]** — invoices have an expiration window
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:41`

**5. [Implicit framing]** — good invoicing workflow distinguishes pending vs confirmed
- Source: `site/learn/bitcoin-pending-vs-confirmed-payments.md:87`

**6. [Implicit framing]** — invoice attribution matters (right transaction, right amount, right invoice)
- Source: `site/learn/bitcoin-pending-vs-confirmed-payments.md:85`

**7. [Direct claim]** — "open-source"
- Source: `site/index.html:4` (meta description), `:22` (JSON-LD)

**8. [Direct claim]** — USD-first invoices with unique Bitcoin addresses
- Source: `site/index.html:225`, `site/learn/what-is-a-bitcoin-invoice.md:60`

**9. [Direct claim]** — QR payments
- Source: `site/index.html:235`

**10. [Direct claim]** — live BTC conversion
- Source: `site/index.html:235`

**11. [Direct claim]** — on-chain payment tracking via dedicated receiving addresses
- Source: `site/index.html:225`, `:235`

**12. [Direct claim]** — watch-only xpub architecture; never holds or accesses private keys
- Source: `site/learn/btcpay-server-alternatives.md:122`

**13. [Direct claim]** — automatic on-chain payment detection
- Source: `site/learn/btcpay-server-alternatives.md:122`

**14. [Direct claim]** — self-hostable via Docker without requiring a full Bitcoin node. Escalates to Major if Lightning enters scope (Lightning typically requires a node).
- Source: `site/learn/btcpay-server-alternatives.md:122`, `:149`

**15. [Direct claim]** — hosted option with simple onboarding. Escalates to Major if we decide not to host a CZ server.
- Source: `site/learn/btcpay-server-alternatives.md:122`

**16. [Implicit framing]** — "Full" invoicing (comparison table tier)
- Source: `site/learn/btcpay-server-alternatives.md:129`

**17. [Implicit framing]** — noncustodial settlement means no intermediary holds funds
- Source: `site/learn/what-is-a-bitcoin-invoice.md:68`

## 3. Resolved

Entries where the concern has been fully addressed and no ongoing action is needed.

**1. [Critical omission, resolved proactively]** — silence on CZ fees
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md` (article-wide)
- Resolution: added neutral acknowledgment — _"Some are free, some charge a fee, and the model often depends on what you need."_ Ongoing fee concerns are tracked separately in 1.1 and 1.2.
- Status: content revised
