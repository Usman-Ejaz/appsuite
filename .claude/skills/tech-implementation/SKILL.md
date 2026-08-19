---
name: tech-implementation
description: >
  Implement an approved plan, a discrete task, a bug fix, or an ad hoc requirement the developer
  describes on the spot. Use this whenever the developer is ready to actually start writing code
  — including phrasing like "let's build this", "implement the plan", "go ahead and build what we
  discussed", "start on X", "fix this bug", "I need you to add X real quick", or being handed off
  from a `tech-discovery` session — even when the developer doesn't say "/tech-implementation" or
  name this skill directly. Always confirms which branch to work on (current, a new branch off a
  confirmed base, or another existing branch) and fetches that base fresh from origin first.
  Presents an implementation checklist before writing any code and keeps it updated as work
  progresses. Never stages or commits anything without separate, explicit developer approval — the
  developer must review the code itself before the full test suite runs, and the full suite runs
  once at the end, not continuously during development, per explicit developer preference to avoid
  slowing the process down. On explicit commit approval, hands off to `git-commit` rather than
  staging or committing directly. Not `git-commit` (that skill owns staging/grouping/committing
  once code is ready) and not `code-review` (a self-review pass on an already-built branch, not
  this skill's build-then-review-then-test flow).
---

# Tech Implementation: Build, Review, Test, Hand Off

You are writing code for a task the developer has already defined — an approved `tech-discovery`
plan, a Linear ticket, a bug report, or something they're describing to you right now. Your job
ends at "tests pass and the developer has approved committing it"; the actual commit is
`git-commit`'s job, not this skill's.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** a branch-naming or checklist-shape pattern the developer approves once is worth
  reusing on the next task without re-asking from scratch — but branch confirmation itself (Step 1)
  always still happens per Rule 2, this is about the *shape* of the question, not skipping it.
- **Unfamiliar means:** a testing or tooling command that isn't the standard pair below (e.g. this
  task touches a package with its own test runner) — verify the actual command before Step 5 rather
  than assuming the default applies everywhere.
- **Learnings file:** `.claude/knowledge-base/skills/tech-implementation.md` — recurring ambiguity
  patterns worth a standard clarifying question, checklist shapes the developer has approved, and
  any project-specific testing/tooling wrinkle confirmed outside the standard commands.
- **Propose, don't silently apply a new heuristic:** if a new kind of ambiguity or a new step
  (beyond the fixed flow below) keeps coming up, propose adding it rather than only handling it ad
  hoc each run.

---

## Rules (non-negotiable)

1. **Ask about genuine ambiguity before starting.** Guessing wrong on an unclear requirement costs
   a rebuild; asking costs one exchange.
2. **Branch is always explicitly confirmed** — current, a new branch (with its base also
   confirmed), or another existing branch — never assumed, even when it seems obvious.
3. **The base branch is fetched and updated from origin before anything is compared or branched
   from it.** A stale local base makes work look complete against a target that's already moved.
4. **Never stage or commit anything without separate, explicit developer approval.** Matches
   `git-commit`'s own first rule — that skill owns staging and committing; this skill's job stops
   at "ready to commit."
5. **The implementation checklist is presented before any code is written, and kept updated as
   work progresses** — visibility into progress, not a one-time formality.
6. **Developer approval (code review) is a hard stop, second-to-last in the flow.** Don't run the
   full test suite and don't ask about committing until the developer has actually reviewed the
   code changes and explicitly approved them.
7. **The full test suite runs once, after approval — not continuously during development.** This
   is an explicit developer preference: testing on every change slows the process down; batching it
   at the end is where it actually gates something real (commit approval).
8. **Commit approval is asked separately, after tests pass, and only on yes do you hand off to
   `git-commit`.** This skill never runs `git add` or `git commit` itself.

---

## Progress checklist

Before Step 0, create a `TodoWrite` checklist mirroring the steps below (branch confirmed → base
updated → implementation checklist presented → code written → developer approval → tests → commit
approval/hand-off), so progress through the whole flow — not just the implementation checklist from
Step 2 — stays visible.

---

## Step 0: Resolve what's being implemented

Identify the source of the task: a plan handed off from `tech-discovery`, a Linear ticket, a bug
report, or an ad hoc requirement the developer just described. If anything is genuinely
ambiguous — missing acceptance criteria, unclear scope, a requirement that conflicts with what the
code currently does — ask before proceeding. Plain conversational questions for open-ended gaps,
`AskUserQuestion` for closed choices.

**Done when:** the task is understood well enough to write a concrete checklist from it in Step 2,
with no unresolved ambiguity that would change the shape of the work.

---

## Step 1: Confirm the branch

Use `AskUserQuestion`:

- **Current branch** — offer as the default when it already matches the task and has no unrelated
  in-flight work; still requires explicit confirmation, never assumed silently.
- **Create a new branch** — if chosen, also ask for the base branch. This repo has no fixed,
  long-lived branch model to assume — confirm the actual base explicitly (checking what exists on
  `origin` rather than guessing a name), the same way `git-open-pr` confirms its own target branch
  from what's actually on the remote instead of a fixed list.
- **Other** — an existing branch not currently checked out.

Whichever base is involved (a new branch's base, or an existing branch's own upstream), fetch it
fresh before doing anything else — per Rule 3, a stale local ref is exactly how already-landed
changes get mis-tracked against what's actually on `origin`:

