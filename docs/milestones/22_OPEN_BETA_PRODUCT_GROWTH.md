# MS22 - Open Beta Product & Growth Iteration

Status: Revised draft for review; product-and-growth direction confirmed, exact phase scope not approved for implementation.
Drafted: 2026-09-09.
Parent: [PLAN.md](../PLAN.md)

## Outcome

CryptoZing leaves M22 with an evidence-backed post-launch discovery baseline, corrected public-surface SEO gaps, an approved audience/positioning/channel strategy, and a repeatable operating cadence whose first internal review has run. It then uses the later portion of the milestone for deliberate product expansion: invoice line items plus a bounded set of other backlog features are specified, built on dev, thoroughly verified, and released to production with live validation. The first bounded marketing experiment is ready for separate activation approval. Growth work measures controllable activity and observed results without promising rankings, traffic, backlinks, or adoption that depend on outside systems and people.

## Accepted Baseline

- [M15](x15_CRYPTOZING_APP_SEO_BOOTSTRAP.md) established the DNS-verified Google Search Console domain property, independent Bing verification, sitemap submission, root indexing, canonical/redirect behavior, and the first monitoring baseline. M22 revalidates that state after launch; it does not repeat property ownership or initial submission work.
- [M18](x18_PRERELEASE_CONTENT_SEO.md) established Eleventy, staging and publishing, the Learn hub, the initial article/video set, and multi-page SEO hygiene. [`CONTENT_PLAN.md`](../CONTENT_PLAN.md) remains the canonical article workflow and queue.
- [M21](x21_OB_DEPLOYMENT.md) moved the apex onto the live app without changing the published `/learn/*` URLs. Laravel owns the landing, help, policy, and configured donation surfaces; the content container owns `/learn/*`, `/staging/*`, the sitemap, robots, and the IndexNow key.
- Self-hosted Umami measures the landing and published article pages. Signed-in pages, staging pages, public invoice pages, and generic self-hosted installations remain outside CryptoZing's production analytics configuration.
- [`PRODUCT_SPEC.md`](../PRODUCT_SPEC.md) remains authoritative for global product behavior; feature specs own detailed requirements. [`BACKLOG.md`](../BACKLOG.md) is the candidate pool, not blanket authorization to pull every deferred feature into M22.

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
- Keep product expansion late in the milestone. Phases 1–4 establish and operationalize the growth baseline before Phase 5 selects the implementation bundle.
- Treat invoice line items as required M22 scope. The current backlog entry supplies the starting intent — multiple description/quantity/rate rows, optional subtotal/tax/discount lines, USD-canonical totals, in-person cash use, and consistent invoice/public/print/mail surfaces — but it does not replace an approved feature spec.
- Select any additional backlog features in Phase 5 from observed open-beta needs, dependencies, risk, user value, and remaining capacity. Record the exact bundle before implementation; unselected backlog items remain deferred.
- Follow the spec-first boundary for every selected feature: approve the canonical requirement or spec delta, then write its implementation strategy, then change code. Discovery from current code may inform a spec only when explicitly agreed.
- Implement selected features on dev through their normal code branches and PRs. Migrations, automated tests, browser/UX verification, regression coverage, and the full Sail suite must pass before a production release is proposed.
- Release to production only after the dev verdict is recorded and the user approves the rollout. Each release must follow the one-shot production access and environment-readback rules, include an applicable migration/backout plan, preserve watch-only and data boundaries, and finish with live functional and service-health verification.
- Reassess the milestone schedule when Phase 5 fixes the feature bundle. If the approved work cannot fit the remaining window, narrow the bundle or update [`milestones.ics`](../milestones.ics) and [`PLAN.md`](../PLAN.md) before feature implementation begins.

## Standards and References

[DOC_ROLES.md](../DOC_ROLES.md), [PRODUCT_SPEC.md](../PRODUCT_SPEC.md), [UX_GUARDRAILS.md](../UX_GUARDRAILS.md), [CONTENT_PROMISES.md](../CONTENT_PROMISES.md), and [RUNNING_THE_SERVER.md](../ops/RUNNING_THE_SERVER.md) govern execution, feature behavior, public claims, and rollout safety. Current search work should follow Google's [SEO Starter Guide](https://developers.google.com/search/docs/fundamentals/seo-starter-guide), [people-first content guidance](https://developers.google.com/search/docs/fundamentals/creating-helpful-content), [spam policies](https://developers.google.com/search/docs/essentials/spam-policies), and [page-experience guidance](https://developers.google.com/search/docs/appearance/page-experience). Search Console's [URL Inspection](https://support.google.com/webmasters/answer/9012289) and sitemap reports provide Google-side evidence; IndexNow complements, rather than replaces, the canonical sitemap inventory.

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

### [ ] Phase 5 — Product backlog selection and feature specifications

Lock invoice line items into the implementation bundle, select only the additional backlog features justified by open-beta evidence and remaining capacity, approve the canonical feature requirements and acceptance boundaries, identify dependencies and rollout risk, and reforecast the milestone before code begins if the selected bundle cannot fit the current schedule.

### [ ] Phase 6 — Dev implementation and thorough verification

Implement each selected feature on dev from its approved spec and strategy, including migrations and cross-surface behavior; run targeted, regression, full Sail, browser, mobile, accessibility, and applicable operational tests; and resolve or explicitly disposition every finding before proposing a production release.

Independent selected features may use separate path-scoped workstreams after their specs are approved. Invoice line items remain on the primary integration path because they affect invoice creation/editing, totals, settlement presentation, public/print output, mail, and future receipt behavior.

### [ ] Phase 7 — Controlled production rollout and live validation

Release only the dev-verified feature set through the approved production procedure, apply and verify migrations safely, smoke the affected issuer/client/public flows, recheck any discovery or content-promise surface changed by the release, confirm service health and background processing, and record the live verdict plus any backout or follow-up disposition.

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
- [ ] Phase 5 names the complete M22 product bundle: invoice line items are included, every additional feature is explicitly selected or left in the backlog, dependencies and risks are recorded, and the milestone schedule is confirmed or updated before implementation.
- [ ] Invoice line items and every additional selected feature have approved canonical requirements and phase strategies before their code work starts.
- [ ] Invoice line items satisfy their approved behavior across data storage and migration, calculation, create/edit, issuer, public/print, mail, manual/in-person settlement, and regression surfaces while preserving USD as the canonical invoice total.
- [ ] Every selected feature passes its targeted and regression tests, the full Sail suite, applicable browser/mobile/accessibility review, and a recorded dev acceptance verdict with no unresolved release-blocking finding.
- [ ] The selected feature set is deployed only after rollout approval, then passes migration verification, live functional smoke coverage, public/content-promises regression checks where affected, service-health checks, and a recorded production verdict with an exercised or still-valid backout path.
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
