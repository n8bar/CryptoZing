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

Entries that need active reconciliation — product decisions pending, content that could contradict the product through drift, or hypocrisy bindings with real teeth.

**1. [Potential hypocrisy]** — Custodial framing as "an intermediary right back in the middle"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:59` — _"Using a custodial service puts an intermediary right back in the middle."_
- We criticized: custodial Bitcoin tools, framed unfavorably (lock-out risk, hack risk, "trusting a third party with your money")
- Corollary that binds CZ: CZ must not become custodial, or this article makes us hypocritical
- Resolution path: (a) commit to noncustodial design — Bitcoin goes directly to user-controlled wallet, CZ never holds funds; or (b) revise the article to soften the custodial criticism (loses positioning value)
- Product verification: confirmed — watch-only xpub architecture, no signing capability, no key storage
- Status: open — awaiting formal product commitment

**2. [Potential hypocrisy]** — "taking a cut they expect you to absorb"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:17`
- We criticized: traditional payment processors for taking hidden, absorbed fees
- Corollary that binds CZ: if CZ takes a per-transaction cut the same way, we are hypocritical
- Resolution path: (a) make any CZ fee structurally different — customer-visible, or non-per-transaction (subscription, freemium, etc.); or (b) narrow the criticism in content
- Status: open — pending monetization decision

**3. [Critical omission, resolved proactively]** — silence on CZ fees
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md` (article-wide)
- Resolution: added neutral acknowledgment — _"Some are free, some charge a fee, and the model often depends on what you need."_
- Status: content revised

**4. [Direct claim]** — "open-source"
- Source: `site/index.html:4` (meta description), `:22` (JSON-LD) — _"CryptoZing is an open-source Bitcoin invoicing app"_
- Required product behavior: repository must remain public under an open-source license at launch
- Product verification: confirmed — MIT license, public GitHub repo
- Status: open — verify license choice is final before RC

**5. [Direct claim]** — CZ charges 0% fee (beta only)
- Source: `site/learn/btcpay-server-alternatives.md:129` (comparison table: "0% (beta)"), `:124` (_"No fees during beta. Pricing for the general release has not been decided."_), `:140` (_"CryptoZing is in open beta and free"_)
- Required product behavior: CZ must not charge fees during beta. Post-beta pricing is explicitly left open in copy.
- Product verification: confirmed — no fee logic in codebase
- Status: open — pending monetization decision. Related to 2. Copy now scoped to beta only.

**6. [Direct claim]** — self-hostable via Docker without requiring a full Bitcoin node
- Source: `site/learn/btcpay-server-alternatives.md:122`, `:149`
- Required product behavior: Docker self-host path must not require a local full node
- Product verification: confirmed — uses Mempool.space API, no bitcoind dependency
- Status: open — verify this remains true if payment detection architecture changes

**7. [Direct claim]** — hosted option with simple onboarding
- Source: `site/learn/btcpay-server-alternatives.md:122` — _"just sign up, complete the quick walk-through and send an invoice"_
- Required product behavior: hosted CZ must offer a streamlined signup-to-first-invoice flow
- Status: open — hosted offering must exist at launch for this to hold

**8. [Implicit framing]** — "Full" invoicing (comparison table tier)
- Source: `site/learn/btcpay-server-alternatives.md:129` (table: Invoicing = "Full")
- Implies: CZ invoicing is on par with BTCPay and Zaprite — line items, client details, payment tracking
- Status: open — verify feature set justifies "Full" label at RC

**9. [Direct claim]** — stated current limitations
- Source: `site/learn/btcpay-server-alternatives.md:124`, `:129` (comparison table)
- Lists: no Lightning, no CSV/JSON export, no recurring invoices, no QuickBooks, basic client management, dashboard-only reporting
- Required product behavior: if any of these ship before launch, update the article to match
- Status: open

## 2. Minor

Entries describing core product behavior or criticizing patterns CZ would only replicate through deliberate action. Verify in bulk during MS19.

**1. [Implicit framing]** — Noncustodial framed as "more direct and less dependent on intermediaries"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:59`

**2. [Potential hypocrisy]** — criticism of "applying chargeback rules"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:17`

**3. [Potential hypocrisy]** — criticism of "deciding when you actually get your money"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:17`

**4. [Potential hypocrisy]** — criticism of "occasionally deciding you do not get it at all"
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:17`

**5. [Implicit framing]** — USD-denominated invoicing as the standard solution
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:31`

**6. [Implicit framing]** — invoices have an expiration window
- Source: `site/learn/accepting-bitcoin-payments-freelancer-small-business.md:41`

**7. [Implicit framing]** — good invoicing workflow distinguishes pending vs confirmed
- Source: `site/learn/bitcoin-pending-vs-confirmed-payments.md:87`

**8. [Implicit framing]** — invoice attribution matters (right transaction, right amount, right invoice)
- Source: `site/learn/bitcoin-pending-vs-confirmed-payments.md:85`

**9. [Implicit framing]** — self-custody ethos as brand identity ("not your keys, not your coins")
- Source: `site/index.html:240`

**10. [Direct claim]** — USD-first invoices with unique Bitcoin addresses
- Source: `site/index.html:225`

**11. [Direct claim]** — QR payments
- Source: `site/index.html:235`

**12. [Direct claim]** — live BTC conversion
- Source: `site/index.html:235`

**13. [Direct claim]** — on-chain payment tracking via dedicated receiving addresses
- Source: `site/index.html:225`, `:235`

**14. [Direct claim]** — watch-only xpub architecture; never holds or accesses private keys
- Source: `site/learn/btcpay-server-alternatives.md:122`

**15. [Direct claim]** — automatic on-chain payment detection
- Source: `site/learn/btcpay-server-alternatives.md:122`

**16. [Direct claim]** — noncustodial (listed alongside BTCPay, Blockonomics, etc.)
- Source: `site/learn/btcpay-server-alternatives.md:49`

**17. [Implicit framing]** — good invoicing tools generate a unique address per invoice
- Source: `site/learn/what-is-a-bitcoin-invoice.md:60`

**18. [Implicit framing]** — noncustodial settlement means no intermediary holds funds
- Source: `site/learn/what-is-a-bitcoin-invoice.md:68`
