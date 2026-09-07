# MS22 - Thorough SEO & Marketing Strategies

Status: Stub — declared 2026-07-17. Scope and schedule to be defined at kickoff (post-MS21).

**Intent:** With the open beta live under `cryptozing.app` (M21), invest deliberately in discovery: a thorough SEO pass across the live app and site surfaces (building on the M15 baseline), plus an ongoing SEO strategy with scheduled tasks to spark engagement and generate backlinks — a one-time pass is an audit, not an SEO practice — and a marketing strategy for reaching the people CryptoZing can serve.

## Phases
_(To be defined at kickoff.)_

## Exit Criteria
_(To be detailed when active.)_

## Handoff from M21

Observations from the open-beta cutover and its verification ([M21.3](../strategies/x21.3_ROLLOUT_VERIFICATION_SIGNOFF.md)):

- Serving path: Laravel owns `/`, `/terms`, `/privacy`; the site container owns `/learn/*`, `/staging/*`, the sitemap, robots, and the IndexNow key. Canonicals, slash redirects, and `www`/http redirects match the Pages-era baseline.
- Analytics: self-hosted Umami at `stats.cryptozing.app` records landing and article views; signed-in, staging, and public invoice pages stay outside it. Website ID and admin credentials are in local storage.
- Indexing: IndexNow notifications now ride the content repo's `publish-vps.sh`; Google briefly held an empty apex answer during the DNS gap on 2026-09-06, so confirm Search Console shows the apex re-crawled.
- Landing metadata (title, description, canonical, og/twitter, JSON-LD) derives from `app.public_url`; the content repo's `site/index.html` is no longer served.

Accepted follow-ups:

- `site.webmanifest` is served as `application/octet-stream` on the VPS path; fix with the next site publish ([cryptozing-site#4](https://github.com/n8bar/cryptozing-site/issues/4)).
- `/index.html` no longer resolves (Pages artifact; Laravel serves `/`). Accepted as gone at M21 close; revisit only if crawl data shows demand.
