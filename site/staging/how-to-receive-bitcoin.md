---
layout: article.njk
title: "How to Receive Bitcoin"
subtitle: "Share an Address. Get Paid."
description: "Receiving bitcoin takes one thing: an address. What a Bitcoin address is, how to get one, how to share it, and how to know when you've been paid."
canonical: "https://cryptozing.app/learn/how-to-receive-bitcoin/"
author: "Nate Barlow, CryptoZing"
date: 2026-07-30
---

Here is a secret hiding in plain sight: receiving bitcoin is simple. You need exactly one thing: an address.

No exchange account required. No money up front. If you can share a link, you can get paid in bitcoin. Let's walk through it.

## What is a Bitcoin address?

A Bitcoin address is a string of letters and numbers that tells the network where a payment should land. Most start with `1`, `3`, or `bc1`, and they look something like this:

`bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh`

Nobody memorizes these, and nobody types them by hand. They get copied, pasted, and scanned.

The important thing to understand is what an address is not: it is not a secret. Sharing an address lets someone send bitcoin to you. It does not let anyone take bitcoin from you. The thing that spends your bitcoin, usually a seed phrase or private key, lives in your wallet and stays there. An address is closer to an email address than a password: made for handing out.

One caveat worth knowing early: Bitcoin's ledger is public. Anyone who has your address can look up the payments made to it. That doesn't expose your name, but it is a reason to hand out fresh addresses rather than reusing one, and we'll come back to it.

## How to get a Bitcoin address

You need a wallet. Any wallet.

A wallet is an app that generates addresses for you and holds the keys that spend whatever arrives at them. Getting one takes a few minutes:

1. **Install a wallet.** A software wallet on your phone or computer is the usual starting point. Hardware wallets add security for larger amounts. Custodial services, such as exchanges, will also give you a deposit address, though there the service holds the keys, not you.
2. **Write down the seed phrase it gives you.** On paper, kept somewhere safe. This is the part people skip and later wish they hadn't. The seed phrase is the wallet; the app is just a window into it.
3. **Tap Receive.** The wallet shows you an address and a QR code. That's it. You now have somewhere to be paid.

Notice what wasn't on that list: buying bitcoin, linking a bank account, or verifying your identity with anyone. Receiving requires none of it.

## Sharing your address

Two good ways, one bad way.

**QR code.** If the sender is standing in front of you or on a video call, show the QR code from your wallet's Receive screen. Their wallet scans it and fills everything in.

**Copy and paste.** For email or chat, copy the address from your wallet and paste it whole. Before the sender pays, it's good practice for both of you to compare the first and last several characters of what they're about to send to.

**Typing it out by hand.** Don't. A single wrong character means the payment goes nowhere recoverable. Every wallet has a copy button for a reason.

And a habit worth building from day one: use a fresh address for each payment. Your wallet does this for you; every time you tap Receive, it can show a new address, and every address it generates stays yours. Old ones keep working. Fresh addresses keep your payment history from being connected together by anyone you've ever handed an address to, and they make it obvious which payment came from whom.

## How do you know when you've been paid?

Moments after the sender pays, the payment shows up in your wallet as pending or unconfirmed. It is real, visible, and on its way, but not settled yet.

Bitcoin settles in batches called blocks, roughly every ten minutes. Each new block adds a confirmation to your payment. For small everyday amounts, one confirmation is commonly treated as enough; for larger payments, waiting for a few is the norm.

The distinction matters more than most beginner guides let on, so we gave it its own article: [Bitcoin Payment Confirmations Explained: What Pending Means](/learn/bitcoin-pending-vs-confirmed-payments/).

## Common worries, answered quickly

**Is it safe to share my address?** Yes. An address can only receive. The trade-off is privacy, not security: whoever has the address can see payments made to it on the public ledger. Fresh addresses per payment keep that view narrow.

**Do I pay a fee to receive bitcoin?** On the Bitcoin network, the sender pays the transaction fee. Receiving costs you nothing at the protocol level. If you receive into a custodial service, it may have fees of its own; one more reason a wallet you control is the better default.

**What if the sender pays the wrong amount?** It happens, usually because their exchange or wallet deducted a fee from the amount instead of adding it on top. The bitcoin still arrives and is still yours; the fix is a conversation with the sender about the difference, not a support ticket. Knowing exactly what arrived, and when, is where good records earn their keep.

## Receiving regularly? Do future-you a favor

Everything above covers getting paid once. If bitcoin is becoming income, a freelance gig, a side business, a client who prefers paying in bitcoin, two habits move from nice-to-have to necessary.

First, per-payment addresses stop being just a privacy nicety and become bookkeeping: one address per payment means every incoming amount is attached to a who and a why. Second, records. The dollar value of bitcoin at the moment you receive it is what tax authorities generally care about, and reconstructing that months later is nobody's idea of a good afternoon.

This is the point where invoicing tools enter the picture; they generate a fresh address per invoice, watch for the payment, and keep the records as a side effect. We covered what those look like in [What Is a Bitcoin Invoice?](/learn/what-is-a-bitcoin-invoice/) and how freelancers and small businesses put them to work in [How to Accept Bitcoin Payments as a Freelancer or Small Business](/learn/accepting-bitcoin-payments-freelancer-small-business/).

But that's the regular-income chapter. For today: install a wallet, back up the seed phrase, tap Receive. Share an address, get paid.
