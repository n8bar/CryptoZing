# Research: Bitcoin vs. PayPal for Client Invoices

_Created: 2026-08-28_

## Article Intent

Answer one practical question for a freelancer or small business: should this client receive a Bitcoin invoice instead of a PayPal invoice?

This is a decision article, not another general guide to accepting Bitcoin. It should compare the complete payment workflows, give the answer near the top, and link out when a supporting concept would otherwise become a second article inside this one.

No Bitcoin sermon. No claim that one method wins for everyone. No video promise.

## Working Title and URL

- **Title:** Should I Invoice This Client in Bitcoin Instead of PayPal?
- **URL:** `/learn/bitcoin-vs-paypal-invoices/`
- **Optional metadata title:** Bitcoin vs. PayPal for Freelance Invoices

## Answer to Establish Early

If the client already has Bitcoin and the issuer is prepared to receive it, Bitcoin is worth offering. If the client would need to buy Bitcoin solely to pay this invoice, or the issuer needs a familiar invoice-to-bank path without a separate conversion step, PayPal is probably the cleaner option.

The choice is bilateral. The issuer's fee preference does not erase the client's payment friction or desire for buyer protection.

## Revised Outline

### 1. Hook and short answer

- Open on a $1,000 invoice and show one current, explicitly labeled U.S. PayPal fee example against an on-chain payment.
- State the jurisdiction, payment method, and as-of date; PayPal does not have one universal invoice rate.
- Say immediately that Bitcoin has no percentage-based network fee, but it is not costless: the payer normally pays a variable miner fee, and acquisition or conversion can add costs on either side.
- Give the short answer before expanding the comparison.

### 2. What is actually being compared

- PayPal bundles an invoice tool, payment processing, a fiat bridge, account custody, and a dispute system.
- A direct Bitcoin workflow combines an invoice tool, the issuer's wallet, the Bitcoin network, and possibly an exchange when either party needs to buy or sell BTC.
- These are different stacks doing overlapping jobs, not interchangeable payment apps.
- Use a compact comparison table for seller cost, client friction, payment visibility, settlement/finality, dispute handling, and access to fiat.

### 3. Fees from beginning to end

- Show the selected PayPal example accurately. Briefly note that card, PayPal/Venmo, Pay Later, ACH, international, and currency-conversion paths can produce different totals.
- Explain that a Bitcoin miner fee is based mainly on transaction data and network demand, not the dollar size of the invoice, and is normally paid in addition to the amount sent.
- Include costs outside the network fee: buying BTC, exchange withdrawal, selling BTC, spreads, and moving dollars to a bank.
- Treat an international client as a good Bitcoin candidate only when the client's actual acquisition and payment path supports that conclusion.
- Link to the broader accepting-Bitcoin guide rather than reopening setup, custody, and bookkeeping here.

### 4. Holds, disputes, finality, and refunds

- PayPal may hold funds and payments can enter disputes, reversals, or card chargebacks. Eligible transactions may receive Seller Protection; do not describe every dispute as an automatic seller loss.
- Bitcoin has no chargeback mechanism. After sufficient confirmation, the payer cannot unilaterally reverse the payment through the network.
- A Bitcoin refund remains possible, but it is a new transaction initiated by the recipient. Irreversibility does not cancel contractual, customer-service, or legal obligations.
- Treat the client's desire for buyer protection as a legitimate tradeoff. It is one reason some clients may refuse Bitcoin, not the only reason.
- Link confirmation mechanics to the pending-versus-confirmed article.

### 5. Volatility and access to fiat

- A USD-denominated invoice keeps the obligation understandable in dollars and narrows exchange-rate exposure; it does not eliminate volatility.
- CryptoZing keeps USD canonical, recomputes the requested BTC from the current rate, and values each payment at the rate captured when it is detected.
- The practical exposure includes the interval between loading the payment amount and detection, plus however long the issuer continues holding BTC afterward.
- If the recipient needs dollars, the conversion path and its timing belong in the decision.

### 6. The actual decision

**PayPal is probably the better fit when:**

- the client would need to open a wallet or buy BTC only for this invoice;
- the client requires a familiar dispute or buyer-protection process;
- the issuer wants a built-in fiat balance and bank-transfer path;
- the work or delivery cannot wait for the chosen confirmation threshold.

