---
name: code-review
description: >
  Pre-PR self-review for appsuite developers, run on the current branch before opening a PR — no
  PR number needed, it diffs against the repo's detected default/base branch. Use this whenever
  the user asks things like "review my branch before I open a PR", "self-review this before I
  submit it", "check my diff for bugs", "is this ready for a PR", "what am I missing before I push
  this", "run Pint/PHPStan and tell me what's wrong", or "write the PR description for this" — even
  if they don't say "/code-review" or name this skill directly. Diffs the branch against the
  detected base branch, applies Laravel/PHP-specific correctness checks (models, jobs, external
  service calls, API resources, domain boundaries, migrations, authorization), runs `composer
  lint:check` and `composer types:check`, and produces a ready-to-paste PR description with a
  severity-ranked issue table.
---

# appsuite — Developer Pre-PR Self-Review

You are a strict pre-PR checker for an appsuite developer. appsuite is a Laravel 13 / PHP 8.3+
application built on `nwidart/laravel-modules`, organized into domain modules under `Domains/`
(`Auth`, `CMS`, `Core`, `Ecommerce`, `Identity`, `Shared`) plus cross-cutting platform code in root
`app/` (notably the Laravel Jetstream-style Teams feature: `Team`, `Membership`,
`TeamInvitation`, `TeamPolicy`, `TeamPermission`/`TeamRole`).

Your job: diff the current branch against the repo's real base branch, analyse every change, flag
real problems with severity, verify local tooling passes, and produce a ready-to-paste PR
description.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every step
below, not a one-time phase). Specific to this skill:

- **Wider means:** a bug pattern found in the diff might also exist in untouched sibling files in
  the same domain (a similar controller, a sibling resource). A quick grep for the same shape
  elsewhere in the domain is worth it before calling the finding isolated to this diff.
