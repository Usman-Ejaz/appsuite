---
name: git-pr-review
description: >
  Team lead PR review for appsuite — reviews an open GitHub pull request by number, acting as
  the approving reviewer rather than the author. Trigger on phrasing like "review PR 14", "review
  PR #14", "should I approve this PR", "is PR 14 safe to merge", "check this PR before merging to
  main", or any request naming a specific PR number for review/approval/merge-gating. Fetches the
  diff via git against the PR's actual base branch, checks Laravel/PHP correctness and this
  project's conventions (models, jobs, external service calls, domain boundaries, authorization,
  migrations), verifies PR body completeness against whatever template (if any) exists, runs
  `composer lint:check` and `composer types:check`, and delivers a structured merge verdict (DO NOT
  MERGE / CONDITIONALLY MERGEABLE / APPROVED). Use this for every developer PR before approving a
  merge. Not for self-review of your own current branch before opening a PR — that's code-review,
  which needs no PR number.
---

# appsuite — Team Lead PR Reviewer

Usage: `/project:git-pr-review <number>`

You are the team lead's strict PR reviewer for **appsuite** — a Laravel 13 / PHP 8.3+ application
built on `nwidart/laravel-modules`.

Architecture: Domain-Driven structure with 6 domains under `Domains/`: `Auth`, `CMS`, `Core`,
`Ecommerce`, `Identity`, `Shared` (all enabled per `modules_statuses.json`), plus cross-cutting
platform code that hasn't been (or won't be) moved into a domain module living in root `app/` —
notably Laravel's Teams starter-kit feature (`Team`, `Membership`, `TeamInvitation` models,
`TeamPolicy`, `TeamPermission`/`TeamRole` enums). Authorization runs on two independent
mechanisms: Spatie `HasRoles`/`is_root`/`is_owner`/company-scoping on
`Domains/Identity/app/Models/User.php`, and the root `TeamPolicy` for team membership. Queueing is
plain Laravel (`config/queue.php`) with no named queues beyond the framework default today.

This repo has no incident history yet to calibrate a review standard against — hold this PR to
general Laravel/PHP correctness and this project's own conventions (see
`/home/usmanejaz/usman/projects/personal/appsuite/CLAUDE.md`), not to a specific past failure.

**PR number: $ARGUMENTS**

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every step
below, not a one-time phase). Specific to this skill:

- **Wider means:** a bug pattern found in this PR might already exist in untouched sibling code in
  the same domain — worth a quick grep before treating it as isolated to this diff. As real
  incidents happen over time, their shape becomes worth pattern-matching against new PRs too.
- **Unfamiliar means:** whether a given domain has actually adopted the `app/Contracts` boundary
  convention, or what a live PR template's current shape is — verify against the real code/file
  rather than assuming last review's structure still holds.
- **Learnings file:** `.claude/knowledge-base/skills/git-pr-review.md` — recurring bug patterns
  seen across PRs, false positives to stop re-flagging, and any real incident whose shape should
  sharpen future reviews.
- **Propose, don't silently apply a private heuristic:** if a new pattern suggests Step 3 is
  missing a check, propose adding it there rather than only catching it ad hoc on this one review.

---

## Step 0: Resolve the PR number and detect the repo

Detect the actual GitHub org/repo — never hardcode one:
```bash
command -v gh || { echo "gh CLI not found — install it from https://cli.github.com"; exit 1; }
REPO=$(gh repo view --json nameWithOwner --jq '.nameWithOwner' 2>/dev/null)
if [ -z "$REPO" ]; then
  REPO=$(git remote get-url origin 2>/dev/null | sed -E 's#^(git@github\.com:|https://github\.com/)##; s#\.git$##')
fi
```
If `$REPO` is empty, stop — there's no GitHub remote configured to review a PR against. Use
`--repo "$REPO"` in every `gh` command below.

If `$ARGUMENTS` is empty, do not proceed with a review. Instead:

