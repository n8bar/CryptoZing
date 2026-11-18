# Research: On-chain vs Lightning for Merchants

_Created: 2026-11-17_

## Scope

Fact base for the educational article "On-chain vs Lightning: what merchants need to know". The question: for a freelancer or small business that invoices clients in bitcoin, what are the real pros and cons of receiving over Lightning, compared with realistic expectations of simply accepting on-chain bitcoin, as of November 2026. This is research, not the article.

**Source tags.** Each bullet ends with a tag in square brackets. The URL for that tag is in the **Sources** list under the same section.

**Verification flags.** Keep these when drafting. Do not upgrade them.

- No flag: the page was opened and read on 2026-11-17. Quotes came through page summaries, so re-check any verbatim quote against the live page before publishing.
- **Snippet only / search summary:** seen only in a search-result excerpt. The page itself was not opened.
- **Title only:** only the page title was seen.
- **Secondary:** trade press, vendor blog or comparison site, not the original source.
- **Vendor:** the publisher sells a competing product. spark.money sells a Lightning alternative. second.tech sells Ark.
- **Computed:** arithmetic on a raw public data series pulled 2026-11-17. The number is not printed on any page. A writer can re-derive it from the linked API.
- **Inference:** reasoning from cited facts. No source states it.
- **>12 mo:** the fact is dated before November 2025. Each one says whether it was re-confirmed.
- **Unverified / not confirmed / conflict:** do not print as fact. All are collected again near the end.

**BTC price used (the figures do not all use the same price).**

- On-chain fee arithmetic uses **$65,000**. mempool.space showed $64,470 on 2026-11-17. Blockchain.com showed $65,300 for 2026-11-16. [mempool price], [Blockchain.com price]
- Lightning channel and invoice-size conversions use **about $64,450** (mempool.space, live 2026-11-17). [mempool price]
- A secondary source gave **$78,269 on 2026-11-04**. The two prices were not reconciled. [CoinPaprika]
- Payer-cap examples (0.05 BTC) use **$80,000 and $100,000** as illustrative prices only.
- Historical spike examples use that day's price (about $42,354 in December 2023, $63,823 on 2024-04-20).
- Blockchain.com's monthly average price differs again: $82,326 for October 2026 and $74,009 for 1 to 16 November 2026 (computed). [Blockchain.com price]
- Re-check the price before printing any dollar figure.

**Sources:**
- mempool price: https://mempool.space/api/v1/prices
- Blockchain.com price: https://api.blockchain.info/charts/market-price?timespan=3years&format=json&sampled=false
- CoinPaprika: https://coinpaprika.com/education/bitcoin-lightning-network/

## Already covered in earlier articles

Link to these instead of repeating them.

1. "What Is a Bitcoin Invoice?" explains that a Lightning invoice is a one-time code with an amount and expiry and that Lightning settles in seconds.
2. "BTCPay Server Alternatives" covers running a Lightning node as ongoing work (channels, inbound liquidity, routing) and has a short Lightning vs on-chain-only section.
3. "Bitcoin Pending vs Confirmed Payments" covers confirmations, the roughly 10-minute block average and the 1/3/6 confirmation thresholds.
4. "How to Receive Bitcoin" says Lightning deserves its own article, which this one fulfils.

## Bottom line for the writer

- **On-chain fits remote invoices well.** Its costs are a wait of about 10 to 60 minutes and a flat fee the payer pays. A simple payment cost about $0.09 at the 2026 norm of 1 sat/vB (computed, $65,000 per BTC). For an invoice due in days or weeks, neither is a practical limit (inference).
- **Lightning's clear wins are at the counter and for small amounts.** The strongest evidence is point of sale (Square is Lightning only, capped at $600 per payment) and small payments. None of the evidence found is about remote invoices of hundreds to thousands of dollars (inference).
- **Receiving over Lightning means picking one of three routes.** Own node (most work, least trust), an LSP-managed self-custodial wallet (app install, one provider, about 1% plus mining fees when liquidity runs short), or a custodian (fastest, gives up the keys, about 0 to 1%).
- **Locked capital is real, but for a merchant who mostly receives it is mainly the provider's capital.** The merchant pays for it through a liquidity fee. Inbound room is used up as payments arrive and leases expire, so it is a recurring cost (inference from cited fees and lease terms).
- **Lightning has on-chain costs of its own.** Each channel needs an open and a close. A forced close costs 2 to 6 times a cooperative one and locks the closer's balance for days (single-author preprint, 12 Nov 2026, not peer reviewed). At today's fee rates the amounts are small. The risk is a close during a fee spike.
- **Payer reach favours on-chain.** Robinhood cannot send Lightning. Ledger, Trezor and several multi-coin wallets are on-chain only per secondary sources. Published Lightning send caps (Cash App $999 per 7 days, snippet only) sit in the range of a typical freelance invoice.
- **Usage matches that.** At CoinGate, Lightning was 9.6% of bitcoin orders in the first half of 2026, down from a 15.41% peak in 2024. That is one processor, by order count. Network-wide Lightning dollar volume grew over the same period (River estimates, via press).
- **Be fair in both directions.** On-chain calm in 2025 and 2026 does not rule out another spike (the worst day on record averaged $128.45 per transaction, 2024-04-20). No measured 2023 to 2026 data shows Lightning success by payment size. No operator in the notes reported theft, and a measured study shows Lightning held up in fee spikes.

## Three ways to receive over Lightning

**At a glance.**

| Route | Setup | Published cost to receive | What is given up |
|---|---|---|---|
| Own node | Node, channels, liquidity, backups | No processor fee; channel and liquidity costs | Time, capital, backup risk |
| LSP-managed self-custodial wallet | App install, 12-word seed | 0 with enough liquidity; about 1% + mining fees without | Dependence on one provider; short trust windows |
| Custodial | Account, often identity checks | 0% to 1% | Keys; limits; account can be suspended |

**Setup time.** No primary source gives a measured setup time for any route. Any figure in the article would be an estimate.

