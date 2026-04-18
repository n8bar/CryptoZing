# Research: BTCPay Server Alternatives

Internal notes for article 3. Not published. Researched 2026-04-18.

## Keyword Validation

**Primary keyword: "BTCPay Server alternatives"** — confirmed as the right target.

- ~400-800 monthly searches across the cluster (including "BTCPay alternative," "BTCPay alternative small business," "BTCPay vs" queries)
- Difficulty: low-medium (30-45). Page 1 is aggregator sites (G2, AlternativeTo) with no depth, plus blogs with DA 25-40. Beatable with an opinionated comparison article.
- Intent: commercial investigation — searchers have heard of BTCPay, decided it's too much or are cross-shopping. CZ's wedge.
- One article can capture secondary clusters: "Bitcoin invoicing tools," "noncustodial Bitcoin payments," "how to invoice in Bitcoin" fit naturally in the body.
- Pure "Bitcoin invoicing" keywords have lower volume AND weaker commercial intent. BTCPay provides a recognizable anchor to draft behind.

**Proposed title:** "BTCPay Server Alternatives: Simpler Bitcoin Invoicing for Small Businesses"
- Hits primary keyword, pulls in invoicing cluster, signals audience.

**Secondary article opportunity (future):** "How to Invoice in Bitcoin" — standalone informational piece targeting the invoicing cluster, linking back to this comparison.

### Google Autocomplete Signals

- "BTCPay Server" → login, plugins, github, setup, **alternative**, docker
- "BTCPay vs" → **bitpay** (dominant comparison)
- "Bitcoin invoice" → email, address, template, payment
- "Crypto invoicing" → platform, solutions, saas
- "How to accept bitcoin" → **as a business**, **payments for business**
- "Self hosted bitcoin" → wallet, node, **payment gateway**

### People Also Ask (from SERPs)

- What is the best alternative to BTCPay Server?
- Is BTCPay Server really free?
- BTCPay vs BitPay — what's the difference?
- Does BTCPay Server require self-hosting?
- What is the easiest Bitcoin payment processor?
- How do freelancers invoice in Bitcoin?

### SERP Competition

Page 1 for "BTCPay Server alternatives" is dominated by aggregator/comparison sites:
1. AlternativeTo.net (DA ~70)
2. G2.com (DA ~90)
3. SourceForge.net (DA ~85)
4. ProductHunt.com (DA ~90)
5. BTCPayServer.org docs (DA ~60)
6. SaaSHub.com (DA ~55)
7. Kyrrex.com blog (DA ~35)
8. BeycanPress.com (DA ~25)
9. InstaWP.com (DA ~40)

Bottom half of page 1 is blogs with DA 25-40 — achievable range for cryptozing.app.

### Volume Disclaimer