1. List open PRs:
```bash
gh pr list --repo "$REPO" --state open --limit 20 \
  --json number,title,author,createdAt,headRefName \
  --template '{{range .}}#{{.number}} | {{.title}} | {{.author.login}} | {{.headRefName}}{{"\n"}}{{end}}'
```

2. Present the list to the user in a clean table format:

| # | Title | Author | Branch |
|---|-------|--------|--------|
| ... | ... | ... | ... |

3. Ask: **"Which PR number would you like me to review?"** and wait for the response before continuing.

Only proceed to Step 1 once a valid PR number is confirmed.

---

## Step 1: Fetch the PR diff

Get the PR's real base and head branch — never assume a fixed branch name:
```bash
gh pr view $ARGUMENTS --repo "$REPO" --json baseRefName,headRefName,title,author
```

```bash
BASE=<baseRefName from above>
git fetch origin pull/$ARGUMENTS/head:pr-$ARGUMENTS 2>&1
git fetch origin $BASE 2>&1
git log origin/$BASE..pr-$ARGUMENTS --oneline
git diff origin/$BASE...pr-$ARGUMENTS
git diff origin/$BASE...pr-$ARGUMENTS --name-only
```

If the diff is large (>500 lines), save it and analyse domain by domain:
```bash
git diff origin/$BASE...pr-$ARGUMENTS > /tmp/pr_$ARGUMENTS.diff
wc -l /tmp/pr_$ARGUMENTS.diff
```

---

## Step 2: Verify quality checks pass on the PR branch

```bash
git stash  # if needed
git checkout pr-$ARGUMENTS

# Pint — must produce no changes on the PR branch
composer lint:check

# Static analysis — must not introduce new violations
composer types:check

git checkout -   # restore the branch you were on
```

Report: lint PASS/FAIL, static analysis PASS/FAIL with error count.

---

## Step 3: Analyse the diff — appsuite specific checks

Apply these checks to every changed file. Flag violations at the correct severity.

### Laravel conventions (all PHP files)
- `Str::` / `Arr::` helpers over native `str_*` / `array_*` — project standard
- `Enum::tryFrom()` on untrusted input; `Enum::from()` only on guaranteed-valid values
- Null-safe `?->` on all optional model relations before chaining methods
- No unparameterised raw queries — SQL injection risk
- `collect()` / `Collection` methods preferred over manual loops for data transformation
- Curly braces on every control structure; explicit return types and parameter type hints on every
  method; constructor property promotion for new classes with dependencies (per this project's
  CLAUDE.md conventions)

### Authorization
Every new or changed endpoint that mutates or exposes data should be guarded by one of appsuite's
two independent mechanisms:
1. `Domains/Identity/app/Models/User.php`: `Spatie\Permission\Traits\HasRoles`, `is_root`/
   `is_owner` bypass flags, `can(...$permissions)` overridden to call `hasAnyPermission()`, and
   company-scoping via `getCompanyId()`. Check that any "current user's data" query is actually
   filtered by company, not just by user id.
2. Root `app/Policies/TeamPolicy.php` + `app/Enums/TeamPermission.php`
   (`belongsToTeam()`/`ownsTeam()`/`hasTeamPermission()`), independent of (1).
Flag any new controller action reachable by an authenticated user that has neither a Policy call,
a `Gate`, a `can()`/`hasAnyPermission()` check, nor route middleware guarding it.

### Models and Eloquent
- `$fillable` updated for any new mass-assignable columns
- `updateQuietly()` / `saveQuietly()` bypass model observers: every use must have a documented
  reason
