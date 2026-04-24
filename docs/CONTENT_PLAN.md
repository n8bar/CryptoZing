# Content Plan

Parallel work track for ongoing article production. Not tied to any single milestone — milestones are checkpoints (see [`PLAN.md`](PLAN.md) conventions).

## Publish strategy

Content and doc changes go straight to `main`. Branches and PRs are reserved for code changes.

## Content workflow

1. **Research** — investigate the topic; document findings in `docs/research/<slug>.md` (not published)
2. **Author** — create `site/staging/<slug>.md` with front matter: `layout: article.njk`, `title`, `description`, `author`, `date`, `canonical: https://cryptozing.app/learn/<slug>/`
3. **Build** — `cd site && npx @11ty/eleventy` (or `--watch`)
4. **Stage** — review locally at `http://<dev-server-LAN>/staging/<slug>/`; push to `main` for a shareable noindexed staging URL
5. **Review** — revise until satisfied; update [`CONTENT_PROMISES.md`](CONTENT_PROMISES.md) with any new promises
6. **Publish** — copy `site/staging/<slug>.md` to `site/learn/<slug>.md`; push to `main`. Staging copy stays as the working copy for future edits.
7. **Verify** — confirm HTTP 200 at `https://cryptozing.app/learn/<slug>/`
8. **Sitemap** — add URL to `site/sitemap.xml` with today's date as `lastmod`
9. **Index** — IndexNow pings automatically on deploy; output URL for Google Search Console → URL Inspection → Request Indexing

## Published

1. [x] Bitcoin pending vs confirmed payments — `/learn/bitcoin-pending-vs-confirmed-payments/`
2. [x] Accepting Bitcoin payments as a freelancer or small business — `/learn/accepting-bitcoin-payments-freelancer-small-business/`
3. [x] BTCPay Server alternatives — `/learn/btcpay-server-alternatives/`

## Queue

_(Ordered by SEO and traffic potential. Pick from the top when starting a new article.)_

4. [x] What is a Bitcoin invoice? — `/learn/what-is-a-bitcoin-invoice/`
5. [ ] Custodial vs noncustodial Bitcoin payments explained
6. [ ] How Bitcoin payment confirmation works
7. [ ] On-chain vs Lightning: what merchants need to know
8. [ ] USD-denominated Bitcoin invoices
9. [ ] Why noncustodial matters for small businesses
10. [ ] Wallet hygiene for business (cross-link from freelancer article's noncustodial section)
11. [ ] xpub-safety (adapted from Helpful Note)
12. [ ] rate-calculation (adapted, optional)
13. [ ] partial-payments (adapted, optional)
14. [ ] invoice-unique-addresses (adapted, optional)
15. [ ] overpayments (adapted, optional)

## Video

- [ ] Scope video content after enough articles exist to inform it.
- [ ] Produce if time allows; explicitly defer and document if not.