Exact volumes from paid tools (Ahrefs/Semrush) not available. Estimates triangulated from: confirmed data point ("crypto payment" = 2,400/mo, difficulty 68 via Clicks.so), Google autocomplete depth as proxy, SERP composition, and market size context (BTCPay ~1M GitHub downloads, #435K SimilarWeb).

## BTCPay Server

- **URL:** btcpayserver.org
- **Custody:** Noncustodial. Uses xpub to generate addresses; server never holds private keys. Exception: built-in hot wallet and Lightning node features do hold keys — docs warn about this.
- **Hosting:** NOT self-host only. Three tiers:
  - Self-hosted (VPS, own hardware, 1-click deployers like LunaNode/Voltage)
  - Third-party hosts (community-run or paid; users connect their own wallets, so it stays noncustodial even when hosted)
  - No official hosted offering from BTCPay Foundation
  - Multi-tenant is a first-class feature (one instance, many stores/users)
- **Fees:** Zero. No subscription, no transaction fees, no percentage. MIT license. Only costs are infrastructure (~$10-70/mo for VPS) and network fees.
- **Invoice/client management:** Full invoice system (create, track, filter, export). Payment requests, refunds, pull payments, payouts, reporting. NOT a CRM — no client database or contact management.
- **Lightning:** Yes. Multiple approaches from custodial (Blink plugin) to full self-sovereign (CLN/LND).
- **Maintenance:** Very active. Latest release v2.3.7 (2026-04-02). ~7,500 GitHub stars. .NET/C#.
- **Integrations:** 20+ e-commerce (WooCommerce, Shopify, Magento, etc.). Full REST API (Greenfield). POS app, crowdfunding app, payment buttons.
- **Standout:** The gold standard for self-sovereign Bitcoin payments. Case studies at scale (Namecheap: $73M+ BTC revenue).

## Alternatives

### Noncustodial

**Blockonomics** — blockonomics.co
- Noncustodial via xpub (same model as BTCPay/CZ). No KYC.
- Hosted SaaS.
- 1% of received volume, billed monthly from pre-loaded BTC credit. First 20 tx free.
- Invoice tool with fiat currency support and dynamic rates. Invoices encrypted in-browser.
- Lightning support: unconfirmed — appears on-chain only.
- Active. 20+ e-commerce plugins.
- Notable: encrypted invoices (Blockonomics can't see contents).

**Swiss Bitcoin Pay** — swiss-bitcoin-pay.ch
- Noncustodial. Lightning payments converted to on-chain BTC, forwarded to your wallet every 24h. No KYC.
- Hosted SaaS + mobile app (iOS/Android).
- 0-1% for BTC. 1.5% for fiat payouts (CHF/EUR/USD).
- Basic invoice/billing via mobile app. Monthly tax reports auto-generated.
- Lightning + on-chain + NFC.
- Active. Swiss company. Used widely in Lugano.
- Notable: strong physical retail / POS focus.

**Zaprite** — zaprite.com
- Noncustodial. Not a payment processor — a payment *gateway*. Connects to your wallets/nodes.
- Hosted SaaS.
- $25/mo flat (includes $25/mo in tx fees). No percentage fee from Zaprite. Fiat via connected Stripe.
- Full invoicing platform: client management, payment links, ticketing, virtual POS, API. Supports surcharges/discounts by payment method. Income tracking.
- Lightning (own node or Alby/Strike/Zebedee) + on-chain via xpub.
- Active.
- Notable: the most invoice/business-management-focused tool on this list. Can accept fiat and Bitcoin side-by-side on the same invoice. **Closest competitor to CZ.**

**Coinsnap** — coinsnap.io
- Noncustodial ("Self-Custody Bitcoin Provider"). Payments forward to your Lightning address.
- Hosted SaaS.
- 1% to BTC wallet. 2.49% total if settled to bank (1% + 1.49% broker fee).
- E-commerce plugin focused (40+ plugins). Not a standalone invoicing platform.
- Lightning + on-chain. Sign up with just email + Lightning address.
- Active.
- Notable: lowest barrier to entry. BTCPay philosophy without self-hosting.

**Flash** — paywithflash.com
- Noncustodial. Connects wallets directly.
- Hosted SaaS.
- 0% processor fees. Only network fees.
- Payment links, POS, paywalls, subscriptions. Not full invoicing.
- Lightning-native.
- Early-stage (500-user early access as of 2026). Maturity uncertain.
- Notable: zero fees, digital content / SaaS focus. Covered by Nasdaq (press release). Keyword research flagged Flash as a close positional competitor to CZ — free, noncustodial, no KYC, freelancer-targeted. Maturity and sustainability of 0% model are open questions.

**Breez** — breez.technology
- Noncustodial (self-custodial, user holds keys).
- Mobile app only. No web dashboard.
- 0.4-0.75% channel opening (one-time). Routing fees after that. No monthly fees.
- Built-in POS with item catalog. Not a full invoicing platform.
- Lightning only (no on-chain at POS level).
- Active. Also provides Breez SDK for developers.
- Notable: only self-custodial wallet with built-in merchant POS. Turns a phone into a Lightning cash register.

**Coinbase Commerce** — coinbase.com/commerce
- Noncustodial (payments go directly to merchant).
- Hosted SaaS.
- 1% per tx. Fiat conversion via Coinbase exchange adds ~0.5-1.5%.
- Basic: payment buttons, hosted checkout, API. Not a full invoicing platform.
- Lightning support: unconfirmed for Commerce specifically.
- Active. Shopify x Coinbase x Stripe partnership (2026).
- Notable: Coinbase brand. Focus shifting toward USDC/Base network.

### Custodial

**OpenNode** — opennode.com
- Custodial. KYC required. Optional fiat conversion.
- Hosted SaaS only.
- 1% per tx. No setup or monthly fees.
- Dashboard for creating/emailing invoices. API.
- Lightning + on-chain.
- Active. $24.7M raised. 32 employees.
- Notable: "Stripe-like" Bitcoin onramp. Pure Bitcoin (no altcoins).

**Strike** — strike.me/en/business
- Custodial. USD balances FDIC-insured. BTC not insured.
- Hosted SaaS / API-driven.
- ~0.3-1% spread on Lightning tx.
- Limited invoicing — primarily a payment rail / API. Shopify/NCR partnerships.
- Lightning is core technology.
- Very active. BitLicense (March 2026). BTC-backed loans, bill pay.
- Notable: consumer app with merchant API. Lightning-first.

**CoinGate** — coingate.com
- Custodial.
- Hosted SaaS.
- 1% flat. SEPA payouts free.
- Order management, refunds, permissions, export.
- Lightning enabled by default.
- Active. EU-regulated (MiCA-licensed, Bank of Lithuania).
- Notable: 70+ cryptocurrencies. Strongest regulatory posture.

**BitPay** — bitpay.com
- Custodial (merchant service). Separate noncustodial wallet app.
- Hosted SaaS.
- Tiered: 2%+$0.25 (<$500K/mo), 1.5%+$0.25 ($500K-$999K), 1%+$0.25 ($1M+).
- Full invoice processing. 130,000+ merchants. 150+ fiat currencies.
- Lightning accepted from supported wallets.
- Very active. Processed $1.38B in 2025. Founded 2011.
- Notable: oldest and largest. Also the most expensive and most criticized.

**Speed** — tryspeed.com
- Custodial. Same-day ACH for USD conversion.
- Hosted SaaS.
- 1% per payment. No other fees. Free fiat conversion.
- Invoices, payment links, QR codes, subscription billing.
- Lightning-first. Also USDC/USDT.
- Active. MSB-licensed.
- Notable: simple, low-cost, Lightning-first with stablecoin support.

**NOWPayments** — nowpayments.io
- Both: default noncustodial (forwarded to wallet), optional custodial add-on.
- Hosted SaaS.
- 0.5% same-currency. ~1% with auto-conversion. Volume discounts.
- Invoice creation, payment links, API.
- Lightning support: unconfirmed.
- Active. 350+ supported cryptocurrencies.
- Notable: broadest crypto support. Lowest base fee. Strong altcoin focus.

### CryptoZing (that's us)

**CryptoZing** — cryptozing.app
- Noncustodial. Watch-only architecture using xpub/zpub for per-invoice address derivation. Never holds, requires, or processes private keys.
- Self-hostable (Laravel/Sail/Docker) or hosted SaaS. Shares self-hostability with BTCPay only among tools researched.
- No fees currently. Monetization strategy TBD — content already leaves room for future fees.
- Full invoice management: CRUD, status lifecycle (draft → sent → pending → partial → paid → void), USD-denominated with BTC computed at current rates, rate snapshots per payment.
- Client management: minimal. Contact database (name/email/notes), invoice association, soft-delete. No client-level aggregation, no client portal, no balance tracking across invoices. Support dashboard shows recent invoices per client but issuers have no equivalent view.
- Lightning: not implemented. On-chain only for RC and MVP. Explicitly deferred.
- Active development. RC targeting mid-2026, first public release mid-to-late 2027.
- Payment detection: automatic on-chain watching via Mempool.space API, confirmation-gated, unconfirmed tracking, idempotent.
- Reporting: dashboard only (outstanding totals, open counts, past due, recent payments, action items). No CSV/JSON export, no time-series analytics, no per-client reports, no tax reporting. These are backlog items.
- Email: manual invoice send, payment acknowledgments, receipts (owner-reviewed before sending), past-due alerts, overpayment/underpayment alerts, delivery logging with Mailgun webhooks.
- Notable: payment attribution hardening (key-aware lineage, collision detection, explicit correction UI), truthful payment communication (no auto-receipts until owner reviews), audit trails on all corrections.
- Self-host verification pending (MS21 exit criterion).

### Infrastructure / Not Merchant-Facing

**Spark (Lightspark)** — spark.money
- Noncustodial. Open protocol, users hold keys.
- Bitcoin L2 protocol, not a merchant service.
- Zero fees within Spark network. Only on-chain fees for deposits/withdrawals.
- No invoice/merchant management.
- Interoperates with Lightning. Separate protocol.
- Beta since April 2025. Well-funded (David Marcus, ex-PayPal).
- Notable: infrastructure layer, not a direct competitor. May underpin future merchant tools.

## Comparison Summary

| Tool | Custody | Lightning | Fee | Invoicing | Client Mgmt | Reporting | Self-host |
|------|---------|-----------|-----|-----------|-------------|-----------|-----------|
| BTCPay Server | Non | Yes | 0% | Full | No | Yes (export, reporting) | Yes (+ hosted) |
| **CryptoZing** | **Non** | **No (on-chain only)** | **0% (TBD)** | **Full** | **Minimal** | **Dashboard only** | **Yes (+ hosted)** |
| Blockonomics | Non | Unconfirmed | 1% | Yes | No | Unknown | No |
| Swiss Bitcoin Pay | Non | Yes | 0-1% | Basic | No | Yes (auto tax reports) | No |
| Zaprite | Non | Yes | $25/mo flat | Full platform | Yes | Yes (income tracking) | No |
| Coinsnap | Non | Yes | 1% | Basic (plugins) | No | Unknown | No |
| Flash | Non | Yes | 0% | Basic | No | Unknown | No |
| Breez | Non | Yes (only) | 0.4-0.75% setup | POS only | No | Export only | No |
| Coinbase Commerce | Non | Unconfirmed | 1% | Basic | No | Unknown | No |
| OpenNode | Custodial | Yes | 1% | Yes | No | Yes | No |
| Strike | Custodial | Yes | 0.3-1% | Limited | No | Unknown | No |
| CoinGate | Custodial | Yes | 1% | Yes | No | Yes (export) | No |
| BitPay | Custodial | Yes | 2%+$0.25 | Full | No | Yes (full) | No |
| Speed | Custodial | Yes | 1% | Yes | No | Unknown | No |
| NOWPayments | Both | Unconfirmed | 0.5-1% | Yes | No | Unknown | No |

## Open Questions

- [ ] Blockonomics Lightning support — need to verify directly
- [ ] Coinbase Commerce Lightning support — need to verify
- [ ] NOWPayments Lightning support — need to verify
- [ ] Exact Strike merchant API pricing
- [ ] Are there other noncustodial tools we're missing?
- [ ] How does CZ's feature set compare to Zaprite specifically? (client management, fiat+BTC invoicing, etc.)

## Observations for the Article

1. **Zaprite is the closest competitor to CZ**, not BTCPay. Both are noncustodial, invoice-centric, and aimed at business billing. BTCPay is more of a full payment infrastructure platform.

2. **The noncustodial field is smaller than it looks.** Many "Bitcoin payment" tools are custodial. The real alternatives in CZ's space: BTCPay, Blockonomics, Zaprite, Coinsnap, Swiss Bitcoin Pay.

3. **Lightning support is not universal** even among active tools. This is a real differentiator.

4. **Fee structures vary widely.** 0% (BTCPay, Flash) to 2%+$0.25 (BitPay). Most cluster around 1%.

5. **Self-hosting is not the dividing line.** BTCPay supports hosted setups. The real axis is custody model + feature depth.

6. **CZ is self-hostable** — this is a differentiator shared only with BTCPay among the tools researched.

## Claims We Cannot Verify

- Namecheap's $73M+ BTC revenue via BTCPay (from BTCPay case study, not independently confirmed)
- BitPay's $1.38B processed in 2025 (BitPay's own claim)
- Flash's 0% fee sustainability (early-stage, business model unclear)
- Exact user counts for most tools