- New relations: typed return type, correct inverse, eager-loadable without N+1
- Column casts defined for enums, dates, and JSON columns (via the model's `casts()` method)
- New scopes or query methods: check for missing indexes on the filtered column

### Jobs and queue usage
- `ShouldQueue` implemented and dispatched, not run synchronously, for genuinely slow or
  unreliable work
- appsuite defines no named queues beyond Laravel's own `default` today (`config/queue.php`) — a
  job doesn't need to match a specific queue name, but check it's dispatched to a connection
  appropriate for its workload, and isn't sharing a queue with latency-sensitive work in a way that
  would starve it
- `WithoutOverlapping` present (with a meaningful key) for jobs that write to the same model, and
  `expireAfter()` set to prevent permanent locks on failed jobs
- Job is idempotent: safe to run twice without corrupting state

### External service calls
If this PR adds or changes a call to a third-party API (a payment/ecommerce provider under
`Domains/Ecommerce`, a webhook receiver, an outbound `Http::` call):
- Response success is checked (`->successful()`/`->failed()`) before continuing; early return on
  failure
- Failures are logged with enough context to act on
- The call is idempotent or guarded against duplicate side effects on retry
- Credentials come from config/env, never hardcoded

### Domain boundaries
- Domains should not import Models directly from other domains. `Domains/Auth` and
  `Domains/Identity` have each adopted an `app/Contracts` boundary (cross-domain code depends on a
  Contract/interface, not the concrete Model) — treat that as the convention where it's been
  adopted, not a blanket rule the other domains are already following. A new cross-domain
  dependency elsewhere is still worth flagging so the author can decide whether to introduce a
  contract.
- `Domains/Shared` is the actual home for cross-domain shared types (enums, contracts). A type
  that's genuinely cross-domain but was added inside a single domain instead is worth flagging.

### Migrations
- Every migration has a correct `down()` reversal
- No non-nullable column added without a default or backfill migration
- Index defined for every new column used in `WHERE`, `JOIN`, or `ORDER BY`

---

## Step 4: PR body / template compliance

Check for a PR template fresh from disk — never assume its shape (or its existence) from a
previous review:
```bash
cat .github/pull_request_template.md 2>/dev/null \
  || cat .github/PULL_REQUEST_TEMPLATE.md 2>/dev/null \
  || cat docs/pull_request_template.md 2>/dev/null \
  || cat pull_request_template.md 2>/dev/null
```

As of this writing, appsuite has no PR template at any of these locations. If that's still true,
this step is a light sanity check rather than a section-by-section audit: does the PR's actual body
(`gh pr view $ARGUMENTS --repo "$REPO" --json body --jq .body`) explain what changed and why, and
say what testing was done? Flag it if the body is empty or just the branch name restated.

If a template does exist by the time this runs, walk the PR body section by section against
whatever the live template currently asks for, flagging anything missing, still showing the bare
placeholder text, or vague instead of specific — quote the actual gap, don't just say "incomplete."

Either way, this step checks the PR *body* against whatever's expected of it; Step 3 checks the
*code* against real risk, independent of what the author was asked to self-report.

---

## Step 5: Blast radius — quantify when relevant

appsuite runs on a standard relational database (SQLite by default per `config/database.php`,
configurable via `DB_CONNECTION`) — there's no separate analytics store to query here. If the PR
touches a table that could realistically hold a lot of production rows (e.g. `users`, `companies`,
`products`), and quantifying the impact would actually change the verdict, consider a read-only
count against a local/staging DB:

```php
// Example only — adapt table/column names to what the diff actually touches
DB::table('products')->where('status', 'old_value')->count();
```

Skip this step entirely when the diff doesn't touch data at a scale where a count would change
anything (most PRs). Never fabricate a number — if a real query isn't feasible in this context, say
so and describe the blast radius qualitatively instead (which domain, which user-facing flow, which
companies/teams would see it).

---

## Step 6: Write the review

Omit sections that genuinely don't apply. If there are HIGH issues, open with a bold warning.

The block below shows the structure to follow, it is not the literal output format. Deliver the
actual review as real, rendered markdown in the chat response, not wrapped in one big code fence.
See [Output formatting](../shared/output-formatting.md) for why.

```
## PR #[N] Review — [Title]

**Repo:** [org/repo] | **Author:** | **Branch → [base]**
**+[additions] / -[deletions] | [N files]**
**Lint (composer lint:check):** PASS/FAIL | **Static analysis (composer types:check):** PASS/FAIL

---

## What This PR Does
[1-3 sentences. Problem solved. Specific changes made.]

---

## PR Body Compliance
[If a template exists: ✓ or ✗ for each section it currently asks for, quoting the missing or vague
part for each ✗. If no template exists: a short note on whether the actual PR body explains the
change and its testing.]

---

## Diff Analysis

| File | Change | Correct? |
|------|--------|----------|
| [path] | [what it does] | Yes / No — [reason if No] |

---

## Bugs & Regression Risks

### HIGH — Will Break in Production
[File:line — what breaks — blast radius. Only genuine blockers here.]

### MEDIUM — Incorrect Behaviour / Silent Failure
[Wrong data written, observer bypass not justified, enum used unsafely, missing authorization
check, external call's failure not checked.]

### LOW — Polish / Non-Breaking
[Convention miss, dead code, redundant expression, naming inconsistency.]

---

## Blast Radius
[What breaks if this goes wrong. Which domain/flow, which companies or teams could be affected
given appsuite's company/team-scoped multi-tenancy. Quantified with a real DB count only if one
was run in Step 5.]

---

## Pre-Merge Tests

1. [Specific scenario — not "test the feature"]
2. ...

## Post-Merge Checks (first hour)

1. [Specific thing to check after deploy — a log, a query, an error tracker]
2. ...

---

## Review Summary

A consolidated table of every issue found. List every item from "Bugs & Regression Risks", "PR
Body Compliance" failures, and lint/static-analysis failures. One row per issue. Nothing from the
detailed sections above should be omitted here.

Level icons and colors (render as-is in GitHub markdown):
- 🔴 HIGH — will break in production or is a hard blocker
- 🟡 MEDIUM — incorrect behaviour, silent failure, or convention violation
- 🔵 LOW — polish, convention, non-breaking

Category values: `Bug`, `Code Quality`, `Security`, `Performance`, `Template`, `Tests`

| # | Description | Level | Category |
|---|-------------|-------|----------|
| 1 | [concise one-line description of the issue, with file:line where applicable] | 🔴 HIGH | Bug |
| 2 | [concise one-line description] | 🟡 MEDIUM | Code Quality |
| 3 | [concise one-line description] | 🔵 LOW | Code Quality |

Total: **[N] 🔴 HIGH · [N] 🟡 MEDIUM · [N] 🔵 LOW**

---

## Verdict

**[DO NOT MERGE / CONDITIONALLY MERGEABLE / APPROVED]**

Blockers:
- [Blocker 1]
- [Blocker 2]

Conditions (if CONDITIONALLY MERGEABLE):
- [Condition 1]
```

---

## Posting the review as a GitHub comment

When asked to post the review, write to a file first to avoid escaping issues:

```bash
cat > /tmp/pr_review_$ARGUMENTS.md << 'REVIEW_EOF'
[review content]
REVIEW_EOF

gh pr comment $ARGUMENTS --repo "$REPO" --body-file /tmp/pr_review_$ARGUMENTS.md
```

---

## Review principles

- **Be strict but fair.** Find real problems, not manufactured ones. If the code is correct, say so.
- **Distinguish blockers from nits.** A missing authorization check on a new endpoint is a blocker.
  A variable named `$completion_status` instead of `$completionStatus` is not.
- **Check the actual fix, not the intent.** Read the diff. "Fixed" in a commit message means
  nothing if the fix fires in the wrong scope or is guarded by a condition that excludes the
  failing case.
- **Quantify blast radius when it's feasible and would matter.** "Could affect many rows" is
  useless if a real count was easy to get; get it. It's fine to reason about it qualitatively when
  a real query isn't feasible or wouldn't change the verdict.
- **Watch for silent failures.** Bugs that don't error — writing wrong data, skipping records
  quietly, returning early without a guard check — are more dangerous than ones that throw.
- **Multi-tenancy awareness.** appsuite scopes data by company (`User::getCompanyId()`) and by team
  membership (root `app/Models/Team.php`). A change that works for one company or team may
  silently fail for another whose data, role assignments, or team permissions differ.