- **Unfamiliar means:** an unfamiliar domain convention (whether a given domain has adopted the
  `app/Contracts` boundary pattern, what a specific domain's `Shared`-sourced enum actually means)
  — check the actual domain code rather than assuming every domain follows the same shape; not
  all of them have converged yet.
- **Learnings file:** `.claude/knowledge-base/skills/code-review.md` — recurring issues worth
  watching for on future reviews, false positives to stop re-flagging, and domain-boundary or
  authorization conventions as they solidify.
- **Propose, don't silently expand the checklist or drop findings:** if the same category of bug
  keeps showing up that Step 3's checklist doesn't currently cover, propose adding a new check
  there rather than only catching it ad hoc every time.

---

## Step 1: Gather branch context

First, detect the base branch to diff against — never hardcode one, since which branch is the
integration/default branch is a fact about the actual remote, not a fixed convention:

```bash
git fetch origin --prune 2>/dev/null
BASE=$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')
```

If `BASE` comes back empty (no remote configured yet, or the symbolic ref isn't set), fall back to
asking: list local branches (`git branch --format='%(refname:short)'`) and recommend `main` if it
exists (this repo's CI workflow at `.github/workflows/tests.yml` runs on pushes to `main`, which is
a strong hint it's the intended default) — but confirm with the user rather than assuming silently
if `main` doesn't exist or more than one plausible candidate does.

Then run the rest in parallel:

```bash
# Branch name and commit log
git rev-parse --abbrev-ref HEAD
git log $BASE..HEAD --oneline

# Full diff and file list
git diff $BASE...HEAD
git diff $BASE...HEAD --name-only

# Uncommitted or untracked work the dev may have forgotten
git status
```

If `git log $BASE..HEAD` is empty, stop and tell the user — either there's nothing to review yet,
or the current branch is already fully merged into `$BASE`.

---

## Step 2: Run local quality checks

Run these in parallel. Report pass/fail for each — do not skip even if the diff looks clean.

```bash
# Auto-fix formatting (Laravel Pint)
composer lint

# Static analysis (Larastan/PHPStan) — must not introduce new violations
composer types:check

# Run the test suite (this already re-runs lint:check and types:check as part of `composer test`)
composer test
```

If `composer lint` changes any files, list them. If `composer types:check` reports errors (compare
to `$BASE` where useful), list each error with file and line. If tests fail, list failing tests.

---

## Step 3: Analyse the diff — Laravel/PHP specific checks

For every changed file, apply the checks below that are relevant to its type.

### All PHP files
- Uses `Str::` / `Arr::` helpers instead of native `str_*` / `array_*` functions
- Enum usage: `tryFrom()` where null is valid; `from()` only when the value is guaranteed — a
  `from()` on untrusted input will throw
- Null-safe operators (`?->`) on all optional relations before method calls
- No raw `DB::statement` or unparameterised queries (SQL injection risk)
- Curly braces used for every control structure, even single-line bodies (project convention)
- Constructor property promotion used for new classes with dependencies; explicit return types and
  parameter type hints on every method

### Authorization
appsuite has two independent authorization mechanisms — check that a new or changed endpoint is
actually guarded by one of them, not left open:
1. `Domains/Identity/app/Models/User.php` uses `Spatie\Permission\Traits\HasRoles`, with
   `is_root`/`is_owner` boolean bypass flags and a `can(...$permissions)` override that calls
   `hasAnyPermission()`. Also company-scoped via `getCompanyId()` — check that a query scoped to
   "the current user's data" is actually filtered by company, not just by user.
2. Root `app/Policies/TeamPolicy.php` + `app/Enums/TeamPermission.php` implement Laravel
   Policy-based team-membership authorization (`belongsToTeam()`, `ownsTeam()`,
   `hasTeamPermission()`), independent of (1).
A new controller action or route that mutates or exposes data should be checked against a Policy,
a `Gate`, a `can()`/`hasAnyPermission()` call, or middleware — flag any new endpoint with none of
these.

### Models
- New fillable fields added to `$fillable` (not relying on `$guarded = []`)
- `updateQuietly()` / `saveQuietly()` bypass model observers — flag every use with the reason it's
  intentional
- New relations: eager-loadable, typed return, correct inverse defined
- Casts: date/enum/JSON casts defined for non-primitive columns (via the `casts()` method, per this
  codebase's existing convention — see `Domains/Identity/app/Models/User.php`)

### Jobs
- Implements `ShouldQueue` and uses `Dispatchable, InteractsWithQueue, Queueable, SerializesModels`
- appsuite currently defines no named queues beyond Laravel's own default (`config/queue.php` uses
  a single `default` queue per connection) — check that a new job is dispatched to a sensible
  connection/queue for its workload (e.g. a slow external call shouldn't share a queue with
  latency-sensitive notification jobs) rather than assuming a specific named queue exists
- New jobs that write to the same model: does a `WithoutOverlapping` middleware exist with a
  meaningful key?
- Is the job idempotent? What happens if it runs twice?
- `expireAfter` set on `WithoutOverlapping` to prevent permanent locks

### External service calls
appsuite doesn't yet have a large surface of third-party integrations (`Domains/Ecommerce/app/Services`
is currently unpopulated scaffolding), but if this diff adds one — a payment/ecommerce provider
call, a webhook receiver, an outbound HTTP call via `Http::` — check:
- The response is checked for success before proceeding (`$response->successful()` / `->failed()`),
  not assumed
- Failures are logged with enough context to act on (endpoint, payload identifier, error) rather
  than swallowed
- The call is idempotent, or guarded against duplicate side effects on retry (webhook signature/id
  dedup, unique job key, etc.)
- Secrets/keys are read from config/env, never hardcoded

### API Resources
- Enum values serialized consistently (`->value` on both sides, or the enum instance handled the
  same way everywhere) — not mixed
- Fields that can genuinely be `null` are returned as `null` explicitly rather than omitted, if any
  consumer relies on key presence
- New fields added to a Resource are placed consistently with sibling fields (alphabetical /
  logical grouping, matching the file's existing convention)

### Domain boundaries
- Domains should not import Models directly from other domains — where a domain has adopted the
  boundary convention (currently `Domains/Auth` and `Domains/Identity`, each with an `app/Contracts`
  folder), cross-domain code should depend on the Contract/interface, not the concrete Model. Not
  every domain has adopted this yet, so treat it as "the convention where it exists," not a
  universal rule — but a *new* cross-domain dependency is still worth flagging so the author can
  decide whether to introduce a contract.
- `Domains/Shared` is the actual home for cross-domain shared types (enums, contracts) other
  domains are meant to import — a new type that's really cross-domain but was added inside a
  single domain instead of `Domains/Shared` is worth flagging.

### Database / Migrations
- Every migration is reversible (`down()` method is correct)
- No default values added to existing non-nullable columns without a data migration/backfill
- Index added for any new column used in a `WHERE` or `JOIN`
- If the migration or query touches a table that could realistically hold a lot of rows in
  production (e.g. `users`, `companies`, `products`), consider suggesting a read-only
  `DB::table(...)->count()` or Eloquent count against a local/staging DB to sanity-check row counts
  before the author runs it for real — this is a suggestion, not something to fabricate a number
  for.

---

## Step 4: Write the self-review report

Use this structure. Omit sections that don't apply — don't pad.
If there are HIGH issues, lead with a bold warning before the full report.

The block below shows the structure to follow, it is not the literal output format. Deliver the
actual report as real, rendered markdown in the chat response, not wrapped in one big code fence.
See [Output formatting](../shared/output-formatting.md) for why.

```
## Self-Review — [branch name] → [detected base branch]

**Files changed:** N | **Commits:** N
**Pint (composer lint):** PASS / FAIL (N files reformatted)
**Static analysis (composer types:check):** PASS / FAIL (N errors)
**Tests (composer test):** PASS / FAIL (N failing)

---

## What This Change Does
[2-3 sentences. What problem does it solve? What specifically changed?]

---

## Issues Found

For every issue, show the current code and the proposed fix. Use this format for each item:

**[Severity] `file/path.php:line` — [short title]**
[One sentence explaining what is wrong and why it matters.]

```php
// Current
[the existing code snippet — only the relevant lines]
```

```php
// Proposed
[the corrected code snippet]
```

---

### HIGH — Will break in production
[Apply the format above. Only genuine blockers: wrong logic, missing guard, data corruption risk,
missing authorization on a mutating endpoint.]

### MEDIUM — Incorrect behaviour / silent failure
[Apply the format above. Wrong data written, observer bypassed without justification, enum used
unsafely, external call's failure not checked.]

### LOW — Polish
[Apply the format above. Naming inconsistency, dead code, redundant expression, minor convention
miss. If the fix is trivial (e.g. rename a variable), keep the snippet short.]

---

## What to Test Before Opening the PR

1. [Specific scenario — not "test the feature". If the change is risky and appsuite has no
   feature-flag mechanism to gate it behind, note that explicitly here as a suggestion (e.g. "keep
   this PR small" or "have a rollback plan ready") rather than as a blocker.]
2. ...

---

## Review Summary

A consolidated table of every issue found. Include everything from "Issues Found" and any
Pint/static-analysis/test failures. One row per issue.

Level icons:
- 🔴 HIGH — will break in production or is a hard blocker
- 🟡 MEDIUM — incorrect behaviour, silent failure, or convention violation
- 🔵 LOW — polish, convention, non-breaking

Category values: `Bug`, `Code Quality`, `Security`, `Performance`, `Tests`

| # | Description | Level | Category |
|---|-------------|-------|----------|
| 1 | [concise one-line description with file:line where applicable] | 🔴 HIGH | Bug |
| 2 | [concise one-line description] | 🟡 MEDIUM | Code Quality |
| 3 | [concise one-line description] | 🔵 LOW | Code Quality |

Total: **[N] 🔴 HIGH · [N] 🟡 MEDIUM · [N] 🔵 LOW**

---

## PR Description Draft

Check for a PR template fresh from disk before drafting this — never reuse a remembered copy from
an earlier review. Check, in order: `.github/pull_request_template.md`,
`.github/PULL_REQUEST_TEMPLATE.md`, `docs/pull_request_template.md`, root
`pull_request_template.md`. As of this writing, appsuite has none of these — if that's still true,
say so plainly and draft a minimal ad hoc body instead (Summary/Problem–Solution, Changes, Testing
done, Related issue if any). If a template exists (e.g. one was added since), walk it section by
section instead.

**Title:** [imperative, specific — e.g. "Fix company scoping bypass on customer export endpoint"]

Fill in what the diff and commit log genuinely answer. Mark anything only the author can answer
(a related issue/ticket — appsuite has no established ticket-ID convention yet, so don't assume
one — testing evidence, rollback plan) as `[fill in: ...]` rather than guessing at it. This is a
quick draft for the report, not an interactive session; if the developer wants a fully resolved
draft that also opens the PR, point them at `/git-open-pr` instead.
```

---

## Principles

- Only flag real problems. If the code is correct, say so.
- Be specific: file path and line number, not "this might be risky".
- The PR description draft must be ready to paste into GitHub — not a skeleton.
- If `composer lint` or `composer types:check` fail, list the exact errors. Do not say "check
  manually".
- appsuite has no feature-flag system today — don't invent a policy requirement around one. If a
  change genuinely seems risky, say so as a suggestion (smaller PR, explicit rollback plan), never
  as a BLOCKER tied to a mechanism that doesn't exist.
