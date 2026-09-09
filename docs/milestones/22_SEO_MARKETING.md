# MS22 - Thorough SEO & Marketing Strategies

Status: Draft for review; not approved for implementation.
Drafted: 2026-09-09.
Parent: [PLAN.md](../PLAN.md)

## Outcome

CryptoZing leaves M22 with an evidence-backed post-launch discovery baseline, corrected public-surface SEO gaps, an approved audience/positioning/channel strategy, and a repeatable operating cadence whose first internal review has run. The first bounded marketing experiment is ready for separate activation approval. The milestone measures controllable work and observed results without promising rankings, traffic, backlinks, or adoption that depend on outside systems and people.

## Accepted Baseline

- [M15](x15_CRYPTOZING_APP_SEO_BOOTSTRAP.md) established the DNS-verified Google Search Console domain property, independent Bing verification, sitemap submission, root indexing, canonical/redirect behavior, and the first monitoring baseline. M22 revalidates that state after launch; it does not repeat property ownership or initial submission work.
- [M18](x18_PRERELEASE_CONTENT_SEO.md) established Eleventy, staging and publishing, the Learn hub, the initial article/video set, and multi-page SEO hygiene. [`CONTENT_PLAN.md`](../CONTENT_PLAN.md) remains the canonical article workflow and queue.
- [M21](x21_OB_DEPLOYMENT.md) moved the apex onto the live app without changing the published `/learn/*` URLs. Laravel owns the landing, help, policy, and configured donation surfaces; the content container owns `/learn/*`, `/staging/*`, the sitemap, robots, and the IndexNow key.
- Self-hosted Umami measures the landing and published article pages. Signed-in pages, staging pages, public invoice pages, and generic self-hosted installations remain outside CryptoZing's production analytics configuration.

## Scope Decisions

- Inventory every owned public surface and its job before changing it, including active URLs, route classes, retained recovery/legacy hosts, and public project profiles. Record the applicable audience, serving owner, index/noindex state, sitemap and canonical treatment, analytics boundary, primary call to action, and current disposition.
- Begin with the audiences already supported by the product — freelancers, builders, merchants, and Bitcoin-friendly operators — but use evidence to choose whom CryptoZing should address first. Distinguish hosted open-beta prospects from technical self-hosters; treat invoice recipients as a trust-and-clarity audience, not a search-acquisition surface.
- Keep positioning inside shipped capability and the [`CONTENT_PROMISES.md`](../CONTENT_PROMISES.md) boundaries: USD-first invoicing, watch-only wallet integration, unique invoice addresses, noncustodial settlement, and on-chain tracking. Do not imply Lightning, perpetual zero pricing, perfect attribution under wallet reuse, or other unshipped behavior.
- Revalidate time-sensitive product, competitor, availability, and pricing claims before retaining or redistributing them. Structured data must describe supported facts; do not invent prices, ratings, reviews, authorship, or organizational relationships to qualify for a search presentation.
- Treat search and marketing as one evidence loop without collapsing their roles. Search work owns crawlability, page quality, query alignment, and discoverability; marketing work owns audience, message, channel, offer, and experiment decisions.
- Keep content people-first and original. Earn links through useful material, credible participation, and direct outreach; do not buy ranking links, automate placement, set backlink quotas, or mass-produce query variants.
- Preserve the privacy boundary. Any newly collected analytics event or field — including a first-party aggregate event — requires explicit privacy review and any needed spec/policy update before implementation. Default conversion proxies to already disclosed pageview/referrer evidence and privacy-safe aggregate business counts. Public invoice URLs and identifiers must not enter acquisition analytics, referrer records, outreach material, baselines, crawl exports, finding registers, or surface matrices; audit dynamic/private surfaces by route pattern, never by retaining live tokens or client data.
- Keep publication convergence push-driven. The sitemap remains the complete canonical inventory; IndexNow should react to added, meaningfully updated, redirected, or removed URLs, record outcomes, and redeliver failures rather than bulk-resubmitting unchanged URLs on a schedule.
- Keep article production in the parallel [`CONTENT_PLAN.md`](../CONTENT_PLAN.md) track. M22 may reprioritize that queue, improve internal linking, or identify evidence-backed page work; it does not recreate the CMS/staging decision or impose an arbitrary article quota.
- Keep the CMS-style Help Center in [`BACKLOG.md`](../BACKLOG.md). M22 may audit and improve the current `/help` page's content and discovery signals, but does not absorb the editor, revision history, or data-driven guide build.
- Do not assume paid media, paid placement, testimonials, a mailing list, partnerships, or outbound campaigns. A phase strategy may recommend a bounded experiment, but spending, public posting, or contacting third parties requires explicit approval and any applicable disclosure/consent work.
- File newly discovered defects as GitHub Issues. Filing does not discharge them: each issue must be fixed in M22 or named as exit criteria in a specific future phase or milestone before M22 closes.

