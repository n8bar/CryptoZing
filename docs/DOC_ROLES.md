# Doc Roles

Canonical reference for what each tracked doc is for, and the rules about how they relate. `AGENTS.md` keeps a pointer to this file rather than the full content; read here when navigating docs or deciding where new content belongs.

## Canonical docs (top-level scope authority)

- [`docs/PLAN.md`](PLAN.md) — RC milestone order, status, current focus, and the primary next doc.
- [`docs/PRODUCT_SPEC.md`](PRODUCT_SPEC.md) — global product behavior and invariants.
- [`docs/BACKLOG.md`](BACKLOG.md) — post-MVP and deferred work only.
- [`docs/UX_GUARDRAILS.md`](UX_GUARDRAILS.md) — global UX, accessibility, and interaction rules.

Keep these in sync with every merge or scope change.

## Doc-tree roles

- `docs/PLAN.md` — milestone-level progress only; each milestone should check off once there.
  - If `PLAN.md` has a `Next action`, keep it milestone-level; do not pull phase-level or strategy-detail steps into it.
- `docs/milestones/**` — phase-level execution docs: objective/status summary, phase rollup, current focus, phase-level next actions, phase checkoffs, and milestone exit criteria.
  - If a milestone doc has a current focus or next action, keep it phase-level. It may point to the current phase strategy, but do not pull strategy-level checklist detail into the milestone doc.
- `docs/specs/**` — detailed feature and domain requirements.
- `docs/strategies/**` — ordered implementation checklists, sequencing, and verification steps for one milestone phase. The "do this in this order" docs for active execution.
- `docs/ops/**` — rollout, contributor, and deployment runbooks.
- `docs/qa/**` — findings, test plans, verification notes, and archive material.

## Strategy doc rules

- **Authority**: strategy docs own ordered execution sequencing for an active workstream. They are authoritative for "what do we do next?" and resumption context, but **not canonical for product scope or behavior** — canonical requirements still live in `PLAN.md`, `PRODUCT_SPEC.md`, and the relevant `docs/specs/**` files.
- **Subagent-aware authoring**: even when a workstream has one primary critical path, write strategy docs with subagent use in mind — keep the main ordered sequence explicit, but call out any known safe parallel sidecars or path-scoped tasks so multi-agent execution does not have to improvise.
- **Owner labels**: when assigning work by owner, use `Guided User` for tasks that require user-side account access or clicks but where the user should be coached through unfamiliar tooling; reserve plain `User` for work the user can drive directly without coaching.
- **Lifecycle**: strategy docs may or may not be retired, archived, or folded into milestone/history docs after completion.

## Checklist-depth separation

- `docs/PLAN.md` owns milestone checkoffs.
- `docs/milestones/**` own phase checkoffs.
- `docs/strategies/**` own the ordered checklist for one phase.
- Higher-level docs roll up lower-level completion with a single checkoff instead of duplicating items.
- For any active workstream, keep one obvious checklist owner. If a milestone doc and a strategy doc both exist, the milestone doc summarizes status/objectives while the strategy doc owns the detailed ordered checklist (unless docs explicitly say otherwise).
- Any doc with numbered tasks/milestones/todos is assumed to be done in order unless that doc explicitly says otherwise — flag intentional deviations.

## CHANGELOG conventions

- Keep [`docs/CHANGELOG.log`](CHANGELOG.log) updated alongside canonical docs when scope or doc structure shifts.
- Maintain `CHANGELOG.log` as plain text in chronological order (oldest first); append new entries at the bottom instead of prepending.

## Findings conventions

- Each finding under `docs/qa/Finding*.md` records `Date:` (when reported) near the top, and adds a `Date fixed:` line once resolved with a brief reference to the milestone, PR, or commit that resolved it.
- A finding without a `Date fixed:` line is treated as still open.