**Bitcoin is probably the better fit when:**

- the client already holds BTC and wants to use it;
- both sides have counted the complete payment and conversion costs and Bitcoin still wins;
- cross-border processor fees or availability are a real problem for this specific client;
- the issuer values self-custody and final settlement and can wait for confirmation;
- the issuer has a deliberate plan to hold or convert the received BTC.

Being tired of a middleman can motivate the comparison, but it is not enough by itself to decide the payment method.

### 7. What the Bitcoin invoice looks like

- Keep this to one short paragraph: a USD amount, current BTC equivalent, payment address and QR code, pending detection, and confirmed settlement.
- Link the definition to *What Is a Bitcoin Invoice?*
- Link the payer/recipient mechanics to *How to Receive Bitcoin*.
- Do not reproduce either article.

### 8. Close

- One sentence: CryptoZing sends USD-denominated invoices with on-chain payment tracking while the issuer keeps custody.
- Link to *BTCPay Server Alternatives* for readers choosing an invoicing tool.
- Stop there. No generic Bitcoin conclusion and no video promise.

## Cluster Link Plan

### Links from the new article

- *How to Accept Bitcoin Payments as a Freelancer or Small Business* — broader setup, custody, volatility, and bookkeeping context.
- *What Is a Bitcoin Invoice?* — definition and invoice formats.
- *Bitcoin Payment Confirmations Explained: What Pending Means* — settlement detail.
- *How to Receive Bitcoin* — wallet and receiving mechanics.
- *BTCPay Server Alternatives* — tool selection.

### Links into the new article

- Add a contextual decision link from the accepting-Bitcoin guide; this is the primary inbound link.
- Consider a second inbound link from *What Is a Bitcoin Invoice?* or *BTCPay Server Alternatives* where a reader moves from understanding the tool to choosing Bitcoin instead of a traditional invoice payment.

Overlap is intentional only at the decision level. State the fact needed for the comparison, link the deeper article, and move on.

## Source and Accuracy Notes

### PayPal fees

Official U.S. merchant fee table, last updated 2026-07-15:
https://www.paypal.com/us/business/paypal-business-fees

For a $1,000 domestic U.S. invoicing transaction under the published table:

- PayPal Checkout, PayPal Guest Checkout, or Venmo: 3.49% + $0.49 = **$35.39**.
- Standard credit/debit card, Apple Pay, or another third-party wallet: 2.99% + $0.49 = **$30.39**.
- Pay by Bank (ACH): 1%, capped at $10 = **$10.00**.
- An international invoicing transaction adds 1.50 percentage points before any applicable currency-conversion cost.

Recheck the live fee table immediately before publication. The article should date its example rather than presenting it as timeless.

### PayPal holds and protection

- Payment-hold guidance: https://www.paypal.com/us/cshelp/article/why-is-my-payment-on-hold-or-unavailable-help126
- Seller Protection terms: https://www.paypal.com/us/legalhub/paypal/seller-protection

### CryptoZing rate behavior

- [`docs/specs/RATES.md`](../specs/RATES.md): USD is canonical; BTC is recomputed from the current rate on invoice views and QR output.
- [`docs/specs/PARTIAL_PAYMENTS+CONFIRMATIONS.md`](../specs/PARTIAL_PAYMENTS+CONFIRMATIONS.md): each payment keeps the BTC/USD rate captured when it is first detected.

Before linking the existing accepting-Bitcoin guide, correct its statement that the client pays a BTC amount fixed when the invoice is created. That explanation no longer matches the product's live-rate behavior and is too broad as a claim about invoicing tools generally.

## Draft Guardrails

- Do not say Bitcoin payments are free.
- Do not imply PayPal always holds or reverses a payment.
- Do not call PayPal an instant or guaranteed path to spendable fiat.
- Do not call a confirmed Bitcoin payment absolutely irreversible; tie confidence to sufficient confirmation and the absence of a chargeback mechanism.
- Do not claim an overseas client automatically makes Bitcoin cheaper.
- Do not let overlap grow past the fact needed to make the decision.
