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

## Entry format

Each entry gets a stable ID of the form `section.entry` (e.g. `2.1`). IDs are assigned in catalog order and never reused or renumbered after assignment, so references from MS19 audits, commits, and conversations remain valid.

For direct claims, implicit framing, and critical omissions:

```
**N.M [Flavor]** — short summary
- Source: file:line (and quoted text if useful)
- Implies: what the reader takes away
- Required product behavior: what the product must do (or not do) to honor this
```

For potential hypocrisy:

```
**N.M [Potential hypocrisy]** — short summary
- Source: file:line (and quoted text)
- We criticized: what behavior, in whom
- Corollary that binds CZ: what CZ must avoid
- Resolution path: (a) commit to avoiding the pattern — with what that means concretely, or (b) revise the content to be narrower
- Status: [open | committed | content revised]
```

For low-risk entries — where the bound behavior would only happen through deliberate, conscious action, not by accident, carelessness, or negligence — use the abbreviated format:

```
**N.M [Flavor, low risk]** — short summary
- Source: file:line
```

Core CryptoZing functionality automatically qualifies as low-risk.

## Resolution philosophy

When an entry is a **critical omission** about a future product decision (for example, silence on whether CZ charges a fee), the default resolution is **proactive acknowledgment in content** — add neutral language now that leaves room for the future decision, rather than waiting and revising later. Reactive resolution looks like changing tune; proactive resolution sets expectations honestly upfront.

## Reconciliation

Every entry needs to be reconciled before RC. MS19 will include a phase to walk this catalog, audit the product against it, and either confirm the product honors each entry or trigger a content revision, copy tweak, or a light behavior tweak if it fits in the scope. New entries added after MS19 reconciliation will need to be caught in the next pre-release pass.

---

# Catalog

## 1. Custody model

**1.1 [Potential hypocrisy]** — Custodial framing as "an intermediary right back in the middle"
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:59` — _"Using a custodial service puts an intermediary right back in the middle."_
- We criticized: custodial Bitcoin tools, framed unfavorably (lock-out risk, hack risk, "trusting a third party with your money")
- Corollary that binds CZ: CZ should not be expanded to be a custodial service, or this article makes us hypocritical
- Resolution path: (a) commit to noncustodial design — Bitcoin goes directly to user-controlled wallet, CZ never holds funds — and verify product enforces this; or (b) revise the article to soften the custodial criticism (loses positioning value)
- Status: open — awaiting product confirmation

**1.2 [Implicit framing]** — Noncustodial framed as "more direct and less dependent on intermediaries"
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:59` — _"The whole point of accepting Bitcoin is that it is supposed to be more direct and less dependent on intermediaries."_
- Implies: CZ aligns with the noncustodial model and the values it represents
- Required product behavior: CZ does not insert itself as custodial intermediary; user holds keys; payments flow directly to user-controlled addresses
- Status: open

## 2. Pricing and fees

**2.1 [Potential hypocrisy]** — "taking a cut they expect you to absorb"
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:17`
- We criticized: traditional payment processors for taking fees that the merchant is expected to absorb (and effectively hide from the customer)
- Corollary that binds CZ: if CZ takes a per-transaction cut and expects the merchant to absorb it the same way, we are hypocritical
- Resolution path: (a) make any CZ fee structurally different — customer-visible line item on the invoice, or non-per-transaction model (subscription, freemium, etc.); or (b) revise the content to narrow the criticism (e.g., focus on the layered/surprise nature, not the absorption pattern)
- Status: open — pending monetization decision (see "Leave Room for CZ Monetization" memory)

**2.2 [Critical omission, resolved proactively]** — silence on CZ fees
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md` (article-wide)
- Implied (originally): by not mentioning any CZ service fee, reader may assume there isn't one
- Resolution: added neutral acknowledgment in section 1 — _"Some are free, some charge a fee, and the model often depends on what you need."_ This breaks the silence without committing CZ to either model.
- Status: content revised

## 3. Reversibility and settlement

**3.1 [Potential hypocrisy, low risk]** — criticism of "applying chargeback rules"
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:17`

## 4. Account control and gatekeeping

**4.1 [Potential hypocrisy, low risk]** — criticism of "deciding when you actually get your money"
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:17`

**4.2 [Potential hypocrisy, low risk]** — criticism of "occasionally deciding you do not get it at all"
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:17`

## 5. Workflow expectations

**5.1 [Implicit framing, low risk]** — USD-denominated invoicing as the standard solution
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:31`

**5.2 [Implicit framing, low risk]** — invoices have an expiration window
- Source: `site/staging/accepting-bitcoin-payments-freelancer-small-business.md:41`

## 6. Pilot article — bitcoin-pending-vs-confirmed-payments

**6.1 [Implicit framing, low risk]** — good invoicing workflow distinguishes pending vs confirmed
- Source: `site/learn/bitcoin-pending-vs-confirmed-payments.md:87`

**6.2 [Implicit framing, low risk]** — invoice attribution matters (right transaction, right amount, right invoice)
- Source: `site/learn/bitcoin-pending-vs-confirmed-payments.md:85`
