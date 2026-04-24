# Research: What Is a Bitcoin Invoice?

_Created: 2026-04-23_

## Scope

Fact-checking for the scam awareness section. The rest of the article covers conceptual Bitcoin/Lightning territory that doesn't require external sourcing beyond protocol documentation.

## PayPal Bitcoin Invoice Scam

**Mechanism:** Scammers use PayPal's actual invoice/money request feature to send official-looking invoices claiming the recipient purchased Bitcoin or cryptocurrency. The invoice includes a phone number urging the recipient to call if they "did not authorize" the transaction.

**What happens if you call:** Scammers pose as PayPal support and attempt to get remote access to the victim's computer, credit card information, or gift card codes. This is a callback phishing / phone-based phishing technique.

**PayPal's guidance:** Don't pay the invoice, don't call any phone number in the invoice note, don't open suspicious URLs. Report to phishing@paypal.com. Log into PayPal directly (not via email links) to check actual transaction history.

**Verification status:** Well-documented. PayPal has an official advisory page. Law enforcement (e.g., Douglas County Sheriff) has issued public warnings. Multiple security outlets have detailed writeups.

**Sources:**
- PayPal official advisory: https://www.paypal.com/us/cshelp/article/what-are-invoice-scams-and-money-request-scams-on-paypal-help1059
- PCRisk detailed breakdown: https://www.pcrisk.com/removal-guides/29855-paypal-crypto-purchase-invoice-email-scam
- MalwareTips writeup: https://malwaretips.com/blogs/paypal-bitcoin-email-scam/
- Douglas County Sheriff warning: https://dcsheriff.net/paypal-bitcoin-purchase-scam/

## Cash App Bitcoin Scams

**Mechanism:** Cash App Bitcoin scams exist but are a different pattern. They involve phishing links via email/text, fake payment notifications, social media testimonials, and social engineering. Cash App does not have a PayPal-style invoice feature, so the "fake invoice" scam pattern does not apply in the same way.

**Decision:** Drop Cash App from the article section or mention it only in passing. The PayPal invoice scam is the specific, well-documented case that matches the article's topic (invoices). Lumping Cash App in misrepresents the scam mechanics.

**Sources:**
- Cash App official guidance: https://cash.app/help/us/en-US/31075-outsmart-bitcoin-scams
- Block (Cash App parent): https://block.xyz/inside/protecting-cash-app-customers-from-bitcoin-scams

## Lightning / Protocol References (for potential sources section)

No formal research needed — these are standard protocol docs:
- BOLT 11 spec (Lightning invoice): https://github.com/lightning/bolts/blob/master/11-payment-encoding.md
- Bitcoin BIPs repository: https://github.com/bitcoin/bips
