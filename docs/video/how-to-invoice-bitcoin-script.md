# How to Invoice Someone in Bitcoin — Shooting Script

**Format:** Educational walkthrough with talking head + narrated screen recordings
**Cast:** Rachel (teaches) and Nate (learns)
**Target length:** 14 minutes
**Tone:** Informational, conversational, light banter. Not an ad. CZ shown as the concrete example with "we're using ours, but the concepts apply" framing.
**Hosted on:** YouTube

**First-timers note:** Don't memorize this. Know the beats, keep the script nearby, and talk naturally. The dialogue below is what you're trying to say, not what you have to say word-for-word. Multiple takes are normal. The best footage will come from the moments where you forget the script and just talk to each other.

---

## 1. Cold open / hook (~1 min)

**[Talking head — both on camera, casual framing]**

RACHEL: So Nate's been building a Bitcoin invoicing tool for months. He's written articles about it, he talks about it constantly, and he has never once used it to invoice an actual person.

NATE: That's not — I've tested it. A lot. Like, extensively.

RACHEL: Testing doesn't count. Nobody owed you money.

NATE: *(beat)* ...no.

RACHEL: So today we're fixing that. By the end of this video, Nate's going to create a real Bitcoin invoice, send it, and get paid. And if you're watching this, you'll be able to do the same thing.

NATE: It's really not that complicated.

RACHEL: Then this should be easy for you.

**[TITLE CARD: "How to Invoice Someone in Bitcoin" / subtitle: "Invoice in dollar amounts, get paid in Bitcoin"]**

> Pick-up banter if the first take feels stiff:
> - Rachel pulls up the app. Nate: "When did you learn this?" Rachel: "While you were writing unit tests."
> - Rachel: "You built the tool. You wrote four articles about it. And you've invoiced zero people." Nate: "I've invoiced myself." Rachel: "That's called a test."

---

## 2. What you need before you start (~3 min)

**[Talking head — Rachel leads, Nate reacts]**

RACHEL: Before you create an invoice, you need four things. *(counting on fingers)*

First — a Bitcoin wallet. Hardware wallet, mobile wallet, whatever you're already using. If you don't have one yet, that's a whole separate video.

Second — your wallet's extended public key. You'll hear people call it an xpub. This is what lets the invoicing tool generate unique Bitcoin addresses for each invoice without ever touching your private keys. Your keys stay in your wallet. The tool only gets the public side.

NATE: Can I explain how—

RACHEL: Not right now. The point is: you give the tool a public key, the tool creates addresses, your private keys never leave your wallet.

Third — an invoicing tool. There are a few options out there. We're going to use CryptoZing because it's ours, but we think the same concepts apply regardless of what tool you use.

And fourth — you need to know how much you're charging. That sounds obvious but it matters because most tools let you enter the amount in dollars and then calculate the Bitcoin equivalent at current rates. We're invoicing in USD. Bitcoin is the payment method.

NATE: Okay, but — why can't I just text someone my Bitcoin address and say "send me five hundred dollars worth of Bitcoin"?

RACHEL: You can. And here's what happens. You have no record of what the payment was for. If you reuse that address for another client, now two people's payments are going to the same place and you're guessing which is which. You have no way to automatically track whether the right amount showed up. You're basically doing accounting on sticky notes.

NATE: *(beat)* I mean, it works though.

RACHEL: Until it doesn't. It works like mailing cash. Doesn't mean it's a good idea.