**Ordering of effort (inference, matches BTCPay's own ordering).** Least to most: custodial account, LSP wallet, hosted or packaged node with an LSP channel, fully self-managed node.

**Writer caution.** Every "free to receive" figure below comes with conditions (liquidity, channel opens, withdrawal or conversion fees, or a promotional end date). Do not write "no fees" for any product.

**"Lightning-compatible" now covers three different things (inference).** Real Lightning channels (own node, Phoenix, ZEUS), swap-based wallets on another system (Aqua on Liquid, Spark and Ark wallets), and custodial ledgers. They look the same to the payer and differ in trust for the merchant.

### Own node

- **Options.** BTCPay Server supports internal nodes (Core Lightning, LND, Eclair), external nodes, Nostr Wallet Connect, custodial services and a Boltz swap plugin. [BTCPay LN docs]
- **BTCPay's four paths, "from least to most self-sovereign".** Custodial service, Boltz swapping to Liquid, paying an LSP to open a channel to your node, fully self-run node. [BTCPay LN docs]
- **Work involved.** "you need to manage payment channels", "you need to manage liquidity", and this "comes with a bit of a learning curve". LSP and self-run setups do not work on shared BTCPay instances. [BTCPay LN docs]
- **A new self-funded channel cannot receive.** "To receive payments on the Lightning Network, you need inbound capacity." [Lightning Labs inbound guide]
- **Anecdote (forum, search summary).** A new Umbrel node got a "node can't receive sats" error because it had no inbound liquidity. [Umbrel forum]
- **Alby Hub is a self-custodial node product, not a custodial service.** "Open source, self-custodial and always free to run", with "Integrated LSP services". Free tier $0. Pro $3.9/month. "0% added transaction fees". Price conflict: see the conflicts section. [Alby pricing]
- **LSP path cost.** "a small service fee, plus the cost of opening the channel, and ongoing capital costs"; rebalancing and high-fee periods add expense. [BTCPay LN docs]
- **Voltage publishes no prices.** Pricing is "shaped around your volume, your rails, and how you deploy, rather than a fixed rate card". The page markets managed nodes with a 99.9% uptime SLA. Inference: it reads as an enterprise product, so older articles describing cheap self-serve Voltage nodes may be stale. [Voltage pricing]
- **What is given up (inference).** No custody is given up. Capital sits in channels and the operator bears the loss risk if backups fail.
- **Not sourced.** Node hardware cost, initial sync time and Umbrel or Start9 setup steps.

### LSP-managed self-custodial wallet

- **Phoenix setup and restore.** "reinstall Phoenix, choose 'Restore my wallet', enter the 12-words seed". ACINQ manages channels as the counterparty. [Phoenix FAQ]
- **Phoenix fee table (seen 2026-11-17).** "Receiving via Lightning | 0 (no fees)". "Receiving via Lightning with insufficient liquidity | 1% + mining fees". "Requesting liquidity | 1% + mining fees". "Channel creation | 1000 sat". "Receiving on-chain | mining fees". "Sending via Lightning | 0.4 % + 4 sat". [Phoenix FAQ]
- **Phoenix fee cap.** It rejects an incoming payment by default "if the fee is more than 5000 satoshi (or 50% of the amount)". The cap can be changed. [Phoenix FAQ]
- **Phoenix first-payment example.** Default liquidity request is 2m sat. "The cost of liquidity is 1% + mining fees", paid upfront. A first payment of 50k sat opens a 2m sat channel and leaves 20k sat after 30k sat in fees. Small payments that cannot cover a channel go to a non-refundable "fee credit". [Phoenix liquidity doc]
- **Phoenix liquidity lasts a year.** "After one year, the channel will simply revert to default behavior" and ACINQ may claim unused liquidity back. [Phoenix FAQ]
- **ZEUS.** It has its own LSP built into the mobile wallet. Just-in-time channel up to 1m sats costs 10,000 sats. 1m to 5m sats costs 1%. A wrapped invoice with no channel open costs 2 sats. Fees vary in high on-chain-fee periods. Advance-purchase prices are quoted, not published. [ZEUS LSP intro], [ZEUS LSP fees]
- **Spark-based wallets (Breez SDK Spark, Wallet of Satoshi self-custody).** Lightning to Spark is "Free for the receiver. 0.15% for the sender (charged on routing nodes via route hints)". Spark to Lightning is "0.25% + routing fee". Withdrawal on-chain is "250 × sats_per_vbyte + 750". Inference: "free to receive" is true for the merchant but not for the transaction. [Spark fee docs]
- **Aqua (search summary; direct page returned 403).** Receives Lightning by swapping through Boltz into L-BTC on Liquid. "Receiving Lightning in AQUA: 0.25% + L-BTC Network Fees". See the Boltz outage under "What payers can actually use". [JAN3 support]
- **Trust, Phoenix.** "Phoenix is trust-minimized, but not trustless." A channel created by an incoming Lightning payment "requires trust until the funding tx is confirmed". A force close returns funds after "720 blocks delay (~ 5 days)". It relies on Google Firebase Cloud Messaging for wake-ups. [Phoenix FAQ]
- **Trust, Liquid.** "Liquid requires trust in a federation of companies, so it is not fully self-custodial." [BTCPay LN docs]
- **Trust, Spark (label disputed).** Wallet of Satoshi says of self-custody mode "you maintain exclusive control over your private keys and funds". Spark's integrated services carry "a limitation of liability capped at $100 in most cases". Matt Corallo argued Spark-based wallets do not meet the usual meaning of self-custody (search summary). WalletScrutiny has an open item titled "unilateral exit claimed but not yet verified in practice" (title only). [WoS disclosure], [Bitcoin Magazine WoS], [WalletScrutiny]
- **Privacy (inference, no page states it).** The LSP sees every payment's amount and timing because it is the only channel peer.
- **Ark wallets (search summary).** Arkade opened publicly in October 2025. Second launched Bark on mainnet on 2026-06-09, with BOLT11 invoices payable from any Lightning wallet. Receive fees and liquidity limits were not found. [Bitfinex blog], [Second blog], [Bitcoin Magazine Bark]

### Custodial service

- **Setup.** BTCPay calls this the quickest path, "with no node or liquidity management". [BTCPay LN docs]
- **Custody risk.** "funds are at risk if the service goes bankrupt or does a rug-pull". "When using any custodial service, you should keep your balance on the service low". BTCPay also lists "some form of KYC needed" and "often limits on per payment amount". [BTCPay LN docs]
- **Square (US sellers).** Bitcoin Payments went live 2025-11-10 (announcement dated 2025-10-08, >12 mo). "Bitcoin payments have a 0% processing fee until December 31, 2026, after which it will be 1%." Cap: $600 per transaction, $20,000 per day. Not available in New York. [Square press release], [Square help]
- **OpenNode (page shows 2026-08-11).** "Typical processing fees for payments and payouts are 1%." No setup, minimum, monthly or annual fees. Lightning withdrawals free. Its help centre lists an "Account Verification (KYB/KYC)" article (title only, search summary). [OpenNode fees], [OpenNode help index]
- **Speed.** 1% when the payer uses an external wallet, 0% when the payer uses Speed Wallet, 0.5% offramp to USD. On-ramp and off-ramp include a KYB/AML check. [Speed pricing]
- **Wallet of Satoshi (disclosure updated 2026-08-25).** Custodial mode "imposes no fees on incoming Lightning payments". Balance capped at 5 BTC. "We reserve the right to suspend or terminate your access to the services at any time". Custodial mode is offered only in select regions. On-chain sends cost 1.95% plus a fixed or miner fee. [WoS disclosure]
- **Blink.** Receiving Lightning, "there is no fee charged to you by Blink". Its custody model was not confirmed on an opened page. [Blink FAQ]
- **Strike.** Trading fees run 0.89% (under $5,000/month) down to 0.25% ($15m+). Target spread 0.21%. Standard bank withdrawal free. Its fee for receiving Lightning was not on the pages reached. [Strike bitcoin fees], [Strike cash fees]
- **Common rate (inference).** 1% is the common published processor rate in late 2026 (OpenNode, Speed, Square from 2027-01-01).
- **Cash App receive cap.** "You can send, or receive payment of up to $999 in bitcoin every 7 days using the Lightning Network." Not available in New York. (Snippet only; the page returned empty when opened.) [Cash App help]
- **Not found.** A dated, sourced example of a Lightning custodian freezing a merchant account. KYC terms, withdrawal limits and freeze terms at Strike, Cash App, Coinbase or OpenNode.

### Provider availability: US exits and returns

- **Phoenix left.** ACINQ announced in April 2024 that Phoenix would leave US app stores on 2024-05-03, citing doubt over whether wallet providers, LSPs or Lightning nodes could be treated as Money Services Businesses. (>12 mo; superseded by the return.) [No BS Bitcoin Phoenix exit]
- **Phoenix reported back.** An article dated 2025-04-10 embeds an ACINQ post of 2025-04-08 welcoming the "Ending Regulation By Prosecution" memo. (>12 mo; no later reversal found.) A second report says Peter Todd confirmed the return (search summary, secondary). ACINQ's own announcement was not opened and the US app-store listing was not checked. Say "reported". [Atlas21 Phoenix return], [Traders Union]
- **Wallet of Satoshi left.** It ended support for US customers and left US app stores. Date conflict: see the conflicts section. [CryptoSlate WoS], [DailyCoin]
- **Wallet of Satoshi came back as self-custody on Spark.** Beta began rolling out 2025-07-01 (>12 mo). The 2026-08-25 disclosure says self-custody is "available globally" and does not name the US. [Spark WoS], [Lightspark WoS], [WoS disclosure]
- **Inference.** For a US merchant in November 2026, Wallet of Satoshi most likely means the Spark self-custody mode.

**Sources:**
- BTCPay LN docs: https://docs.btcpayserver.org/LightningNetwork/
- Lightning Labs inbound guide: https://docs.lightning.engineering/the-lightning-network/liquidity/how-to-get-inbound-capacity-on-the-lightning-network
- Umbrel forum: https://community.umbrel.com/t/closing-channels-online-offline/11068/3
- Alby pricing: https://getalby.com/pricing
- Voltage pricing: https://www.voltage.cloud/pricing
- Phoenix FAQ: https://phoenix.acinq.co/content/faq.md
- Phoenix liquidity doc: https://phoenix.acinq.co/content/server/liquidity.md
- ZEUS LSP intro: https://docs.zeusln.app/lsp/intro
- ZEUS LSP fees: https://docs.zeusln.app/lsp/fees
- Spark fee docs: https://docs.spark.money/wallets/estimate-fees.md
- JAN3 support: https://jan3.zendesk.com/hc/en-us/articles/34940028331163-What-Are-the-Fees-on-AQUA
- WoS disclosure: https://walletofsatoshi.com/disclosure
- Bitcoin Magazine WoS: https://bitcoinmagazine.com/news/wallet-of-satoshi-partners-with-spark-to-offer-self-custodial-bitcoin-lightning-experience
- WalletScrutiny: https://gitlab.com/walletscrutiny/walletScrutinyCom/-/work_items/947
- Bitfinex blog: https://blog.bitfinex.com/industry-news/building-financial-infrastructure-on-bitcoin-ark/
- Second blog: https://blog.second.tech/bark-now-on-bitcoin-mainnet/
- Bitcoin Magazine Bark: https://bitcoinmagazine.com/news/second-launches-bark-on-bitcoin-mainnet
- Square press release: https://squareup.com/us/en/press/square-bitcoin
- Square help: https://squareup.com/help/us/en/article/8622-accept-and-manage-bitcoin-payments
- OpenNode fees: http://help.opennode.com/en/articles/3176293-pricing-and-fees
- OpenNode help index: https://help.opennode.com/llms.txt
- Speed pricing: https://www.tryspeed.com/pricing
- Blink FAQ: https://faq.blink.sv/transaction-fees/what-are-the-fees-for-receiving-bitcoin-in-my-blink-wallet
- Strike bitcoin fees: https://strike.me/faq/what-fees-and-rates-apply-to-bitcoin-transactions/
- Strike cash fees: https://strike.me/faq/what-fees-and-rates-apply-to-cash-transactions/
- Cash App help: https://cash.app/help/6506-lightning
- No BS Bitcoin Phoenix exit: https://nobsbitcoin.com/phoenix-wallet-to-be-removed-from-us-app-stores-on-may-3
- Atlas21 Phoenix return: https://atlas21.com/investigations-into-exchanges-and-wallets-dropped-phoenix-wallet-returns-to-the-u-s/
- Traders Union: https://tradersunion.com/news/market-voices/show/201069-phoenix-wallet-returns
- CryptoSlate WoS: https://cryptoslate.com/lightning-network-app-wallet-of-satoshi-ends-support-for-u-s-customers/
- DailyCoin: https://dailycoin.com/bitcoin-users-decry-wallet-of-satoshis-withdrawal-from-us/
- Spark WoS: https://www.spark.money/news/wallet-of-satoshi-spark-partnership
- Lightspark WoS: https://www.lightspark.com/news/spark/wallet-of-satoshi-is-now-live-on-spark-bringing-a-

## Channels, locked capital and inbound liquidity

**What a channel is, in plain words.** "Opening a channel is not spending funds, it's more like putting the funds on a pre-paid card." [BTCPay LN FAQ]

**Locked capital.**

- **Who funds.** In the original open, the node that opens contributes all the funds. In the newer dual-funded open, both sides can add inputs. [BOLT 2]
- **Reserve.** Each side keeps a reserve "so it always has something to lose". "1% of the channel total is suggested." [BOLT 2]
- **Some LSPs waive the reserve.** Megalith's live endpoint returned `"supports_zero_channel_reserve": true` on 2026-11-17. [Megalith LSPS1]
- **More than the reserve can be unusable.** Phoenix says some funds are locked to cover on-chain fees for a unilateral close, and the amount "can be significant". It does not publish the exact amount. [Phoenix FAQ]
- **Lightning Labs says the same.** A channel "may appear slightly smaller than the overall channel capacity". A new LND channel typically needs three confirmations before it is active. [Lightning Labs inbound guide]
- **Getting coins out without closing, option A: splicing.** A splice-out "removes funds from a channel to an independent onchain output" and the channel stays usable. The initiator pays for the splice transaction. [Optech splicing], [BOLT 2]
- **Splicing support (Optech dated entries).** Phoenix v2.2.0 (2024-03-20). LDK splice-out (2025-08-29). Core Lightning `splicein`/`spliceout` (2026-03-27). Eclair official protocol (2026-05-08). LND is not listed as shipping splicing. [Optech splicing]
- **Option B: swap out.** Loop Out pays out of the channel over Lightning and returns the balance on-chain "minus fees". The channel stays open and the swapped amount becomes inbound capacity. [Lightning Labs inbound guide]
- **Inference.** "Locked" is accurate in two senses. The money needs an on-chain transaction (close, splice or swap) to reach a normal wallet. A slice of it (reserve plus fee buffer) cannot be spent over Lightning while the channel is open.
- **Inference.** For a merchant who mostly receives, the capital locked is mainly the partner's or LSP's. The merchant pays for it through a liquidity fee.
- **Inference.** An LND-based merchant cannot splice today per the Optech record, so getting coins out without closing means a swap.

**Inbound liquidity.**

- **The rule.** If Alice opens a channel with her whole balance, her inbound capacity is zero because "receiving just a single satoshi would push her balance over the total capacity of the channel." [Lightning Labs inbound guide]
- **BTCPay's version.** "Merchants need incoming channels. Other people opening a channel with them provides liquidity to the merchant." [BTCPay LN FAQ]
- **LSP trade-off.** You "pay the LSP so they open a LN channel to your node". Upside: "you can receive payments without reserving your own funds". Downsides: "you need to buy liquidity (and pay for their capital costs)" and "expensive initial setup during high-fee environments". Received funds fill the channel, which then "needs rebalancing (swapping LN to on-chain BTC) to reuse channel capacity." [BTCPay LN docs]
- **Spend first.** "Any lightning payment out of our channel will translate to the equivalent in inbound capacity." [Lightning Labs inbound guide]
- **Loop Out.** Swap out "about 80-90%" of the balance, open a second channel with the returned coins, and "about 40-45% of our total channel capacity is now available for receiving." [Lightning Labs inbound guide]
- **Loop Out price example (undated doc example, 1,000,000-sat swap).** Server fee 3,260 sat, on-chain 6,802 sat, routing 2,524 sat, about 12,600 sat total (about 1.26%), plus a refundable prepay of up to 30,000 sat. Minimum is "typically around 250,000 satoshis". [Loop CLI docs]
- **Marketplaces.** Lightning Pool lets bidders pay nodes to open channels for a set period. No prices given. Liquidity ads are "An experimental feature". [Lightning Labs inbound guide], [Optech liquidity ads]
- **Amboss Magma (Sept 2025, >12 mo, not re-confirmed, vendor author).** Median and mean lease price "around 2.6%" APR, small channels "often exceeding 4%". Amboss charges "a flat 500 PPM" per order. The article is unclear on the small-channel size cut-off. [Second on Magma]
- **Megalith live terms (2026-11-17).** Inbound channel size 150,000 to 30,000,000 sats (about $97 to $19,300 at $64,450). Maximum lease 13,140 blocks (about three months). Price is quoted per order. [Megalith LSPS1]
- **Phoenix and ZEUS live prices.** Phoenix "Requesting liquidity: 1% + mining fees", reserved for a year. ZEUS 10,000 sats up to 1M, 1% from 1M to 5M. [Phoenix FAQ], [ZEUS LSP fees]
- **Independent price survey (2025-02-19, >12 mo; the author said on 2026-02-03 it was "a bit outdated... but not too much").** Opening fee for a 1,000,000-sat private inbound channel, one-year term: LNBig 3,600; Breez 4,500; LQWD 8,400; Phoenix 11,263; Coincept 11,300; Blocktank 12,452; Rizful 19,878; Megalithic 20,282; FlashSats 20,750; Bitrefill Thor 25,000; Olympus 52,250 sats. An LSP replied the spread is "dependent on the prevailing chain fees." [Stacker News LSP survey]
- **Inference.** In that survey, 1M sats of inbound (about $645 of receiving room at $64,450) cost roughly 0.4% to 5% of the channel size up front. The live 1% figures sit inside that range.
- **Inference.** Inbound capacity is used up as the merchant is paid. A receive-only merchant must keep re-buying capacity or keep draining the channel, and each costs an on-chain fee plus a service fee.
- **Inference.** Leases expire (about three months at Megalith, one year in the survey), so inbound capacity is a recurring cost, not a one-time setup.

**Sources:**
- BTCPay LN FAQ: https://docs.btcpayserver.org/FAQ/LightningNetwork/
- BTCPay LN docs: https://docs.btcpayserver.org/LightningNetwork/
- BOLT 2: https://github.com/lightning/bolts/blob/master/02-peer-protocol.md
- Megalith LSPS1: https://megalithic.me/api/lsps1/v1/get_info
- Phoenix FAQ: https://phoenix.acinq.co/content/faq.md
- Lightning Labs inbound guide: https://docs.lightning.engineering/the-lightning-network/liquidity/how-to-get-inbound-capacity-on-the-lightning-network
- Optech splicing: https://bitcoinops.org/en/topics/splicing/
- Loop CLI docs: https://docs.lightning.engineering/lightning-network-tools/loop/the-loop-cli
- Optech liquidity ads: https://bitcoinops.org/en/topics/liquidity-advertisements/
- Second on Magma: https://second.tech/blog/diving-deeper-into-lightning-liquidity-amboss-magma-2/
- ZEUS LSP fees: https://docs.zeusln.app/lsp/fees/
- Stacker News LSP survey: https://stacker.news/items/890432

## Hidden on-chain costs of Lightning (open, close, force close)

- **Every channel needs at least two on-chain transactions over its life.** One to open, one to close. The initiator pays the miner fee to open. [Lightning Labs inbound guide]
- **Fee rates today (live, 2026-11-17).** Fastest 3 sat/vB, half-hour 1, economy 1. [mempool fees]
- **Channel open at these rates.** About 153 to 305 sats (secondary source's calculation, Oct to Nov 2026). [CoinPaprika]
- **Cooperative close.** Legacy rule: "The funder chooses a fee it thinks is fair". Newer `option_simple_close`: "Each peer creates their own closing transaction where they pay the fee." [BOLT 2]
- **Force close is bigger.** The commitment transaction is 724 weight units plus 172 per pending payment (1,124 plus 172 with anchors). Each pending payment needs a further transaction, then each side sweeps its output. [BOLT 3]
- **Force close delays the closer's money.** `to_self_delay` is "how long it will have to wait in case of breakdown before redeeming its own funds." [BOLT 2]
- **Delay, LND default.** Scaled "linear to the channel size between 144 and 2016" blocks (about one day to two weeks). [LND sample config]
- **Delay, Phoenix.** After "720 blocks (about 5 days)", though it "can be more"; "high fees apply." [Phoenix FAQ]
- **Measured (preprint dated 2026-11-12, single author, not peer reviewed).** Across 973,581 public channel closes from Jan 2018 to Oct 2026, "a force close costs 2 to 6 times a cooperative close in fees and locks the closer's balance for days." [Kumar preprint]
- **Same study, as reported by press (not verified against the PDF).** 2026 median force-close cost 925 sats per channel across both parties versus 197 sats cooperative (4.7 times). The closer "waited a median 2.5 days" in 2026. 523,815 of the 973,581 closes were force closes. [CryptoSlate force close]
- **Same study on fee spikes (fair to Lightning).** "Fee spikes did not produce a wave of force closes". Since 2021 forwarding nodes lost only 38 payments to late timeouts. But "In 2023, 18.2% of HTLC timeouts confirmed more than 40 blocks after expiry." [Kumar preprint]
- **Fee-spike force closes do happen (2024-08-22, >12 mo).** Alby said fees were "spiking by more than 500x" and "a few users saw channel force closures." Affected users had to pay to open a new channel. [Stacker News Alby thread]
- **High-fee-era anecdote (about 2022, >12 mo; fee level no longer current).** One large operator put a single force close at about 130,000 sats and named on-chain fees his biggest cost. [LN+ ZeroFeeRouting]
- **Why anchors exist.** A commitment fee set in advance "may be too high or too low by then". Anchor outputs let either side raise the fee at broadcast time. Core Lightning made them default (2024-02-14). Eclair stopped accepting new non-anchor channels (2024-12-06). [Optech anchors]
- **Anchors need spare on-chain coins.** Core Lightning keeps a reserve that "defaults to 25000sat". LND "will require 1 UTXO to be reserved for this channel type." [CLN config], [LND sample config]
- **Nodes force close on their own.** The spec requires a node to "fail the channel" if a pending payment passes its timeout unresolved. [BOLT 2]
- **Small balances can be stranded.** Phoenix refuses swaps under 546 sats. [Phoenix FAQ]
- **Inference (arithmetic).** At 1 to 3 sat/vB an anchor commitment with no pending payments (about 281 vB) costs roughly 280 to 850 sats before sweeps. At 100 sat/vB it is about 28,000 sats. Fee level, not Lightning itself, drives the cost.
- **Inference.** Lifetime on-chain cost of one channel in November 2026 conditions is a few hundred to about a thousand sats (well under $1 at $64,450). In a spike it is tens to hundreds of times more, and a force close is the one event whose timing the merchant does not control.
- **Inference.** The delay matters more than the fee for cash flow. After a force close an LND merchant could wait up to two weeks for their own balance.
- **Inference.** About half of historical public closes were forced, per the press summary, so a force close is a normal event to plan for.

**Sources:**
- Lightning Labs inbound guide: https://docs.lightning.engineering/the-lightning-network/liquidity/how-to-get-inbound-capacity-on-the-lightning-network
- mempool fees: https://mempool.space/api/v1/fees/recommended
- CoinPaprika: https://coinpaprika.com/education/bitcoin-lightning-network/
- BOLT 2: https://github.com/lightning/bolts/blob/master/02-peer-protocol.md
- BOLT 3: https://github.com/lightning/bolts/blob/master/03-transactions.md
- LND sample config: https://github.com/lightningnetwork/lnd/blob/master/sample-lnd.conf
- Phoenix FAQ: https://phoenix.acinq.co/content/faq.md
- Kumar preprint: https://arxiv.org/abs/2611.16311
- CryptoSlate force close: https://cryptoslate.com/a-forced-bitcoin-lightning-exit-can-be-cheap-and-still-leave-you-waiting-days/
- Stacker News Alby thread: https://stacker.news/items/658124
- LN+ ZeroFeeRouting: https://lightningnetwork.plus/posts/335
- Optech anchors: https://bitcoinops.org/en/topics/anchor-outputs/
- CLN config: https://docs.corelightning.org/reference/lightningd-config

## Staying online and backups

**Staying online.**

- **Why Lightning differs.** On-chain, "the spender can pay that address at any time—even when the receiver is offline". On Lightning, "the receiver needs to release a secret at the time a payment is received", which needs sender and receiver to "both be online at the same time." [Optech async]
- **Phoenix.** "Phoenix will work fine as long as the app is active, but if it's in the background or closed, payments might fail." With pending payments the phone should stay online for days, "otherwise, channels may be force-closed." [Phoenix FAQ]
- **BTCPay.** It will not create a Lightning invoice unless "the lightning node is fully synced." [BTCPay LN FAQ]
- **Second most common failure cause (Aug 2023, >12 mo).** No route can be found, "which is often the case when the receiver is offline and has a non-custodial wallet." [River 2023 report]
- **Workaround, ZEUS Zaplocker.** Offline users can receive at a static address. "Users just need to log in to their ZEUS wallets within 24 hours to claim the payments", "otherwise they will be returned to the sender." [ZEUS Zaplocker]
- **Workaround, Spark.** "Lightning payments work in Spark even if the receiver is offline." [Breez SDK Spark]
- **Protocol-level async payments.** Code has merged in Eclair (2024-09-06) and LDK (2024-09-20, 2025-07-04, 2025-07-18). The page lists code merges, not wallet releases. Use in a shipping mobile wallet was not confirmed. [Optech async]
- **Own node left offline (forum reports, search summary).** Peers may force-close channels, and recovering funds can take from the next block to about two weeks. [Umbrel forum]
- **Inference.** A custodian receives while the merchant's phone is off, because the custodian's node is the receiver. An own node on always-on hardware receives unattended; the risk is downtime.
- **Inference.** Lightning keys are hot by design. A watch-only invoicing tool cannot receive Lightning payments without something online holding signing keys, whether the merchant's node or a provider's.

**Watching the chain.**

- **How cheating works.** A partner can try "publishing previous, expired and revoked commitment transactions that award them a higher balance." The honest side can take the cheater's funds, but only within the timelock: "a CSV delay of 144 blocks allows for about 24 hours". [Lightning Labs watchtowers]
- **Watchtowers.** Third parties that broadcast the penalty transaction, letting clients "go offline for significant amounts of time." "Watchtowers are not entrusted with the funds they monitor." [Optech watchtowers]
- **LND watchtower client is off by default** (`wtclient.active=false`). Towers are "altruistic, meaning there is no compensation or other incentive." [LND sample config], [Lightning Labs watchtower setup]

**Backups.**

- **Seed alone (LND).** Recovers "all on-chain funds in your LND wallet" and funds from channels peers force close. It does not revive channels: "we are unable to revive our channels." [Lightning Labs recovery]
- **Static channel backups (LND).** Used to "contact our former peers and ask them to force close our channels." "Keep a copy of your channel.backup file on a separate machine and update it whenever a new channel is opened." [Lightning Labs recovery]
- **BTCPay warnings.** "the Lightning Network is still in an experimental phase". "Do not delete `wallet.db` and simply restart BTCPay Server" or "your channels can no longer be used". "Never run two LND instances on the same data". [BTCPay LN FAQ]
- **Phoenix.** 12-word seed only. Do not uninstall or reset the app "until funds from force-closed channels are recovered." [Phoenix FAQ]
- **Spec safeguard.** A node that learns it has fallen behind "MUST NOT broadcast its commitment transaction". [BOLT 2]
- **Inference.** For a small merchant the realistic risk is their own stale data. Restoring an old database and broadcasting from it looks the same as cheating and triggers the penalty.
- **Inference.** A hosted or LSP-backed wallet cuts the uptime and backup burden but adds a counterparty.

**Operator workload (mostly routing-node experience, self-selected forum sample).**

- **Eight-year operator (c-otto.de, May 2018 to Aug 2026).** 3,444 channels: 2,898 closed cooperatively (84.1%), 145 force closed by the peer, 401 force closed by his own node. "You'll make mistakes, suffer from bugs and high fee waves, and most likely you'll get very little in return." He gave private reasons for stopping and said "nothing major happened." [Stacker News c-otto]
- **Large operator (about 2022, >12 mo).** "managing the node will take a lot of time". A database disk failed with "no other backups at the time"; he managed to copy the data off. [LN+ ZeroFeeRouting]
- **BTCPay's caution.** "Community support for Lightning Network questions is pretty limited." [BTCPay LN FAQ]
- **Fairness note (inference).** None of these operators reported theft. The documented costs are attention, fees and delay, not routine loss of funds. Most of this is routing-node pain; a merchant with one or two LSP channels avoids rebalancing but still owns uptime, backups, liquidity top-ups and close risk.

**Sources:**
- Optech async: https://bitcoinops.org/en/topics/async-payments/
- Phoenix FAQ: https://phoenix.acinq.co/content/faq.md
- BTCPay LN FAQ: https://docs.btcpayserver.org/FAQ/LightningNetwork/
- River 2023 report: https://www.river.com/learn/files/river-lightning-report-2023.pdf
- ZEUS Zaplocker: https://docs.zeusln.app/lightning-address/zaplocker
- Breez SDK Spark: https://sdk-doc-spark.breez.technology/guide/receive_payment.html
- Umbrel forum: https://community.umbrel.com/t/closing-channels-online-offline/11068/3
- Lightning Labs watchtowers: https://docs.lightning.engineering/the-lightning-network/payment-channels/watchtowers
- Optech watchtowers: https://bitcoinops.org/en/topics/watchtowers/
- LND sample config: https://github.com/lightningnetwork/lnd/blob/master/sample-lnd.conf
- Lightning Labs watchtower setup: https://docs.lightning.engineering/lightning-network-tools/lnd/watchtower
- Lightning Labs recovery: https://docs.lightning.engineering/lightning-network-tools/lnd/recovery-planning-for-failure
- BOLT 2: https://github.com/lightning/bolts/blob/master/02-peer-protocol.md
- Stacker News c-otto: https://stacker.news/items/1551319
- LN+ ZeroFeeRouting: https://lightningnetwork.plus/posts/335

## Invoice expiry, reusable codes and Lightning addresses

- **Default expiry is one hour.** "Default is 3600 (1 hour) if not specified." After expiry the payer "SHOULD NOT attempt a payment." [BOLT 11]
- **No fiat field, no maximum amount.** The amount is in bitcoin units. A BOLT11 invoice can carry a fallback on-chain address. [BOLT 11]
- **Inference.** For net-30 style invoicing, BOLT11 is a poor fit. The invoice has to be made when the client is ready to pay, so the node or provider must be online then. A long expiry fixes the timing but locks in a bitcoin amount.
- **Inference.** An on-chain address has no expiry but has the same exchange-rate question. Lightning forces it into a short window by default.
- **BOLT12 offers.** Reusable payment codes, recurring payments, and amounts in another currency such as USD with the bitcoin amount settled at request time. [Optech offers]
- **BOLT12 status (Optech dated entries).** Spec merged (2024-10-04). Core Lightning on by default (2024-11-22). Eclair accepts BOLT12 invoices with reply paths (2026-07-31). LND added offer creation and invoice-request handling (2026-11-13). The LND entries are merged code; a tagged release was not confirmed. [Optech offers]
- **Inference.** BOLT12 alone does not remove the online requirement. Both the payer's wallet and the merchant's node must support it. No data was found on what share of wallets and exchanges can pay a BOLT12 offer.
- **Lightning address.** Format `you@domain`. The payer needs no invoice or QR code. It is "just a set of simple protocol instructions" built on LNURL. [lightningaddress.com]
- **Three ways to get one.** Use a provider, point a node at a community bridge server, or self-host. Listed providers include Strike, Wallet of Satoshi, Blink, Alby, BTCPay Server, ZBD, Coinos, Bitrefill, CoinCorner, Spark Wallet and Lexe. [lightningaddress.com]
- **ZEUS Pay address types (v0.11).** Cashu (an ecash mint holds funds), Zaplocker ("The first ever self-custodial Lightning addresses for mobile"), and Nostr Wallet Connect. ZEUS Pay and Zaplocker fees "are now at zero". [ZEUS Pay docs], [ZEUS Zaplocker]
- **Phoenix took a different route (search summary; >12 mo; not reconfirmed).** BOLT12 offers since v2.3.1 (July 2024) and BIP353 DNS-based names from v2.3.3. [No BS Bitcoin Phoenix 2.3.1], [Blocktrainer]
- **Coinbase.** Lightning works "via invoices only, not lightning addresses" (snippet only). [Coinbase Lightning help]
- **Inference.** A Lightning address needs an always-on web endpoint, so it comes built in with custodial and hosted services and needs extra setup otherwise. An address at a third-party domain adds a dependency on that domain.
- **Inference.** There are two look-alike formats: LNURL-based Lightning addresses and BIP353 names. A payer's wallet must support the one the merchant uses.

**Sources:**
- BOLT 11: https://github.com/lightning/bolts/blob/master/11-payment-encoding.md
- Optech offers: https://bitcoinops.org/en/topics/offers/
- lightningaddress.com: https://lightningaddress.com/
- ZEUS Pay docs: https://docs.zeusln.app/lightning-address/intro
- ZEUS Zaplocker: https://docs.zeusln.app/lightning-address/zaplocker
- No BS Bitcoin Phoenix 2.3.1: https://www.nobsbitcoin.com/phoenix-wallet-v2-3-1-phoenixd-v0-2-0/
- Blocktrainer: https://blocktrainer.de/en/blog/phoenix-wallet-how-do-the-new-bip-353-lightning-addresses-work
- Coinbase Lightning help: https://help.coinbase.com/en/coinbase/trading-and-funding/sending-or-receiving-cryptocurrency/lightning

## Payment size and reliability

**Receiving limits.**

- **Hard ceiling.** "your Lightning channel balance will not be able to exceed the capacity of your channel." [Lightning Labs inbound guide]
- **No protocol cap on payment size today.** The old per-payment limit was removed from the spec on 2021-06-30. A channel must be under 2^24 sats (about 0.168 BTC) unless both nodes opt in to large channels. [Optech large channels], [BOLT 2]
- **Multi-path payments.** One payment can arrive in pieces across several channels. Supported by LND, Eclair, Core Lightning and LDK since 2020. Inference: the pieces still have to fit in the inbound room that exists. [Optech multipath]
- **Just-in-time channels.** The provider opens a channel when the first payment arrives. "the LSP forwards the payment to the client, deducting the channel opening fee." "The client trusts the LSP to actually open a real channel with a valid funding transaction". [Optech JIT], [BLIP 52]
- **Zero-conf trust.** Funds in a zero-conf channel "are not secure until the channel open transaction receives a sufficient number of confirmations." [Optech zero-conf]
- **Typical public channel sizes (live record dated 2026-11-18 UTC).** Median 2,566,406 sats (about $1,650). Average 11,888,502 sats (about $7,660). Both at $64,450. [emzy LN stats]
- **Largest LSP channel on offer from one provider.** 30,000,000 sats (about $19,300 at $64,450). [Megalith LSPS1]
- **Large payments do move between big nodes.** One operator's largest single forward was 160,130,043 sats (1.6 BTC) in Nov 2025. His average forward was 124,026 sats and median 20,003 sats over 1.38 million forwards. [Stacker News c-otto]
- **Inference (at $64,450).** A $2,000 invoice is about 3.1M sats, larger than the median public channel. A $10,000 invoice is about 15.5M sats, larger than the average public channel. Both need a deliberately large channel or several channels plus multi-path.
- **Inference.** JIT and on-the-fly funding turn the "cannot receive" wall into a fee (1% at Phoenix and ZEUS) plus an on-chain fee, with trust in the LSP until the funding transaction confirms.

**Reliability.**

- **Aggregate success is high (Aug 2023, >12 mo; no newer River figure found).** "River's Lightning payment success rate was 99.7% in August 2023 across 308k transactions." [River 2023 report]
- **Main failure causes (same report).** "The most common reason why a Lightning payment fails is a timeout when searching for a route. This happens when no route can be found that has enough liquidity across the entire path." [River 2023 report]
- **The payments behind that number were small.** The average public Lightning transaction was "around 44.7k satoshis, or $11.84 in August 2023". [River 2023 report]
- **Do not present 99.7% as the odds for a large invoice (inference).** It is an aggregate over mostly small payments from one of the largest nodes.
- **No measured success-by-amount data for 2023 to 2026 was found.** This is the biggest hole in the topic.
- **Theory (2021, >12 mo; still applies).** Senders cannot see channel balances and find routes by trial and error. Probability-aware routing "increases the size of payments that can be reliably delivered by several orders of magnitude". [Pickhardt et al. 2021], [Pickhardt and Richter 2021]
- **Capacity is not the driver for a well-run node (2023, >12 mo).** River cut its own capacity "by 48% from 315 BTC to 151 BTC" and says this did not hurt success rate or volume. [River 2023 report]
- **A failed Lightning payment returns to the sender.** General protocol behaviour, not separately sourced. The cost is a retry or a fall back to on-chain.
- **Inference.** A $2,000 invoice is realistic if the merchant has that much inbound to a well-connected hub or LSP and the payer uses a well-connected wallet or exchange. A $10,000 invoice needs planning. A default small-node setup should expect it to fail or to trigger a paid on-the-fly channel.

**Routing fees (the payer pays).**

- **Formula per forwarding node.** "basefee + (amount * feerate / 1000000)." [LND sample config]
- **What nodes advertise (live record dated 2026-11-18 UTC).** Median 101 ppm plus 0.164 sat base. Average 814 ppm plus 0.883 sat base. [emzy LN stats]
- **Wallet-level payer fee.** Phoenix charges the sender "0.4% + 4 sat". [Phoenix FAQ]
- **Inference (at $64,450).** At the median policy a $2,000 payment pays about 313 sats per hop (about $0.20) and a $10,000 payment about 1,570 sats per hop (about $1). At the average policy the same payments cost about $1.60 and $8 per hop.
- **Inference: there is a crossover.** Lightning fees scale with amount; on-chain fees scale with transaction size. At 1 to 3 sat/vB, on-chain is cheaper for the payer than a single 101 ppm hop above roughly 1.4M to 4.2M sats (about $900 to $2,700 at $64,450). This flips when on-chain fees rise.
- **Not fetched.** BOLT 4 and BOLT 7. Cite them if a formal source is needed for "the sender pays routing fees".

**Sources:**
- Lightning Labs inbound guide: https://docs.lightning.engineering/the-lightning-network/liquidity/how-to-get-inbound-capacity-on-the-lightning-network
- Optech large channels: https://bitcoinops.org/en/topics/large-channels/
- BOLT 2: https://github.com/lightning/bolts/blob/master/02-peer-protocol.md
- Optech multipath: https://bitcoinops.org/en/topics/multipath-payments/
- Optech JIT: https://bitcoinops.org/en/topics/jit-channels/
- BLIP 52: https://github.com/lightning/blips/blob/master/blip-0052.md
- Optech zero-conf: https://bitcoinops.org/en/topics/zero-conf-channels/
- emzy LN stats: https://mempool.emzy.de/api/v1/lightning/statistics/latest
- Megalith LSPS1: https://megalithic.me/api/lsps1/v1/get_info
- Stacker News c-otto: https://stacker.news/items/1551319
- River 2023 report: https://www.river.com/learn/files/river-lightning-report-2023.pdf
- Pickhardt et al. 2021: https://arxiv.org/abs/2103.08576
- Pickhardt and Richter 2021: https://arxiv.org/abs/2107.05322
- LND sample config: https://github.com/lightningnetwork/lnd/blob/master/sample-lnd.conf
- Phoenix FAQ: https://phoenix.acinq.co/content/faq.md

## On-chain: fees today and in spikes

**Fees today.**

- **Live reading, 2026-11-17.** Fastest 3, half-hour 1, hour 1, economy 1, minimum 1 sat/vB. The "precise" endpoint showed fastest 2.641, half-hour 1.391, hour 0.631, economy 0.2, minimum 0.1 sat/vB. [mempool fees], [mempool precise fees]
- **Median fee rate inside blocks has been about 1 sat/vB every month from August 2025 to November 2026 (computed).** [mempool fee-rate history]
- **Average fee per transaction (computed).** About $1.19 in 2025 and about $0.40 in 2026 to 16 November. Highest day in 2025: $3.81 (2025-01-20). Highest day in 2026: $1.02 (2026-02-06). [Blockchain.com fees USD], [Blockchain.com tx count]
- **A simple payment is about 141 vB (computed from Optech's component sizes).** One native SegWit input, two outputs: 140.75 vB. Taproot: 154.25 vB. [Optech size calculator]
- **The fee depends on size in vbytes, not on the amount sent.** [Bitcoin Wiki miner fees]
- **The network average overstates a simple invoice payment (inference).** In 2026 the average transaction paid about 400 to 650 sats. A 141 vB payment at 1 sat/vB pays 141 sats (about $0.09 at $65,000).
- **Writer's phrase (inference).** "Pennies to about a quarter per payment, most days" in 2025 and 2026.
- **Why fees fell (Galaxy Research, 2025-08-20, >12 mo; 2026 data confirms the low-fee condition still holds).** "Median daily fee has dropped >80% since April 2024". It points to less Runes and Ordinals activity, bitcoin held in ETFs, and trading moving to other chains. [Galaxy Research]

**Median in-block fee rate by period (computed, whole sat/vB).** [mempool fee-rate history]

| Period | Median (sat/vB) | 90th percentile in block | Highest bucket median |
|---|---|---|---|
| Dec 2023 | 136 | 201 | 467 |
| Apr 2024 | 43 | 90.5 | 1,178 |
| Jul–Sep 2024 | 3–6 | 7–11 | 85 |
| Oct–Dec 2024 | 6–8 | 11.5–15.5 | 69 |
| Jan–May 2025 | 3 | 6 | 13 |
| Jun–Jul 2025 | 2 | 5 | 7 |
| Aug 2025–Nov 2026 | 1 | 3–4 | 8 (Sep 2025); 5 or less in 2026 |

**Average fee per transaction by year (computed).** [Blockchain.com fees USD], [Blockchain.com tx count]

| Year | Mean of daily averages (USD) | Median day (USD) | Highest day (USD) |
|---|---|---|---|
| 2017 | 4.89 | 2.33 | 55.92 |
| 2021 | 9.98 | 5.09 | 62.79 |
| 2023 | 4.38 | 2.15 | 38.43 |
| 2024 | 5.20 | 3.22 | 128.45 |
| 2025 | 1.19 | 1.17 | 3.81 |
| 2026 (to Nov 16) | 0.40 | 0.36 | 1.02 |

**Spikes on record (computed from the same series).**

| Episode | Peak day, average fee | Days at $10 or more | Days at $30 or more |
|---|---|---|---|
| Dec 2017 | 2017-12-22, $55.92 | 58 (2017-11-11 to 2018-01-26) | 17 |
| Spring 2021 | 2021-04-21, $62.79 | 137 (2021-01-05 to 2021-05-28) | 11 |
| May 2023 (BRC-20) | 2023-05-08, $30.91 | 4 | 1 |
| Nov 2023–Jan 2024 (inscriptions) | 2023-12-17, $38.43 | 51 (2023-11-09 to 2024-01-18) | 4 |
| Apr 2024 (halving, Runes) | 2024-04-20, $128.45 | 19 (2024-04-11 to 2024-06-08, includes June) | 4 |
| Jun 2024 (exchange consolidation) | 2024-06-07, $83.61 | one-day event | |

- **The 2024-04-20 record is confirmed by press**, which ties it to the Runes launch at the halving block. Fees fell sharply the next day. [The Block Runes]
- **The 2024-06-07 spike (snippet only).** Press attributed it to OKX consolidating wallet outputs with faulty fee estimation. [Cointelegraph OKX], [Hashrate Index], [Decrypt OKX]
- **No spike in 2025 or 2026 (computed).** The highest bucket-median fee rate from January 2025 to November 2026 was 13 sat/vB (May 2025). In 2026 it did not exceed 5. [mempool fee-rate history]
- **What a 141 vB payment would have cost in a spike, at that day's price (inference).** December 2023 monthly median (136 sat/vB, about $42,354): about $8.12. December 2023 worst bucket (467 sat/vB): about $27.89. Halving-day worst bucket (1,178 sat/vB, $63,823): about $106.
- **Two patterns (inference).** Spikes since 2023 were driven by non-payment activity and lasted days. The 2017 and 2021 episodes came from general demand and lasted weeks to months. A merchant can usually wait out the first kind. The second kind would outlast a typical due date.

**Fee as a share of the invoice.** Assumptions: 141 vB payment (one input, two outputs, native SegWit). BTC at $65,000. Fee paid by the sender. Fee = rate × 141 × 65,000 / 100,000,000. All figures are arithmetic on those assumptions (inference). The spike rows use today's price, not the price on the day.

| Fee level | sat/vB | Fee (USD) | $20 | $200 | $2,000 | $20,000 |
|---|---|---|---|---|---|---|
| Low (2026 norm) | 1 | $0.09 | 0.46% | 0.046% | 0.0046% | 0.00046% |
| Current "fastest" (2026-11-17) | 3 | $0.27 | 1.37% | 0.14% | 0.014% | 0.0014% |
| Busy but ordinary (2024-style) | 20 | $1.83 | 9.2% | 0.92% | 0.092% | 0.0092% |
| Spike (Dec 2023 monthly median) | 150 | $13.75 | 68.7% | 6.9% | 0.69% | 0.069% |
| Extreme (halving-day class) | 500 | $45.83 | 229% | 22.9% | 2.29% | 0.23% |

**Comparison rows (arithmetic from cited prices).**

| Method | $20 | $200 | $2,000 | $20,000 |
|---|---|---|---|---|
| Card, 2.9% + $0.30 | $0.88 (4.4%) | $6.10 (3.05%) | $58.30 (2.92%) | $580.30 (2.90%) |
| Card, international, +1.5% | $1.18 (5.9%) | $9.10 (4.55%) | $88.30 (4.42%) | $880.30 (4.40%) |
| ACH via Stripe, 0.8% capped at $5 | $0.16 (0.8%) | $1.60 (0.8%) | $5.00 (0.25%) | $5.00 (0.025%) |
| Incoming domestic wire, $15 | 75% | 7.5% | 0.75% | 0.075% |

- **Stripe US pricing.** 2.9% + 30 cents per domestic card charge. +1.5% international. $15 per dispute. ACH Direct Debit 0.8% capped at $5.00. [Stripe pricing]
- **Card industry average (snippet only; paywalled).** US merchant processing fees were $198.25 billion in 2025, a weighted average of 1.59% of purchase volume. [Nilson Report]
- **Wire (snippet only; publication date not checked).** Survey median $15 for an incoming domestic wire. [NerdWallet wires]
- **ACH (Feb 2022, >12 mo; no newer public edition found).** Median cost to send or receive an ACH payment $0.26 to $0.50. [Nacha AFP survey]
- **Break-even with the card fee (inference).** The on-chain fee equals 2.9% + 30 cents at an invoice of about $53 (20 sat/vB), about $464 (150 sat/vB), or about $1,570 (500 sat/vB). At 1 to 3 sat/vB the on-chain fee is below the card's fixed 30 cents at every size.
- **Who bears it differs (inference).** Card and ACH fees come out of the merchant's receipts. The on-chain fee is paid by the client on top of the invoice. In a spike the cost shows up as client friction.
- **Left out of the tables.** Exchange spread or conversion fees if the merchant sells for dollars, price movement while waiting, and the later cost of spending received payments (next section).

**The minimum fee dropped in 2025.**

- **Bitcoin Core 29.1 (2025-09-04) and 30.0 (2025-10-10) cut the default minimum relay fee from 1 sat/vB to 0.1 sat/vB.** The Core wallet's own fee rates "remain unchanged". [Bitcoin Core 29.1], [Bitcoin Core 30.0], [Optech min relay fee]
- **Miner uptake (mid-2025 press, snippet only, unverified against primary data).** Over 26% of hash rate, rising to about 85% after Foundry joined. [CriptoNoticias 1], [CriptoNoticias 2]
- **Mempool on 2026-11-17.** 64,730 transactions, 31.9 MvB. Only about 1.5 MvB bid 1 sat/vB or more. The rest bid about 0.10 to 0.12 sat/vB. [mempool API], [mempool projected blocks]
- **Inference.** The backlog that appeared in July and August 2026 is a queue of near-floor bids, not a fee spike. A payer bidding 1 to 3 sat/vB gets into the next block or two. A payer bidding 0.1 to 0.2 sat/vB joins a queue about 30 blocks deep, so hours to days is plausible.

**Sources:**
- mempool fees: https://mempool.space/api/v1/fees/recommended
- mempool precise fees: https://mempool.space/api/v1/fees/precise
- mempool fee-rate history: https://mempool.space/api/v1/mining/blocks/fee-rates/3y
- Blockchain.com fees USD: https://api.blockchain.info/charts/transaction-fees-usd?timespan=all&format=json&sampled=false
- Blockchain.com tx count: https://api.blockchain.info/charts/n-transactions?timespan=all&format=json&sampled=false
- Optech size calculator: https://bitcoinops.org/en/tools/calc-size/
- Bitcoin Wiki miner fees: https://en.bitcoin.it/wiki/Miner_fees
- Galaxy Research: https://www.galaxy.com/insights/research/bitcoin-onchain-fees-utxo
- The Block Runes: https://www.theblock.co/post/290034/bitcoins-average-transaction-fee-comes-down-after-reaching-record-high-amid-runes-rollout
- Cointelegraph OKX: https://cointelegraph.com/news/bitcoin-network-transaction-fees-temporarily-soar-to-nearly-52
- Hashrate Index: https://hashrateindex.com/blog/hashrate-index-roundup-june-10-2024/
- Decrypt OKX: https://decrypt.co/234446/bitcoin-fees-skyrocket-okx-exchange-burning-utxo?amp=1
- Stripe pricing: https://stripe.com/pricing
- Nilson Report: https://nilsonreport.com/articles/merchant-processing-fees-in-the-united-states-2025/
- NerdWallet wires: https://www.nerdwallet.com/article/banking/wire-transfers-what-banks-charge
- Nacha AFP survey: https://nacha.org/news/ach-costs-are-fraction-check-costs-businesses-afp-survey-shows
- Bitcoin Core 29.1: https://bitcoincore.org/en/releases/29.1/
- Bitcoin Core 30.0: https://bitcoincore.org/en/releases/30.0/
- Optech min relay fee: https://bitcoinops.org/en/topics/default-minimum-transaction-relay-feerates/
- CriptoNoticias 1: https://www.criptonoticias.com/mineria/transacciones-1-satvb-normalizan-bitcoin/
- CriptoNoticias 2: https://www.criptonoticias.com/mineria/pool-mineria-bitcoin-transacciones-1sat-vb/
- mempool API: https://mempool.space/api/mempool
- mempool projected blocks: https://mempool.space/api/v1/fees/mempool-blocks

## On-chain: the receiver's later cost, small payments and privacy

- **The sender pays by custom.** "by custom the spender is almost always solely responsible for paying all necessary Bitcoin transaction fees." [Bitcoin Wiki miner fees]
- **The merchant pays later.** Each received payment becomes one input that costs about 68 vB to spend (native SegWit 68.25, Taproot 57.75, legacy 148). [Optech size calculator]
- **Worked example (inference, $65,000).** Sweeping 100 received payments into one output is 6,866.5 vB. At 1 sat/vB: $4.46 total, about 4.4 cents per payment. At 20 sat/vB: $89.26. At 150 sat/vB: $669.48, about $6.65 per payment.
- **As a share of what was received, at 150 sat/vB (inference).** 33% of a $20 payment, 3.3% of $200, 0.33% of $2,000.
- **The merchant controls the timing (inference).** Sweeping in quiet periods locks in the cheap rate. For a handful of invoices a month worth hundreds or thousands of dollars, this cost is small in any fee climate.
- **Dust limit.** Nodes refuse to relay outputs below 294 sats (native SegWit) or 546 sats (legacy). That is about $0.19 and $0.35 at $65,000. [Bitcoin Core policy], [Optech uneconomical outputs]
- **Practical floor (inference, $65,000).** Sender's fee plus the receiver's later input cost is 209.25 vB. That combined cost is 1% of the payment at about $14 (1 sat/vB), $41 (3), $272 (20) or $2,040 (150 sat/vB).
- **Inference.** For invoices of hundreds to thousands of dollars, on-chain was economical in every fee climate seen in 2025 and 2026. Below about $10 to $50 it is only sensible while fees stay near the floor.
- **Privacy is the weak spot.** "All Bitcoin transactions are public, traceable, and permanently stored in the Bitcoin network." "To protect your privacy, you should use a new Bitcoin address each time you receive a new payment." [bitcoin.org privacy]
- **Consolidation links addresses (snippet only, vendor).** A transaction with many inputs and one output signals that those addresses share one owner. [Spark UTXO glossary]
- **Inference.** A client who knows one invoice's address can follow where the merchant later sends those coins. This is a business-confidentiality cost, not a security one.

**Sources:**
- Bitcoin Wiki miner fees: https://en.bitcoin.it/wiki/Miner_fees
- Optech size calculator: https://bitcoinops.org/en/tools/calc-size/
- Bitcoin Core policy: https://raw.githubusercontent.com/bitcoin/bitcoin/master/src/policy/policy.cpp
- Optech uneconomical outputs: https://bitcoinops.org/en/topics/uneconomical-outputs/
- bitcoin.org privacy: https://bitcoin.org/en/protect-your-privacy
- Spark UTXO glossary: https://www.spark.money/glossary/utxo-consolidation

## On-chain: waits, stuck payments, RBF/CPFP

**Waits.**

- **Blocks arrive at random around a 10-minute average.** The chance a block has arrived within t minutes is 1 − e^(−t/10) (snippet only for the formula). [Spark block time], [O'Connor block time]
- **The model fits history (2021-02-20, >12 mo; a statistical property, not re-tested on newer blocks).** Of about 670,000 blocks, 190 came more than 106 minutes after the previous one: 0.0028% observed versus 0.0025% predicted. [Lopp block variance]
- **Share of blocks taking longer than (computed from the model).** 10 min: 36.8%. 20 min: 13.5%. 30 min: 5.0%. 60 min: 0.25%. That is about 7 gaps over 30 minutes every day, and one gap over 60 minutes about every 2.8 days.
- **Waiting does not shorten the wait (computed).** After 20 minutes without a block the expected remaining wait is still 10 minutes.
- **Time to N confirmations for a payment that bids enough for the next block (computed from the model).**

| Confirmations | Average | Median | Over 30 min | Over 60 min | Over 90 min | Over 120 min |
|---|---|---|---|---|---|---|
| 1 | 10 min | 6.9 min | 5.0% | 0.25% | 0.01% | under 0.01% |
| 3 | 30 min | 26.7 min | 42.3% | 6.2% | 0.6% | 0.05% |
| 6 | 60 min | 56.7 min | 91.6% | 44.6% | 11.6% | 2.0% |

- **Measured median confirmation time for fee-paying transactions (computed half-year averages).** 2024-H2 10.2 min. 2025-H1 8.1. 2025-H2 7.4. 2026-H1 10.5. 2026-H2 (to Nov 16) 11.6. [Blockchain.com median confirmation]
- **The mean is much higher than the median.** Low-bidding transactions wait. Typical daily means were about 23 to 47 minutes across 2025 and 2026 (computed). [Blockchain.com average confirmation]
- **Fee estimates carry no guarantee.** They "do not guarantee transaction confirmation in any period of time." [mempool FAQ]
- **Inference.** For invoices due in days or weeks, an hour to six confirmations is not a practical constraint. It matters for on-the-spot delivery.

**Stuck payments.**

- **RBF (the sender's fix).** Replace-by-fee "allows an unconfirmed transaction in a mempool to be replaced with a different transaction" that pays a higher fee. [Optech RBF]
- **CPFP (the receiver can do it).** "If you control one of the stuck transaction's outputs, you can use CPFP" to raise its effective fee rate. A paid acceleration service is a third route. [mempool FAQ], [Optech CPFP]
- **Receiver-side CPFP costs the merchant (inference, $65,000).** Parent 141 vB stuck at 1 sat/vB, child about 110 vB, target 20 sat/vB for the pair: 4,879 sats, about $3.17.
- **Receiver-side CPFP needs a signing wallet (inference).** A watch-only invoicing tool cannot do it. The merchant's own wallet would.
- **Full RBF is the default.** Bitcoin Core 28.0 (2024-10-02): "The default value of the `-mempoolfullrbf` configuration option has been changed from 0 to 1". (>12 mo; still holds.) The option was later removed altogether (Bitcoin Core #30592, covered by Optech 2024-11-15). [Bitcoin Core 28.0], [Optech RBF]
- **Zero confirmations was never safe by the reference guidance.** It is "Only safe if you trust the person paying you." [bitcoin.org need to know]
- **Inference.** Treat an unconfirmed payment as "seen, not settled". Mark an invoice paid at the first confirmation, or more for large amounts.
- **Inference.** RBF is also the friendly tool. A client who underpaid the fee can bump it without the merchant doing anything.

**Sources:**
- Spark block time: https://www.spark.money/glossary/block-time
- O'Connor block time: https://r6.ca/blog/20180225T160548Z.html
- Lopp block variance: https://blog.lopp.net/bitcoin-block-time-variance/
- Blockchain.com median confirmation: https://api.blockchain.info/charts/median-confirmation-time?timespan=3years&format=json&sampled=false
- Blockchain.com average confirmation: https://api.blockchain.info/charts/avg-confirmation-time?timespan=3years&format=json&sampled=false
- mempool FAQ: https://mempool.space/docs/faq
- Optech RBF: https://bitcoinops.org/en/topics/replace-by-fee/
- Optech CPFP: https://bitcoinops.org/en/topics/cpfp/
- Bitcoin Core 28.0: https://bitcoincore.org/en/releases/28.0/
- bitcoin.org need to know: https://bitcoin.org/en/you-need-to-know

## What payers can actually use (exchanges, wallets, caps, cold storage)

**Can send Lightning.**

- **Cash App.** "You can send, or receive payment of up to $999 in bitcoin every 7 days using the Lightning Network." Not available in New York. (Snippet only; page returned empty when opened.) [Cash App help]
- **Coinbase.** Launched Lightning in April 2024. Launch cap was $2,000 per withdrawal. (Secondary; >12 mo; current cap not confirmed, help page returned HTTP 403.) [The Block Coinbase launch]
- **Coinbase terms (snippet only, undated help pages).** Lightning "is currently not available in New York and Canada". Invoices expire after 72 hours. Processing fee on Lightning sends is 0.2%. [Coinbase Lightning help], [Coinbase fees]
- **Kraken.** Lightning withdrawals supported. Account must be verified. The amount on the invoice "cannot be left blank". The page states no minimum or maximum. [Kraken Lightning]
- **Kraken fee (snippet only).** Lightning withdrawal fee "0 + 0.1%". Withdrawal limits are "dynamic". [Kraken fees], [Kraken limits]
- **OKX (snippet only).** 0.05 BTC per transaction and 0.5 BTC per day. Conflict on whether OKX still supports Lightning: see the conflicts section. [OKX help]
- **Binance (secondary; >12 mo).** Completed Lightning deposits and withdrawals in July 2023. No Lightning-specific cap found. [Bitcoin.com Binance], [LightningExchanges list]
- **Strike business accounts (snippet only).** "Send bitcoin via Lightning | Up to ₿0.05 / transaction, unlimited transactions." [Strike business limits]
- **River (secondary).** Listed with full Lightning support since October 2019. [LightningExchanges list]
- **A crowd-maintained list shows 31 exchanges with full Lightning support (secondary).** Its newest dated entry is July 2024, so it may be stale. [LightningExchanges list]

**Cannot send Lightning.**

- **Robinhood (read, Nov 2026).** "We don't currently support lightning network transactions or address formats that start with "bc1p" (taproot)." [Robinhood support]
- **Bitstamp (secondary).** Runs Lightning nodes but does not support Lightning deposits or withdrawals. [LightningExchanges list], [Spark exchange comparison]
- **Gemini (secondary, vendor; no official source found).** "does not currently offer Lightning withdrawals". [Spark exchange comparison]
- **Ledger, Trust Wallet, Exodus, Coinbase Wallet (secondary; vendor page of a competing wallet).** Listed as no Lightning. Exodus is disputed: see the conflicts section. [Starr wallets]
- **Trezor (secondary).** A 2026 comparison table lists Trezor Safe 3 and Ledger Nano S Plus as "No" for Lightning. [Swapzilla]
- **PayPal and Sparrow.** No source either way. Do not assert.

**Cold storage and business treasuries.**

- **Blockstream Jade (2026-03-12).** It does not open Lightning channels. It pays an invoice by swapping Liquid bitcoin: "your LBTC funds are used to pay it through an atomic swap." Incoming payments use Boltz swaps. [Blockstream Jade]
- **Boltz went offline (secondary).** Boltz disabled its swap services on 2026-08-03, citing repeated attacks, saying "we cannot responsibly re-enable Boltz Swaps." Refunds for in-flight swaps still work. The latest coverage found says it "remains offline". Status on 2026-11-17 was not confirmed. [Bitbo], [The Defiant], [Cointelegraph Boltz], [TFTC]
- **Blockstream Swaps (secondary).** Announced in August 2026 as "currently in beta testing with select participants." [Bitbo], [crypto.news]
- **Open question for the writer.** Aqua's Lightning receive and Jade's incoming Lightning both relied on Boltz. Whether they work after the outage was not checked.
- **Inference (not a cited fact).** For a treasury in multisig, paying a Lightning invoice means signing an on-chain transaction to fund a hot Lightning wallet, exchange or swap, then paying. Lightning gives that payer no speed or fee advantage over paying on-chain. No source was found on how multisig providers handle Lightning.

**What the caps mean for an invoice (inference).**

- A client paying a $1,500 invoice from Cash App cannot do it over Lightning in one go.
- 0.05 BTC (OKX, Strike business) is $4,000 at $80,000 per BTC and $5,000 at $100,000 (illustrative prices). At $65,000 it is $3,250.
- A New York client using Cash App or Coinbase has no Lightning option.
- Any bitcoin holder can pay an on-chain address. No primary source was found that counts wallet and exchange support for on-chain versus Lightning, so "works with nearly every wallet and exchange" is a commonly held claim, not a measured one.

**Sources:**
- Cash App help: https://cash.app/help/6506-lightning
- The Block Coinbase launch: https://www.theblock.co/post/291662/coinbase-rolls-out-lightning-network-support-in-collaboration-with-lightspark
- Coinbase Lightning help: https://help.coinbase.com/en/coinbase/trading-and-funding/sending-or-receiving-cryptocurrency/lightning
- Coinbase fees: https://help.coinbase.com/en/coinbase/trading-and-funding/pricing-and-fees/fees
- Kraken Lightning: https://support.kraken.com/articles/5068216131988
- Kraken fees: https://support.kraken.com/articles/360000767986-cryptocurrency-withdrawal-fees-and-minimums
- Kraken limits: https://support.kraken.com/articles/deposit-and-withdrawal-limits
- OKX help: https://www.okx.com/en-us/help/how-do-i-withdraw-bitcoin-btc-with-okx-lightning-network
- Bitcoin.com Binance: https://news.bitcoin.com/binance-integrates-lightning-network-deposits-and-withdrawals/
- LightningExchanges list: https://github.com/theDavidCoen/LightningExchanges
- Strike business limits: https://strike.me/faq/what-are-the-transaction-limits-for-businesses/
- Robinhood support: https://robinhood.com/us/en/support/articles/cryptocurrency-wallets
- Spark exchange comparison: https://www.spark.money/tools/crypto-exchange-withdrawal-fee-comparison
- Starr wallets: https://starr.app/wallets-that-support-lightning
- Swapzilla: https://swapzilla.io/blog/best-bitcoin-wallets-2026/
- Blockstream Jade: https://blog.blockstream.com/jade-lightning-payments-are-here/
- Bitbo: https://bitbo.io/news/blockstream-launches-swaps-boltz/
- The Defiant: https://thedefiant.io/news/defi/bitcoin-bridge-boltz-halts-swaps-indefinitely-citing-ai-assisted-attacks
- Cointelegraph Boltz: https://regional-front.cointelegraph.com/news/boltz-pauses-service-after-wave-of-ai-assisted-hacking-attempts
- TFTC: https://www.tftc.io/boltz-bitcoin-swaps-suspended-ai-attacks
- crypto.news: https://crypto.news/blockstream-launches-swaps-service-after-boltz-suspends-operations/

## Offering both in one QR code

- **Format.** `bitcoin:<address>?amount=<BTC>&lightning=<BOLT11 invoice>`. Wallets that only understand on-chain use the address. Lightning-aware wallets read the `lightning` parameter. A BOLT12 offer uses `lno=`. The site now describes this under BIP 321. [bitcoinqr.dev]
- **BIP 321 is the newer URI scheme (snippet only).** [BIP 321]
- **Support is uneven (snippet only).** The test matrix separates apps that scan the QR, apps that recognise the Lightning part, and apps that can create one. Some apps scan but ignore Lightning. [bitcoinqr.dev mirror]
- **Reported supporters (secondary).** Aqua (v0.1.50), Phoenix, Zeus and Alby Hub. A tester reported Cash App recognising the Lightning invoice. [No BS Bitcoin Aqua], [Spark protocol comparison]
- **Expired invoice fallback (snippet only, one tester).** A wallet scanning a unified QR with an expired Lightning invoice falls back to the on-chain address. [bitcoinqr.dev mirror]
- **Inference.** The worst case with an on-chain-only wallet is that it pays on-chain, which still settles the invoice. On a long-lived invoice the Lightning part expires, so the QR degrades toward on-chain only unless refreshed.
- **Not available.** The full wallet-by-wallet compatibility table did not load. No count of supporting wallets can be given.

**Sources:**
- bitcoinqr.dev: https://bitcoinqr.dev/
- BIP 321: https://bips.dev/321/
- bitcoinqr.dev mirror: https://bip21.netlify.app
- No BS Bitcoin Aqua: https://www.nobsbitcoin.com/aqua-wallet-v0-1-50/
- Spark protocol comparison: https://www.spark.money/tools/bitcoin-payment-protocol-comparison

## Usage split and payment sizes in practice

**Share of merchant bitcoin payments over Lightning (CoinGate, by order count).**

| Period | Lightning share of BTC orders | Source |
|---|---|---|
| 2022 | 5.98% | CoinGate, 2024-08-13 |
| 2023 | 7.95% | same |
| 2024 (Jan–Jul) | 14.51%; monthly peak 18.5% in June 2024 | same |
| 2024 (full year, peak) | 15.41% | CoinGate via Chainwire, 2025-10-28 |
| 2025 (full year) | 11.3% Lightning, 88.7% on-chain | CoinGate 2025 report, 2026-01-21 |
| H1 2026 | 9.6% Lightning, rest on-chain | CoinGate H1 2026 report, 2026-07-14 |

- **By count, not value.** CoinGate's wording is "of BTC orders". No merchant processor found publishes Lightning's share by value. [CoinGate Lightning post], [CoinGate 2025 report]
- **Scale.** 292,217 bitcoin orders in 2025. 152,254 Bitcoin-network orders in H1 2026. [CoinGate 2025 report], [CoinGate H1 2026 report]
- **Caveat.** CoinGate is one mostly-European processor with a retail and digital-goods mix. It is not the whole market.
- **Coinbase (>12 mo; no newer figure found).** Said in April 2025 that Lightning handles 15% of its bitcoin transaction volume. Sources differ on "volume" versus "transactions", and neither says count or dollar value. This is an exchange, not merchant payments. [The Block Coinbase 15%], [Lightspark Coinbase]
- **BitPay (snippet only; charts did not load).** 6,365 Lightning transactions over the last 6 months and 791 in the last 30 days, as of Nov 2026. [BitPay stats]
- **BTCPay Server.** Its 2025 report (2026-01-22) gives no payment volume and no Lightning split. It is self-hosted, so there is no central dataset. [BTCPay 2025 report]

**Network-wide Lightning estimates (River; all Lightning activity, not just merchants).**

- **August 2023 (>12 mo; superseded).** Lower bound of 6.6 million routed transactions, about $78.2 million, average about $11.84. [River 2023 report]
- **November 2025 (secondary; published Feb 2026).** About $1.17 billion across about 5.22 million transactions, average $223, up from $118 a year earlier. Some coverage rounds this to $1.1 billion and 5.2 million. [Bitcoin Magazine River], [Cointelegraph River]
- **Reason given (secondary).** The main use is moving larger sums between exchanges. Micropayment experiments in gaming and messaging "faded." [Bitcoin Magazine River]
- **Method (secondary).** River aggregates data from large node operators and extrapolates. Lightning has no public ledger, so all such numbers are estimates. [Bitcoin Magazine River]
- **Inference.** Both trends can be true. Lightning's share of merchant checkout orders at CoinGate fell after 2024 while network dollar volume grew about fourfold in a year. Growth came from fewer, bigger payments.
- **Inference.** Lightning's share by value at a merchant processor is very likely lower than its share by count. No source states the value share.

**Payment sizes.**

- **CoinGate average bitcoin cart (on-chain and Lightning combined).** €132 in 2025. [Chainwire CoinGate]
- **Square caps each bitcoin payment at $600**, with a $20,000 daily limit. Payments taking more than five minutes are declined. [Square help]
- **Largest BTCPay payment noted in 2025.** "Namecheap processed a 2M USD Bitcoin payment using BTCPay Server." The report does not say which rail. [BTCPay 2025 report]
- **Not found.** A median Lightning payment size. An average on-chain merchant payment size.
- **Inference.** A freelance invoice of a few hundred dollars is inside what Lightning routinely handles. An invoice of several thousand dollars runs into sender caps before it runs into a routing problem.

**Merchant rollouts.**

- **Square.** Customers pay with a Lightning-enabled wallet by QR code or NFC tap. No on-chain option is described. "Bitcoin payments settled in dollars are now auto-enabled for new sellers in the U.S for eligible locations by default." Refunds go out as a gift card. "Bitcoin payments aren't compatible with invoices that allow partial payments." [Square help]
- **Square enrollment (secondary).** Block's bitcoin product lead said on 2026-04-28 that more than 800,000 Square businesses had auto-enrollment enabled. Enrollment is not usage. No Square bitcoin payment volume was found. [Crypto Briefing Square], [Bitcoin Magazine Square]
- **Steak 'n Shake (secondary; company claims, unaudited).** About 50% lower processing fees when a customer pays in bitcoin over Lightning instead of by card. It "has not disclosed what percentage of its transactions now run on Lightning." [Bitcoin Magazine Steak n Shake], [SpendNode]
- **Stripe and Shopify chose stablecoins (snippet only).** Stripe accepts USDC, USDP and USDG. Shopify's native crypto option is USDC on Base through Stripe. Neither is bitcoin or Lightning. [Stripe stablecoin docs], [Stripe newsroom]
- **Stablecoin context.** At CoinGate in H1 2026, "USDC became the most-used payment asset at 22.1%, overtaking BTC at 21.0%." Bitcoin "held steady near 21%." [CoinGate H1 2026 report]

**Public network size.**

- **Live record dated 2026-11-18 UTC.** 26,511 channels, 13,827 nodes, 3,151.76 BTC total capacity (about $203 million at $64,450). [emzy LN stats]
- **Trend (same series; channels / BTC).** 1 Jan 2026: 41,648 / 5,743.7. 1 Jul 2026: 40,456 / 4,602.7. 10 Nov 2026: 26,944 / 3,247.0. Capacity peak: 5,851.2 BTC on 28 Dec 2025. [emzy LN series]
- **Inference.** From 1 Jul to 18 Nov 2026 public channels fell about 34% and public capacity about 32%. No primary source explains it. One secondary source says four large operators closed 2,563 channels between 2 and 16 Aug 2026. [CoinPaprika]
- **Public capacity understates use (2023, >12 mo).** "Not all of the Lightning Network is public." When sender and recipient use the same custodian "the transaction is settled in the database of that service, without ever touching the Lightning Network." [River 2023 report]
- **Capacity is a weak proxy for use.** "An increase in capacity on Lightning is not a good thing by default. What matters is the efficiency of this capacity." (2023, >12 mo.) [River 2023 report]
- **Caution.** The usual source (mempool.space's own Lightning API) had not updated since 2026-08-30. Node counts are the least reliable series. Prefer channel and capacity figures. [mempool LN stats]

**Sources:**
- CoinGate Lightning post: https://coingate.com/blog/post/lightning-network-year-over-year-data
- Chainwire CoinGate: https://chainwire.org/2025/10/28/coingate-data-shows-amid-stablecoin-rise-bitcoin-remains-the-top-choice-for-retail-crypto-payments/
- CoinGate 2025 report: https://coingate.com/blog/post/crypto-payments-data-report-2025
- CoinGate H1 2026 report: https://coingate.com/blog/post/crypto-payments-data-report-h1-2026
- The Block Coinbase 15%: https://www.theblock.co/post/352241/lightning-network-facilitates-15-of-coinbases-bitcoin-transaction-volume-firm-says
- Lightspark Coinbase: https://www.lightspark.com/news/lightspark/coinbase-lightning-network-lightspark
- BitPay stats: https://bitpay.com/stats
- BTCPay 2025 report: https://blog.btcpayserver.org/2025-report/
- River 2023 report: https://www.river.com/learn/files/river-lightning-report-2023.pdf
- Bitcoin Magazine River: https://bitcoinmagazine.com/news/bitcoins-lightning-network-surpasses
- Cointelegraph River: https://cointelegraph.com/news/bitcoin-lightning-network-1b-monthly-volume
- Square help: https://squareup.com/help/us/en/article/8622-accept-and-manage-bitcoin-payments
- Crypto Briefing Square: https://cryptobriefing.com/bitcoin-payment-adoption-square
- Bitcoin Magazine Square: https://bitcoinmagazine.com/news/square-begins-automatic-bitcoin-payment
- Bitcoin Magazine Steak n Shake: https://bitcoinmagazine.com/news/bitcoin-is-fueling-steak-n-shake-comeback
- SpendNode: https://www.spendnode.io/blog/steak-n-shake-bitcoin-50-percent-processing-fees-april-2026/
- Stripe stablecoin docs: https://docs.stripe.com/payments/stablecoin-payments
- Stripe newsroom: https://stripe.com/newsroom/news/shopify-stripe-stablecoin-payments
- emzy LN stats: https://mempool.emzy.de/api/v1/lightning/statistics/latest
- emzy LN series: https://mempool.emzy.de/api/v1/lightning/statistics/3y
- CoinPaprika: https://coinpaprika.com/education/bitcoin-lightning-network/
- mempool LN stats: https://mempool.space/api/v1/lightning/statistics/latest

## Record-keeping, refunds and tax

**Proof of payment.**

- **On-chain leaves a public record.** "All Bitcoin transactions are stored publicly and permanently on the network." [bitcoin.org need to know]
- **Lightning proof (secondary).** The payee's node signs the invoice and releases the payment preimage on success. Invoice plus preimage show the payment was made. [Lightspark glossary], [Blink proof FAQ]
- **Lightning has no public ledger (secondary).** Even network-wide statistics have to be estimated from operators' private data. [Bitcoin Magazine River]
- **Known weakness.** Lightning's proof of payment "does not prove who paid" (Optech item, 2018). [Optech proof of payment]
- **Merchant tooling (2023, >12 mo; assumed still present, not re-checked).** BTCPay Server added proof of payment to each payment's receipt page. [Optech proof of payment]
- **New in 2026, experimental.** BOLT12 "payer proofs" were specified (noted 2026-07-31). Core Lightning 26.06 added experimental support (2026-06-05). [Optech proof of payment]
- **Inference.** On-chain, the merchant can hand over a transaction ID and anyone can check the time and amount. On Lightning, the merchant must keep their own records (invoice, preimage, timestamp, dollar value, processor export). If the node database or provider history is lost, there is nothing public to fall back on.

**Tax (US).**

- **No IRS guidance found that mentions Lightning.** The IRS virtual currency FAQ page does not use the word. "Page Last Reviewed or Updated: 30-Jun-2026." A tax professional should confirm. [IRS FAQ]
- **The income rule is the same on any rail.** Virtual currency received for services is income at "the fair market value of the virtual currency, in U.S. dollars, when received". [IRS FAQ]
- **Off-chain rule.** For transactions "not recorded on the distributed ledger", value is set at the time the transaction "would have been recorded". The IRS accepts a blockchain explorer value as evidence. Reading a Lightning payment as "off-chain" here is an inference, not an IRS statement. [IRS FAQ]
- **Records.** Taxpayers must "maintain records that are sufficient to establish the positions taken on tax returns". [IRS FAQ]
- **Scope note.** The page says these FAQs "generally apply to transactions involving digital assets completed before Jan. 1, 2025." [IRS FAQ]
- **Form 1099-DA (snippet only / secondary).** Brokers began sending the form in early 2026. It is a broker obligation, not a rail-specific one. [IRS 1099-DA], [CRI advisory]

**Refunds.**

- **Neither rail has chargebacks.** "A Bitcoin transaction cannot be reversed, it can only be refunded by the person receiving the funds." [bitcoin.org need to know]
- **On-chain refund (BTCPay).** The merchant issues a refund and shares a claim link. The customer "adds their refund bitcoin address and claims the invoice", then the merchant approves and broadcasts. The page describes the on-chain flow only. [BTCPay refund docs]
- **Lightning refund (secondary, vendor).** BOLT 11 "has no protocol support for refunds". BOLT 12 adds a refund flow. [Spark invoices]
- **Square.** Bitcoin-payment "refunds are issued via a gift card for the equivalent amount of bitcoin in dollars". [Square help]
- **Inference.** Either way the merchant has to get a destination from the customer. On-chain it is an address, which does not expire. On Lightning it is an invoice, which expires and must be for the exact amount.
- **Inference.** Refunding to the sending address or node is unsafe on both rails. An exchange payment does not come from an address the customer controls, and a Lightning payment does not reveal the payer.

**Sources:**
- bitcoin.org need to know: https://bitcoin.org/en/you-need-to-know
- Lightspark glossary: https://lightspark.com/glossary/payment-preimage
- Blink proof FAQ: https://faq.blink.sv/blink-and-other-wallets/how-to-prove-that-a-lightning-invoice-was-paid
- Optech proof of payment: https://bitcoinops.org/en/topics/proof-of-payment/
- Bitcoin Magazine River: https://bitcoinmagazine.com/news/bitcoins-lightning-network-surpasses
- IRS FAQ: https://www.irs.gov/individuals/international-taxpayers/frequently-asked-questions-on-virtual-currency-transactions
- IRS 1099-DA: https://www.irs.gov/instructions/i1099da
- CRI advisory: https://www.criadv.com/insight/irs-form-1099-da-digital-asset-reporting-2026/
- BTCPay refund docs: https://docs.btcpayserver.org/Refund/
- Spark invoices: https://www.spark.money/research/lightning-invoices-bolt11-bolt12
- Square help: https://squareup.com/help/us/en/article/8622-accept-and-manage-bitcoin-payments

## Where each one fits

Everything in this section is inference drawn from the facts above. Each line names the section that holds the sources.

**Lightning fits best when:**

- The payer is standing at a counter and seconds matter. (Usage split: Square.)
- The amount is too small to justify an on-chain fee and wait. Under about $10 to $50, on-chain only makes sense while fees stay near the floor. (On-chain: the receiver's later cost.)
- On-chain fees are in a spike. In the fee table, the fee on a $200 invoice is 6.9% at 150 sat/vB. Channel opens and closes also cost more in a spike.
- The merchant wants no public trail of payments. Lightning has no public ledger. (Record-keeping.) Privacy differences are otherwise largely unsourced.
- The merchant accepts a custodian or an LSP wallet and its roughly 0% to 1% cost. (Three ways.)

**On-chain fits best when:**

- The invoice is paid remotely and due in days or weeks. (On-chain: waits.)
- The invoice is in the thousands of dollars. Sender caps and channel sizes bind before routing does. (Payment size; What payers can use.)
- The merchant cannot choose the client's wallet. Hardware wallets, multisig treasuries and some exchanges have no direct Lightning path. (What payers can use.)
- The merchant wants nothing to run. The receiver can be offline, with no channel, no inbound liquidity and no hot keys. (Staying online.)
- The merchant wants a record anyone can check. (Record-keeping.)

**Costs that belong on each side of the ledger.**

| | Lightning | On-chain |
|---|---|---|
| Speed | Seconds | About 10 minutes for one confirmation, about an hour for six |
| Fee to the payer | Routing fee, scales with amount | Flat per transaction, scales with fee rate |
| Fee to the merchant | Liquidity, channel opens and closes, or about 1% to a processor | Later cost to spend each payment |
| Size limits | Inbound capacity and sender caps | None from the network |
| Must be online | Yes, unless a provider holds the payment | No |
| Public record | No | Yes |
| Fee-spike exposure | Channel opens, closes and force closes | Payer's fee and merchant's later spend |

**Middle path.** A unified QR offers both and lets the payer's wallet choose. (Offering both in one QR code.)

## Unverified or conflicting claims (do not print as fact)

**Conflicts.**

- **Alby Hub hosting price.** Pricing section shows Pro at $3.9/month. Page metadata says $9.9/month. A forum post says 21,000 sats/month. [Alby pricing]
- **Phoenix receive-side fee.** Third-party guides say "1,000 sat plus mining fees" with the 1% gone. ACINQ's FAQ still lists "1% + mining fees". Treat the FAQ as current. [Phoenix FAQ]
- **Wallet of Satoshi US exit date.** CryptoSlate and DailyCoin place it in November 2023. A Bitcoin Magazine digest says November 2024. [CryptoSlate WoS], [DailyCoin], [Bitcoin Magazine WoS]
- **Square rollout end date.** One search summary says rollout was due to finish by 2026-11-10. This looks like a mix-up with the 2025-11-10 launch date. Not confirmed. [Crypto Briefing Square default]
- **Coinbase Lightning send fee.** 0.1% per The Block (April 2024, >12 mo). 0.2% per a Coinbase help snippet (Nov 2026). [The Block Coinbase fee], [Coinbase fees]
- **OKX.** A live help page lists Lightning limits. One 2026 affiliate list says OKX "discontinued its support for the Lightning Network". Not resolved. [OKX help], [blockdyor]
- **Binance dates.** A marketing page claims withdrawals only arrived in "late 2025". That page has other date errors. Do not use it. [Bitget academy]
- **Exodus.** One guide says no Lightning as of 2026. Another says Exodus added it in 2025. Not resolved. [Starr wallets], [azcryptoexchanges]
- **LND large channels.** Optech's summary says LND "enabled large channels by default" in July 2020. LND's sample config shows the option off by default. Trust the config file. [Optech large channels], [LND sample config]
- **Olympus (ZEUS) price.** The 2025 survey lists 52,250 sats for 1M. ZEUS's fee page lists 10,000 sats for a JIT channel up to 1M. Different products and dates. [Stacker News LSP survey], [ZEUS LSP fees]
- **BOLT12 merge date.** Optech dates the spec merge 2024-10-04. A vendor explainer says September 2024. [Optech offers], [Spark BOLT12 adoption]
- **CoinGate 2023 share.** 7.8% in its 2023 annual report (snippet only). 7.95% in its later Lightning post. [CoinGate 2023 report], [CoinGate Lightning post]
- **Six-confirmation timing.** One search result quoted "57% of 6-block spans complete within 60 minutes, and about 3% take longer than two hours" (snippet only). The plain model gives 55.4% and 2.0%. [jozhe substack], [Spark block time]
- **BTC price.** $64,450 to $65,300 on 16 to 17 Nov 2026 versus $78,269 on 4 Nov 2026 from a secondary source. Not reconciled. See Scope.

**Unverified.**

- **"Below 4% success for payments above $1,000 with naive routing."** A vendor blog attributes this to Pickhardt and Richter 2021. The figure was not found in the abstract. [Spark large payments]
- **"River's 308k transactions averaged about $223."** Same vendor. River's own report gives $11.84 for that month. The $223 figure belongs to November 2025. [Spark large payments]
- **Diar 2018: 70% success for a few dollars, 1% near $200.** Developers disputed the method and the network was far smaller. Historical only. [Cointelegraph Diar]
- **"Lost about 4 BTC" (Oct 2019).** A Reddit user said they lost funds after force-closing from an old state. Seen only in search snippets. The BitMEX page returned 404. Whether funds were returned is unknown. [BitMEX Research], [Reddit 4 BTC]
- **Forum reports of channels stuck in "waiting close" for weeks after restores (2021 to 2024).** Anecdotal, not individually verified. [Umbrel waiting close], [Start9 restore]
- **Coinbase's $2,000 Lightning cap today.** Launch-era figure only. [The Block Coinbase launch]
- **Kraken's 0.1 BTC Lightning cap.** A 2022 launch figure, not on any current Kraken page reached. [NewsBTC Kraken]
- **"1 million bitcoin-enabled Square merchants."** Headline only. No primary source. [Yahoo Finance Square]
- **"Lightning is roughly 14% of all Bitcoin transactions at BitPay."** Secondary site, not confirmed against BitPay. [SQ Magazine]
- **Strike New York BitLicense on 2026-03-06.** Single third-party blog. [BlockEden Strike]
- **Wallet of Satoshi "Self-Custody Mode" launch on 2026-05-09.** One tracker item. Unverified. [WalletScrutiny]
- **Strike 1% spread on converted Lightning receipts.** Search digest only.
- **Breez deprecating its Greenlight-based SDK in 2025.** Single vendor source, search summary. [Spark Phoenix vs Breez]
- **Cash App raising limits and offering zero-fee Lightning spending in mid-2026.** Search summary of a competitor's newsletter. [Blink weekly brief]
- **Full-RBF option removal shipping in Bitcoin Core 29.0.** Vendor source, snippet only. The removal itself is confirmed by Optech. [Spark full RBF]
- **Double-spend success rates under full RBF.** Secondary estimates conflict (from under one in a million to about 11%). Neither verified. [Spark full RBF]
- **85% of hash rate mining sub-1 sat/vB transactions.** Mid-2025 press, snippet only. [CriptoNoticias 2]
- **"9,000 to 12,000 BTC of Lightning capacity including private channels."** Appears on vendor pages without method. No measured private-capacity figure exists.
- **Taproot dust limit of 330 sats.** Derived from the Core rule, not seen stated in the source. [Bitcoin Core policy]

**Sources:**
- Alby pricing: https://getalby.com/pricing
- Phoenix FAQ: https://phoenix.acinq.co/content/faq.md
- CryptoSlate WoS: https://cryptoslate.com/lightning-network-app-wallet-of-satoshi-ends-support-for-u-s-customers/
- DailyCoin: https://dailycoin.com/bitcoin-users-decry-wallet-of-satoshis-withdrawal-from-us/
- Bitcoin Magazine WoS: https://bitcoinmagazine.com/news/wallet-of-satoshi-partners-with-spark-to-offer-self-custodial-bitcoin-lightning-experience
- Crypto Briefing Square default: https://cryptobriefing.com/square-bitcoin-payments-default-us-merchants/
- The Block Coinbase fee: https://www.theblock.co/post/291662/coinbase-lightning-network-lightspark
- Coinbase fees: https://help.coinbase.com/en/coinbase/trading-and-funding/pricing-and-fees/fees
- OKX help: https://www.okx.com/en-us/help/how-do-i-withdraw-bitcoin-btc-with-okx-lightning-network
- blockdyor: https://blockdyor.com/best-exchanges-lightning-network/
- Bitget academy: https://www.bitget.com/academy/12560603875720
- Starr wallets: https://starr.app/wallets-that-support-lightning
- azcryptoexchanges: https://www.azcryptoexchanges.com/guides/best-lightning-network-crypto-wallets/
- Optech large channels: https://bitcoinops.org/en/topics/large-channels/
- LND sample config: https://github.com/lightningnetwork/lnd/blob/master/sample-lnd.conf
- Stacker News LSP survey: https://stacker.news/items/890432
- ZEUS LSP fees: https://docs.zeusln.app/lsp/fees/
- Optech offers: https://bitcoinops.org/en/topics/offers/
- Spark BOLT12 adoption: https://www.spark.money/research/lightning-bolt12-adoption-progress
- CoinGate 2023 report: https://coingate.com/blog/post/crypto-payments-report-2023
- CoinGate Lightning post: https://coingate.com/blog/post/lightning-network-year-over-year-data
- jozhe substack: https://jozhe.substack.com/p/how-often-does-bitcoin-take-an-hour
- Spark block time: https://www.spark.money/glossary/block-time
- Spark large payments: https://www.spark.money/research/lightning-large-payment-reliability
- Cointelegraph Diar: https://cointelegraph.com/news/study-low-reliability-for-successfully-routing-payments-on-lightning-network
- BitMEX Research: https://www.bitmex.com/blog/lightning-network-part-5-bitmex-research-launches-penalty-transaction-alert-system
- Reddit 4 BTC: https://www.reddit.com/r/Bitcoin/comments/dlvokv/how_i_lost_4_btc_on_lightning_network/
- Umbrel waiting close: https://community.umbrel.com/t/lightning-channel-stuck-in-waiting-close/6103
- Start9 restore: https://community.start9.com/t/backup-restore-lnd-issue-potential-lost-channel-and-sats-after-upgrading-lnd/4291
- The Block Coinbase launch: https://www.theblock.co/post/291662/coinbase-rolls-out-lightning-network-support-in-collaboration-with-lightspark
- NewsBTC Kraken: https://www.newsbtc.com/news/light-speed-kraken-another-giant-exchange-integrates-the-lightning-network
- Yahoo Finance Square: https://finance.yahoo.com/news/square-reaches-1-million-bitcoin-173000780.html
- SQ Magazine: https://sqmagazine.co.uk/bitpay-statistics/
- BlockEden Strike: https://blockeden.xyz/blog/2026/03/18/strike-new-york-bitlicense-bitcoin-lightning-payments-regulatory-milestone/
- WalletScrutiny: https://gitlab.com/walletscrutiny/walletScrutinyCom/-/work_items/947
- Spark Phoenix vs Breez: https://www.spark.money/tools/phoenix-vs-breez-wallet-comparison
- Blink weekly brief: https://blink.sv/blog/weekly-brief-2026-07
- Spark full RBF: https://www.spark.money/research/bitcoin-mempool-fullrbf-merchant-impact
- CriptoNoticias 2: https://www.criptonoticias.com/mineria/pool-mineria-bitcoin-transacciones-1sat-vb/
- Bitcoin Core policy: https://raw.githubusercontent.com/bitcoin/bitcoin/master/src/policy/policy.cpp

## Gaps

**Lightning, receiving side.**

- No primary source gives setup time for any route.
- No measured Lightning success rate by payment amount for 2023 to 2026. River's February 2026 report was seen only through press coverage.
- No published maximum for a single just-in-time payment at Phoenix or ZEUS beyond ZEUS's 5M-sat fee tier.
- No live prices for Lightning Pool, Magma, Megalith, FlashSats or Olympus advance leases. No live Boltz swap fee.
- No source states LND splicing as shipped or scheduled as of November 2026.
- Whether any shipping mobile wallet uses async payments in production.
- Whether Phoenix and Wallet of Satoshi are in US app stores today. Neither listing was checked.
- Node hardware cost, initial sync time, and Umbrel or Start9 setup steps.
- Receive fees and limits at Strike and Cash App from their own pages. Voltage prices. Alby's channel-open fees.
- Muun's current status and model.
- No survey of time spent per month by small merchants running a node. No 2026 first-hand merchant write-up (as opposed to routing-node operators).
- The force-close preprint's full PDF was not read. Per-year medians are unverified.
- No primary-source explanation for the July to November 2026 fall in public channels and capacity. No measured estimate of private channel capacity.

**On-chain.**

- No analyst-published 2026 median fee rate. The 2026 figures are computed from raw data.
- Fee rates in sat/vB for the 2017, 2021 and May 2023 spikes. Median (not average) dollar fees on spike days.
- The real 2025 to 2026 distribution of block intervals was not computed from block timestamps. The percentages come from the model plus one 2021 check.
- What the roughly 30 MvB of 0.1 sat/vB transactions are. Which wallets and exchanges allow sub-1 sat/vB fees.
- No primary measurement of double-spend success under full RBF. Share of miners running full RBF.
- No non-vendor merchant guide to consolidating received payments was opened.
- Exchange conversion costs for merchants who cash out.

**Payers and adoption.**

- Current Lightning send caps at Coinbase, Kraken, Binance, River and Strike individual accounts.
- PayPal, Venmo and Sparrow Lightning status. Trezor and Ledger status rests on secondary comparison pages.
- Whether Boltz is back. Check boltz.exchange before publishing. Blockstream Swaps availability, limits and fees.
- Whether Aqua and Jade Lightning receives work after the Boltz outage.
- Lightning's share of merchant payments by value. A 2026 Coinbase figure. BitPay's exact Lightning share.
- How multisig custody providers handle Lightning payments.
- No dataset breaks out B2B invoice payments by rail. No survey of business payers.
- Square bitcoin payment volume, transaction count and active-seller count.
- The full unified-QR wallet compatibility table.

**Records, refunds and tax.**

- No IRS, AICPA or FASB document was found that mentions Lightning by name. A tax professional should confirm.
- BTCPay's documented flow for Lightning refunds. How custodial processors handle refunds on each rail.
- BOLT12 refund and payer-proof wallet support.
