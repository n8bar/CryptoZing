# CryptoZing — Privacy Policy (DRAFT / WORK IN PROGRESS)

Status: Draft complete and self-reviewed — section walk (item 5) remains.
Self-drafted, not legal advice (per the "no lawyer for RC1" decision in [`../strategies/19.5_LEGAL_LAYER.md`](../strategies/19.5_LEGAL_LAYER.md)).
Placeholders in `[brackets]` resolve at MS21 deploy. Structure adapted from the Automattic privacy policy base (CC BY-SA 4.0); the wallet/blockchain sections are drafted from scratch.

---

**Privacy Policy**
*Effective: `[date]` · Operated by CryptoZing LLC*

*(intro — APPROVED)* CryptoZing is a Bitcoin invoicing service run by CryptoZing LLC. This policy explains what information the Service collects, why, and what happens to it. The short version: we collect what we need to run the Service, we don't sell your information, and we don't run ads or third-party analytics.

**1. Information you give us.** *(APPROVED)*
- *Account*: your name, email address, and password (stored hashed — we can't read it).
- *Business identity*: the billing name, email, phone, address, footer note, and branding you configure — these appear on the invoices and emails you send through the Service.
- *Wallet*: the extended public key (xpub) of each Bitcoin account you connect. Section 3 covers what an xpub reveals.
- *Invoice content*: descriptions, amounts, dates, notes — whatever you type into an invoice.
- *Customer details*: your customers' names, email addresses, and any notes you keep about them. Section 4 covers how we handle them.

**2. Information we collect automatically.** *(APPROVED)*
- *Log and session data*: signing in records your IP address and browser details with your session. Our operational logs record events like email deliveries (recipient address and outcome) and views of public invoice pages (viewer IP address).
- *Cookies*: we set first-party cookies for sign-in sessions and security. There are no advertising, analytics, or cross-site tracking cookies.
- *Blockchain activity*: payment activity at the Bitcoin addresses derived from your xpub, observed from the public blockchain.

**3. What your xpub lets us see.** *(APPROVED)*
An xpub can't move funds, but it derives all of an account's receive addresses. That means anyone holding the xpub — including CryptoZing — can see the connected account's full receive history and balance, not just payments to your invoices. We use it only to provide the Service to you: deriving fresh invoice addresses and reading the account's public blockchain activity to detect and display payments, statuses, and totals. We don't use it for anything else. We store it encrypted, we don't share or sell it, and payment watching sends only individual derived addresses — never the xpub itself — to the blockchain data service (Section 7). Delete your account and the xpub goes with it (Section 8).

**4. Your customers' information.** *(APPROVED)*
You're responsible for having the right to give us your customers' details (`[Terms of Service]`, Section 7). We use them only to provide the Service to you: showing them on your invoices and sending invoice email — delivery, receipts, and payment alerts — on your behalf, with replies directed to you. We don't market to your customers or use their addresses for anything else.

**5. Public invoice pages.** *(APPROVED)*
When you share an invoice link, anyone with the link can see that invoice: its contents and amounts, payment address and status, your business identity, and the customer-facing details you entered. Links use unguessable tokens and tell search engines not to index the page; you can disable or rotate a link at any time, and shared links stop working if you delete your account.

**6. How we use information.** *(APPROVED)*
We use the information above to run the Service: creating, sending, and displaying your invoices; watching for and recording payments; showing your dashboard; providing support when you ask for it; keeping the Service secure and troubleshooting problems; and communicating with you about your account. We don't sell your information, build advertising profiles, or use your data for purposes unrelated to providing the Service.

**7. Third-party services.** *(APPROVED)*
We share information with service providers that help us run CryptoZing — each gets only what it needs to provide its service. For example:
- An email delivery provider (like Mailgun) processes recipient addresses and message content.
- A blockchain data service (like mempool.space) answers payment-watching queries about individual invoice addresses; those queries come from our servers and carry no account identity and no xpub.
- An exchange-rate source (like Coinbase) provides the BTC–USD rate we display; those requests carry no user data at all.
- Hosting and infrastructure providers store and transmit Service data as part of running it.
- Other providers as the Service grows — the same rule applies: each gets only what it needs to provide its service.

We may also disclose information if the law requires it or to protect the Service and its users.

**8. Retention and deletion.** *(APPROVED)*
We keep your information while your account is active. Deleting your account removes your data from the Service — profile, wallet configuration (xpubs), customers, invoices, and payment and delivery records — and your shared invoice links stop working, as described in Section 9 of the `[Terms of Service]`. A few things outlive deletion: emails that were already sent, operational logs and backups that age out on their own rather than being edited per-account, and records we keep as a party to a transaction or are required to retain.

**9. Security.**
Traffic is encrypted in transit (HTTPS), passwords are stored hashed, and xpubs are stored encrypted. Support access to your account exists only while a time-boxed, read-only grant from you is active — and even then it doesn't include your wallet configuration. No online service is fully secure, so keep your own copies of records you can't afford to lose (`[Terms of Service]`, Section 8).

**10. Your choices.**
You can edit your account and business details in settings, manage your connected wallet accounts, disable or rotate any shared invoice link, and delete your account yourself at any time. For anything you can't do from settings — or questions about the information we hold about you — email us (Section 12) and we'll help.

**11. Changes to this policy.**
We may update this policy from time to time. When we do, we'll post the update with a new effective date, and if a change is material we may also notify you through the Service or by email. Continuing to use the Service after we've given notice means the updated policy applies to you.

**12. Contact.**
Privacy questions? Contact CryptoZing LLC at CryptoZingTerms@CyberCreek.us.

*Adapted from the Automattic Privacy Policy ([github.com/Automattic/legalmattic](https://github.com/Automattic/legalmattic)), used under Creative Commons Attribution-ShareAlike 4.0. This Privacy Policy is likewise licensed under CC BY-SA 4.0.*