> Pick-up banter:
> - Rachel: "That's literally what you did before you built the tool." Nate: "We don't talk about that."
> - Rachel holds up a sticky note. "This is your old invoicing system." Nate: "That's a grocery list." Rachel: "Exactly."
> - Rachel: "Address reuse is a privacy problem." Nate: "For who?" Rachel: "For you. And your client. And anyone watching the blockchain." *(this one's educational — good to include if it flows)*

---

## 3. Creating the invoice (~5 min)

**[TRANSITION: Cut to screen recording. Rachel narrates. Cut back to talking head for the "why" explanations.]**

RACHEL (V.O. — voice-over: you hear Rachel but see the screen): Alright, let's make an invoice. I'm going to walk Nate through this step by step.

**[ON SCREEN: CZ dashboard → New Invoice]**

RACHEL (V.O.): First, the amount. We're entering this in US dollars. The tool is going to convert that to Bitcoin at whatever the current rate is.

**[ON SCREEN: Type in dollar amount. BTC amount populates.]**

**[CUT TO: Talking head]**

RACHEL: This is worth pausing on. Your client owes you five hundred dollars — or whatever the number is. They don't owe you a fixed amount of Bitcoin. The dollar amount is the constant truth. Bitcoin is how they're delivering it.

NATE: What happens to the exchange rate after I send the invoice? Like, what if Bitcoin moves before they pay?

RACHEL: The invoice recalculates. Every time your client opens the invoice, the BTC amount updates to reflect the current rate. So the invoice always asks for the right amount of sats to cover the USD balance right now. The dollar amount doesn't change — that's what you're owed. The Bitcoin amount adjusts so the payer always sends the right value.

NATE: So the rate is never locked in?

RACHEL: Never. USD is the anchor. The Bitcoin just floats with the market until the moment they pay.

**[CUT TO: Screen recording]**

RACHEL (V.O.): Next — the tool generates a unique Bitcoin address for this invoice. Every invoice gets its own address.

**[ON SCREEN: Address field populated, unique to this invoice]**

**[CUT TO: Talking head]**

NATE: Why not just use one address for everything?

RACHEL: Short version — it's messy. If two clients pay to the same address, you're matching payments to invoices manually. With a unique address per invoice, when money shows up at that address, you know exactly which invoice it's for. No guessing.

**[CUT TO: Screen recording]**

RACHEL (V.O.): Now add whatever details make sense — a description of the work, a due date if you want one, line items if you're itemizing. This part works like any invoice you've ever created.

**[ON SCREEN: Fill in description, due date. Click create/save.]**

RACHEL (V.O.): And that's it. Invoice is created. Ready to send.

**[ON SCREEN: Invoice detail view — amount, address, QR code visible]**

> Pick-up banter:
> - Nate: "So the Bitcoin amount just... changes?" Rachel: "Every time they open the invoice. That's the whole point."
> - Nate: "What if Bitcoin crashes right before they pay?" Rachel: "Then they send more sats. You still get your five hundred dollars."
> - Rachel watches Nate fill in the fields. "You designed this screen and you're still reading the labels."
> - Nate: "This feels like it should be more complicated." Rachel: "It really shouldn't."
> - Rachel: "Let's recap. Why not just use one address?" Nate starts a long explanation. Rachel: "Short version." Nate: shrugging, "...it's messy." Rachel: "Because it's messy."

---

## 4. Sending it (~3 min)

**[Screen recording + talking head]**

RACHEL (V.O.): Now you send it. Most tools give you a few options — a shareable link, email, or a QR code. Sometimes all three.

**[ON SCREEN: Show share/send options. Copy link. Show the invoice URL in a browser.]**

RACHEL (V.O.): Let's look at what the payer actually sees when they open this.

**[ON SCREEN: Open invoice link in a clean browser window — the payer's view. Amount in USD, BTC equivalent, Bitcoin address, QR code.]**

**[CUT TO: Talking head]**

RACHEL: That's what your client gets. The amount they owe, the Bitcoin address to send it to, and a QR code if they want to scan instead of copy-paste. Their whole job is: open wallet, scan or paste, send.

### If Leo is in the video:

**[CUT TO: Leo on camera — in person or video call, whatever works]**

NATE: So Leo, I just sent you an invoice. What are you looking at?

LEO: *(opens the link, walks through what he sees)*

RACHEL: Now open your wallet and scan the QR code. That's it — that's the whole payer experience.

**[ON SCREEN: Leo's wallet scanning the QR / sending the payment — screen record if possible]**

### If Leo is not in the video:

NATE: *(switching roles)* Okay, I'm the client now. I got a link. I'm opening it. I see... the amount, an address, and a QR code.

RACHEL: Now you open your wallet, scan the QR, and send it.

NATE: That's it?

RACHEL: That's it.

NATE: *(long pause)* ...really?

RACHEL: Really.

**[ON SCREEN: Wallet app scanning QR code, confirming the send — use Nate's phone if no Leo footage]**

> Pick-up banter:
> - Rachel: "Your whole job as the payer is scan, send, done." Nate: "Three words." Rachel: "You're welcome."
> - Nate inspects the QR code. Rachel: "It's a QR code, not a treasure map."
> - If Leo: Leo squints at the BTC amount. "That's a lot of decimal places." Rachel: "You get used to it."
> - Nate: "So now I just wait for the money." Rachel: "Now you wait for the money." Nate: "This is the best part."

---

## 5. Payment comes in (~4 min)

**[TRANSITION: Back to screen recording — CZ dashboard showing the invoice]**

RACHEL (V.O.): Now we wait. The payment's been sent, and in a few seconds to a couple minutes, it should show up.

**[ON SCREEN: Invoice status changes — payment detected, shows as pending/unconfirmed]**

**[CUT TO: Talking head]**

RACHEL: So the payment just showed up, but it says pending. What does that mean?

Pending means the Bitcoin network has seen the transaction. It's been broadcast. But it hasn't been included in a block yet. Think of it like — the payment is in transit. It exists, you can see it, but it hasn't settled.

NATE: Okay, so when does it settle?

RACHEL: When it gets its first confirmation. That means a miner included the transaction in a block. On Bitcoin, that takes about ten minutes on average. Once it's in a block, it's confirmed.

NATE: And that's when I trust it?

RACHEL: For most invoices, yes. One confirmation is enough. Each additional block after that makes it harder and harder to reverse — but for a freelance invoice, you're not worried about someone trying to reverse a five-hundred-dollar payment. One confirmation, you're good.

NATE: What about bigger amounts?

RACHEL: Bigger amounts, you might wait for three or six confirmations. But that's an edge case for most freelancers and small businesses. We wrote a whole article on this if you want the details — link's in the description.

**[CUT TO: Screen recording]**

RACHEL (V.O.): And there it is — first confirmation.

**[ON SCREEN: Invoice status updates to confirmed. Payment complete.]**

RACHEL (V.O.): Invoice paid. Done.

**[CUT TO: Talking head. Beat. Both look at the camera.]**

NATE: That's it?

RACHEL: That's it.

> Pick-up banter:
> - Nate stares at the pending transaction. "So I just... wait?" Rachel: "You wait. Like everybody waits. It's ten minutes."
> - Nate: "What if it stays pending forever?" Rachel: "It won't." Nate: "But what if—" Rachel: "It won't."
> - Rachel: "Pending is like a check in the mail." Nate: "People still mail checks?" Rachel: "That's not the point."
> - Nate keeps refreshing the page. Rachel: "Watching it doesn't make it confirm faster."

**[PRODUCTION NOTE: The wait between broadcasting and confirmation is 10-60 minutes. Plan to cut away to talking head during the wait, then cut back when it confirms. Alternatively, use a time-lapse or "10 minutes later" card. Don't try to film it in real time.]**

---

## 6. What if something goes wrong? (~2 min) — OPTIONAL, cut if runtime is tight

**[Talking head — both on camera]**

RACHEL: Before we wrap up — a few things that can go wrong. They usually don't, but you should know what to do.

**Underpayment.**

RACHEL: Your client sends less than the invoice amount. Most tools will flag this automatically. You just follow up with the client and ask them to send the remainder.

NATE: Does that happen a lot?

RACHEL: Not really. It's usually a copy-paste mistake or a wallet that subtracted the network fee from the amount instead of adding it on top.

**Overpayment.**

RACHEL: They send too much. It happens. You refund the difference to an address they give you. Don't just keep it.

NATE: What if it's like, a dollar over?

RACHEL: Then you have a conversation with your client about whether they care. Most people don't. But if it's meaningful, refund it.

**Late payment or expired invoice.**

RACHEL: If the invoice had a due date or an expiry and the client missed it, you create a new one. The Bitcoin exchange rate will have changed, so a fresh invoice with a fresh rate is the clean way to handle it.

RACHEL: But honestly — most of the time, none of this happens. The payer scans, sends, and it's done.

NATE: What about double sp—

RACHEL: No.

> Pick-up banter:
> - Nate: "What if they send it from two different wallets?" Rachel: "Then you got two payments. Next question."
> - Nate: "What if someone sends me Bitcoin I didn't ask for?" Rachel: "Then you have a very generous stranger. That's not an invoicing problem."
> - Rachel: "Underpayment, overpayment, late payment. Those are the three." Nate: "What about—" Rachel: "Those are the three."

---

## 7. Wrap-up (~2 min)

**[Talking head — both on camera]**

RACHEL: So let's recap what just happened. We set up the basics — wallet, xpub, invoicing tool. We created an invoice in US dollars, the tool converted it to Bitcoin and generated a unique address. We sent it. The payer scanned, sent, and we watched the payment show up and confirm on-chain.

NATE: That's the whole thing.

RACHEL: That's the whole thing. It's not as complicated as people make it sound.

NATE: If you want to go deeper on any of this — how confirmations work, what it looks like to accept Bitcoin as a freelancer, what "Bitcoin invoice" even means depending on who you ask — we've got written guides on the site. They're all free. Links in the description.

RACHEL: The tool's free too, at least during beta, maybe longer.

NATE: Links in the description for that too.

RACHEL: Thanks for watching.

> Pick-up banter:
> - Rachel: "See? Not that hard." Nate: "I built the tool. I knew it wasn't hard." Rachel: "And yet, first invoice today."
> - Rachel: "Any last questions?" Nate starts to speak. Rachel: quickly cuts him off "No Questions? Happy Invoicing!" Elbows Nate "Please leave any questions in the comments."
> - Nate to camera: "If I can do this, you can do this." Rachel: "You literally built the software." Nate: "...fair."
> - Rachel: "Go read the articles." Nate: "I wrote those." Rachel: "I know. They're pretty good." Nate: genuine surprise "Really?"

---

## Open ideas

- **Real invoice with a real client:** If timing and consent work out, use a real invoice to Nate's primary client (Leo / ANC) instead of a demo. Would need Leo's cooperation for payment timing during the shoot. Not a hard requirement — if it doesn't come together, a demo invoice works fine. Either way, shoot backup screen recordings with test data.

## Production notes

- **Don't try to be perfect.** Stumbles and re-takes are normal. Shoot each section multiple times and pick the best one in editing. The banter will sound better on the third take than the first.
- **Talking head segments:** Natural lighting, simple background, both in frame or single shots depending on who's speaking. Sit or stand somewhere comfortable — you'll be there a while.
- **Screen recordings:** Clean desktop, close unnecessary tabs, browser zoomed in so text is readable. Mouse movements deliberate — don't rush. Viewers need to see where you're clicking.
- **Audio matters more than video.** A well-lit iPhone shot with clean audio beats a DSLR with room echo. If you have a decent mic, use it. If not, record in a quiet room with soft surfaces.
- **Banter:** The scripted lines are starting points. If something funnier or more natural comes out, use that. Keep what works in editing, cut what doesn't.
- **The confirmation wait:** Plan for 10-60 minutes between sending the payment and the first confirmation. Use a cut, time-lapse, or "a few minutes later" card. Don't try to fill dead air.
- **Shoot more than you need.** Expect to capture 40-60 minutes of footage to make a 14-minute video. That ratio is normal.
- **B-roll ideas:** Wallet app on phone, QR code scanning close-up, the invoice loading on a laptop screen, Nate and Rachel looking at the same screen.
- **YouTube description:** Include links to all four articles, link to CZ, and timestamps for each section.