```bash
git fetch origin <base-or-branch> --prune
```

- **New branch:** create it from the freshly-fetched ref, not a possibly-stale local one:
  ```bash
  git checkout -b <new-branch> origin/<base>
  ```
- **Current or other existing branch:** compare against its own upstream and flag rather than
  silently proceed if it's behind or diverged:
  ```bash
  git log <branch>..origin/<branch> --oneline   # commits on origin not yet local
  git log origin/<branch>..<branch> --oneline   # local commits not yet pushed
  ```
  If the branch is behind its upstream, tell the developer and let them decide how to resolve it
  (pull, rebase) — don't resolve it automatically.

**Done when:** a branch is checked out, its base (or its own upstream) is confirmed up to date with
origin, and the developer has explicitly confirmed this is the branch to work on.

---

## Step 2: Present the implementation checklist

Before writing any code, build a `TodoWrite` checklist for the actual work. If this task came from
an approved `tech-discovery` plan, reuse its Scope of Work / Checklist section directly rather than
re-deriving one. Show it to the developer as real markdown per
[Output formatting](../shared/output-formatting.md). If the developer flags something wrong or
missing, fix the checklist before starting — this is a visibility checkpoint, not a rubber stamp.

**Done when:** the developer has seen the checklist and raised no objection to its shape or scope.

---

## Step 3: Implement

Work through the checklist, updating each `TodoWrite` item's status (pending → in_progress →
completed) as you go so progress stays visible in real time — don't batch updates until the end.

Follow this repo's established conventions while writing code:

- `laravel-best-practices` is the authoritative reference for backend PHP patterns (controllers,
  models, jobs, validation, queries) — consult it rather than re-deriving conventions ad hoc.
- `pest-testing` is the authoritative reference for how tests in this repo are written — use it
  for any test file this task touches, rather than restating Pest conventions here.
- Respect the module boundaries `nwidart/laravel-modules` enforces: no cross-domain `Model`
  imports between `Domains/Auth`, `Domains/CMS`, `Domains/Core`, `Domains/Ecommerce`,
  `Domains/Identity`, and `Domains/Shared`, or between a Domain and the cross-cutting Teams code
  in root `app/` (`app/Models/{Team,Membership,TeamInvitation}.php`,
  `app/Policies/TeamPolicy.php`, `app/Enums/{TeamPermission,TeamRole}.php`).
- If the task touches authorization, roles, or permission checks, use the `apply-permissions`
  skill for how that's actually done here (spatie/laravel-permission roles/permissions in
  `Domains/Identity`, plus the Team-membership Policy system in root `app/`) rather than inventing
  a scoping model.

After any PHP file change, run `vendor/bin/pint --dirty --format agent` per this project's
standing convention (CLAUDE.md) so formatting is caught as you go, not saved up for Step 5.

Per Rule 4, never stage or commit during this step, even to checkpoint work-in-progress — that's a
separate, later approval.

**Done when:** every item on the checklist has a corresponding code change and is marked
completed.

---

## Step 4: Developer approval — code review checkpoint

Present a summary of what changed (the diff, or a file-by-file description if the diff is large)
and stop. This is the second-to-last step in the whole flow: do not proceed to Step 5 (testing) or
Step 6 (commit approval) until the developer has actually reviewed the code and explicitly approved
it. If they ask for changes, apply them and re-present — the same loop pattern used for plan
approval in `tech-discovery` and draft approval in `git-open-pr`.

**Done when:** the developer has given explicit, unambiguous approval of the code changes.

---

## Step 5: Full test suite

Only after Step 4's approval — per Rule 7, this is the one point in the flow where testing runs,
and the one point where iterating on failures is expected, since it gates commit approval rather
than slowing down active development. Run this repo's actual composer scripts:

```bash
composer lint
composer test
```

`composer lint` runs Pint in auto-fix mode — if it reformats any files, list them. `composer test`
chains `artisan config:clear`, `lint:check` (Pint in check mode), `types:check` (PHPStan via
Larastan), and the full `artisan test` suite, in that order — report pass/fail for each stage it
reports. If `types:check` reports new errors, list each with file and line and fix them. If tests
fail, list the failing tests, fix them, and re-run `composer test` until clean. While iterating on
one failure, `php artisan test --compact --filter=<name>` is fine for a fast targeted re-run, but
`composer test` clean is what actually gates Step 6.

**Done when:** `composer lint` and `composer test` both pass clean.

---

## Step 6: Ask for commit approval

Once tests are clean, ask the developer directly whether to commit now. On explicit yes, invoke the
`git-commit` skill via the `Skill` tool so it applies its own grouping/staging/secret-screening
logic — never stage or commit directly from within this skill (Rule 4/8). On no, stop and leave the
working tree as-is; the developer can commit later whenever they're ready.

**Done when:** either `git-commit` has been invoked, or the developer has explicitly declined to
commit right now.

---

## Principles

- A checklist the developer never saw is a plan they never actually agreed to — show it before
  starting, not after.
- Code review before testing, not after: there's no point running a full suite against code the
  developer is about to ask you to change.
- Testing is a gate, not a treadmill — it runs once, when it actually decides something (whether
  this is ready to commit), not on every intermediate edit.
- Staging and committing are `git-commit`'s job. This skill's job is done once the code is tested
  and approved.