## Standards and References

[DOC_ROLES.md](../DOC_ROLES.md), [UX_GUARDRAILS.md](../UX_GUARDRAILS.md), and [CONTENT_PROMISES.md](../CONTENT_PROMISES.md) govern execution and public claims. Current search work should follow Google's [SEO Starter Guide](https://developers.google.com/search/docs/fundamentals/seo-starter-guide), [people-first content guidance](https://developers.google.com/search/docs/fundamentals/creating-helpful-content), [spam policies](https://developers.google.com/search/docs/essentials/spam-policies), and [page-experience guidance](https://developers.google.com/search/docs/appearance/page-experience). Search Console's [URL Inspection](https://support.google.com/webmasters/answer/9012289) and sitemap reports provide Google-side evidence; IndexNow complements, rather than replaces, the canonical sitemap inventory.

## Current Focus

- Active phase: **None — milestone scope is awaiting review.**
- Next action after approval: draft the detailed M22.1 strategy from the approved phase boundary and baseline requirements.

## Phase Rollup

### [ ] Phase 1 — Post-launch discovery baseline and full-surface audit

Reconcile live crawl/index behavior with Search Console, Bing, Umami, referrer, branded-search, and repository evidence; inventory the active and legacy/recovery public surfaces plus intentional exclusions; approve a concrete audit rubric; verify the apex was re-crawled after the M21 cutover; and turn confirmed gaps into a prioritized finding register with accountable dispositions.

### [ ] Phase 2 — Audience, positioning, and channel strategy

Choose the primary audience and hosted/self-hosted message hierarchy, map its problems and search intent to the right destination pages and calls to action, establish consistent branded identity where disambiguation is needed, and approve a ranked set of measurable channel experiments with owners, time/cost bounds, and stop/continue rules.

### [ ] Phase 3 — SEO remediation and content alignment

Resolve the prioritized technical, on-page, structured-data, internal-link, page-experience, crawl/index, and publication-notification findings across the app and content repositories; align affected content with the approved positioning; update content priorities when evidence supports it; and re-run the audit to verify the result.

After Phase 1, Phase 2 and the technical-only portion of Phase 3 may proceed in parallel. Audience-sensitive metadata, copy, or destination-page changes in Phase 3 wait for Phase 2 approval.

### [ ] Phase 4 — Recurring practice and activation handoff

Run the first internal measurement/discovery review, document the repeatable operating playbook, add genuinely time-triggered measurement, content, engagement, and legitimate outreach reviews to [`milestones.ics`](../milestones.ics) with owners, expected outputs, and decision thresholds, and prepare the first low-cost marketing experiment as a ready-to-run brief. Spending, public posting, or contacting third parties remains outside the milestone unless separately authorized.

## Exit Criteria

Detailed ordered work belongs to the phase strategies and rolls up here.

- [ ] A dated baseline records the available Google, Bing, Umami, referrer, and conversion-proxy evidence without credentials, personal data, or tokenized URLs; the M21 apex re-crawl question has a clear verdict or an explicit external-wait owner and recheck date.
- [ ] A surface matrix accounts for each canonical ordinary public URL and each dynamic/private route pattern, including `/help`, the configured donation surface, and retained legacy/recovery hosts, and records the applicable ownership, intended index state, sitemap/canonical treatment, analytics boundary, audience, call to action, and disposition without storing live tokens or client identifiers.
- [ ] Phase 1 approves a repeatable audit rubric covering status and redirect behavior, canonical and sitemap inclusion/exclusion, metadata and applicable structured-data validation, representative mobile/accessibility checks, and explicit performance thresholds or a documented baseline-only treatment.
- [ ] Every intended index surface on `cryptozing.app` passes the approved rubric at its preferred HTTPS apex URL; private, authenticated, staging, and tokenized invoice route classes remain excluded as intended, while third-party profiles and legacy/recovery hosts pass their separately recorded redirect/canonical/noindex disposition.
- [ ] Navigation from a tokenized invoice route cannot place its token, client data, or full private URL into analytics or referrer records on a measured public page.
- [ ] The inherited [`cryptozing-site#4`](https://github.com/n8bar/cryptozing-site/issues/4) manifest MIME defect is fixed and verified. `/index.html` remains absent unless crawl or referrer evidence justifies a different disposition.
- [ ] Every finding is verified fixed or assigned to named future exit criteria with an accepted rationale; no issue is treated as complete merely because it was filed.
- [ ] Publishing maintains an accurate canonical sitemap and sends added, meaningfully updated, redirected, and removed URL states through IndexNow with recorded outcomes and failure redelivery; unchanged URLs are not bulk-resubmitted on a schedule.
- [ ] The audience, positioning, query/destination map, channel priorities, measurement definitions, and bounded experiment plan are approved without contradicting product behavior or content promises.
- [ ] [`CONTENT_PLAN.md`](../CONTENT_PLAN.md) priorities and relevant public copy/internal links reflect the approved evidence where a change is warranted; time-sensitive claims have been revalidated, and no date/content change is made merely to manufacture freshness or keyword variants.
- [ ] At least one low-cost marketing experiment has an approved, ready-to-run brief with its audience, message, channel, owner, time/cost bound, measurement, and stop/continue rule; any separately authorized execution result is recorded without exposing credentials, personal data, or tokenized URLs.
- [ ] A durable operating playbook and at least a 90-day recurring cadence exist for measurement, crawl/index review, content decisions, engagement, and legitimate outreach; the first internal review is complete, and calendar behavior plus milestone schedule consistency are verified locally.
- [ ] The [content promises catalog](../CONTENT_PROMISES.md) has been checked against every public claim changed or introduced by M22, with any new promise recorded and reconciled before closure.

## Handoff from M21

Observations from the open-beta cutover and its verification ([M21.3](../strategies/x21.3_ROLLOUT_VERIFICATION_SIGNOFF.md)):

- Serving path: Laravel owns `/`, `/terms`, `/privacy`, `/help`, and the configured `/donate` route; the site container owns `/learn/*`, `/staging/*`, the sitemap, robots, and the IndexNow key. Canonicals, slash redirects, and `www`/http redirects match the Pages-era baseline.
- Analytics: self-hosted Umami at `stats.cryptozing.app` records landing and article views; signed-in, staging, and public invoice pages stay outside it. Website ID and admin credentials are in local storage.
- Indexing: IndexNow notifications now ride the content repo's `publish-vps.sh`; Google briefly held an empty apex answer during the DNS gap on 2026-09-06, so confirm Search Console shows the apex re-crawled.
- Landing metadata (title, description, canonical, og/twitter, JSON-LD) derives from `app.public_url`; the content repo's former landing page is no longer served.

Accepted follow-ups:

- `site.webmanifest` is still served as `application/octet-stream`; fix and verify it with the next site release ([cryptozing-site#4](https://github.com/n8bar/cryptozing-site/issues/4)).
- `/index.html` no longer resolves (Pages artifact; Laravel serves `/`). Accepted as gone at M21 close; revisit only if crawl or referrer data shows demand.
