---
name: git-open-pr
description: >
  Open a GitHub pull request for appsuite, filling out the repo's real PR body with genuine,
  diff-derived content instead of leaving it as a blank checklist. Use this whenever the user
  wants to open, create, or raise a PR — phrasing like "open a PR", "create a pull request",
  "let's PR this branch", "raise a PR against main", or "/git-open-pr" — even if they don't name
  this skill directly. Always confirms the source (head) branch, defaulting to the current branch
  but offering any other local branch, and always confirms the target (base) branch from the
  branches that actually exist on the remote, defaulting to the repo's detected default branch
  rather than a fixed list. Detects the actual GitHub org/repo from the git remote rather than
  assuming one, and checks for a PR template at any of the standard locations, re-reading it fresh
  from disk on every run rather than memorising its contents — falling back to a minimal ad hoc
  body when no template exists, which is currently the case in this repo. Drafts the full PR body
  — commit/diff summary, template sections filled from real git data where a template exists, open
  questions (marked `[ASK: ...]`, each with a best-effort guess written inline where one is
  reasoned) for anything only the author can answer (a related issue/ticket, testing evidence,
  rollback plan) — and never checks a safety-sensitive checkbox (a migration-safety checklist, or
  whatever risk checklist a live template might have) on the user's behalf just because a guess
  seems plausible. Once the draft is shown, resolves every open item through one explicit choice of
  mode — accept the written guesses (filling in anything unguessed too), answer everything in one
  bulk reply, or go through each open item one by one — rather than defaulting to asking about each
  item individually. If the branch conflicts with its target, never resolves it by merging the
  target back into the source (the one exception being the repo's detected default branch as
  target, and even then only with explicit approval) — surfaces the conflicting files and asks the
  developer how to proceed instead. Not for reviewing an already-open PR (that's git-pr-review) and
  not for committing changes (that's git-commit) — this skill assumes the branch's commits already
  exist and its job starts at "now open the PR."
---

# appsuite — Open a Pull Request

You are opening a GitHub PR for **appsuite**, using `gh`. This repo currently has no PR template
(`.github/pull_request_template.md` and the other standard locations don't exist yet as of this
writing) — your job is to produce a genuinely filled-out draft from real git data either way: a
real template if one exists by the time this runs, or a clear minimal ad hoc body if not. Either
way, stop for the author's input wherever the answer isn't in the diff.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every step
below, not a one-time phase). Specific to this skill:

- **Wider means:** a section that's hard to auto-fill for one PR (e.g. blast-radius wording,
  rollback-plan phrasing) is usually hard for the same reason on the next PR too — a phrasing
  pattern the user approves here is worth reusing verbatim next time, not re-deriving.
- **Unfamiliar means:** whether a PR template exists yet, and if so what shape it has — re-check
  the standard locations fresh (Step 5) rather than assuming last run's "no template" finding still
  holds, since the user may add one at any time.
- **Learnings file:** `.claude/knowledge-base/skills/git-open-pr.md` — phrasing the user has
  approved for recurring sections (blast radius, rollback plan), any project convention for PR
  titles confirmed from real `gh pr list` history, whether/when a PR template appeared and its
  shape, which resolution mode (Step 8) the developer tends to reach for, and which source→target
  pairs tend to conflict (Step 4).
- **Propose, don't silently apply a new heuristic:** if a template appears or changes shape (new
  section, removed section, reworded checkbox), propose updating Step 7's field-mapping rather
  than quietly reinterpreting the new template through an old mapping.

---

## Rules (non-negotiable)

1. **Never hardcode a template's contents, and never assume one exists.** Check the standard
   locations fresh from disk every run (Step 5) — never rely on a remembered copy or a remembered
   "there's no template" finding from an earlier run.
2. **Never auto-check a safety-sensitive checkbox.** If a template exists and has a risk/safety
   checklist (e.g. a migration-safety section), it gets left unchecked with the relevant question
   surfaced to the user, even when the diff looks like it obviously qualifies for "skip" — a wrong
   guess here is exactly the failure mode these checks exist to prevent. This holds under every
   resolution mode in Step 8, including "agree with your guesses" — that mode fills in open text,
   it never ticks a safety-sensitive box on the developer's behalf.
3. **Source and target branch are always explicitly confirmed**, every run — never assume the
   current branch or infer the target from branch naming, even when it seems obvious.
4. **Pushing and PR creation are one combined, explicit approval** — this skill doesn't push
   commits as a silent side effect of an earlier step; see Step 9.
5. **A drafted body is a proposal, not a default.** Show it and loop on feedback before calling
   `gh pr create` — same pattern as this repo's other git skills (never create/push on the first
   draft).
6. **Diff against a freshly-fetched `origin/<target>`, never a possibly-stale local branch.**
   Step 3 fetches before anything is computed, and every diff/log/merge-check from Step 4 onward
   uses `origin/<target>` and `origin/<source>` — a stale local `<target>` is exactly how commits
   `<target>` already absorbed (through an earlier PR, or someone else's merge) get re-described as
   new in this one, or how a conflict that's already been resolved upstream looks unresolved here.
7. **Open-item resolution defaults to one confirming choice, not N separate questions.** Once the
   draft is shown, ask a single `AskUserQuestion` for how to resolve every `[ASK: ...]` marker at
   once — accept the written guesses, answer everything in one bulk reply, or go through each item
   one by one (Step 8). Don't fall back to asking about each open item individually unless the
   developer specifically picked that mode.
8. **A merge conflict with the target is never resolved by merging the target back into the
   source** — that permanently pulls the target's full history into the source branch, including
   whatever wasn't meant for it. The one exception is when the target is the repo's detected
   default branch specifically (Step 2) — that branch only ever holds already-integrated code, so
   merging it into `<source>` doesn't drag in anything risky. Still ask before doing it — it's a
   real merge commit landing on `<source>` — but it isn't the forbidden case. Every other target:
   don't resolve it yourself; surface it to the developer.

---

## Progress checklist

Before Step 1, create a `TodoWrite` checklist mirroring the steps below.

1. Preflight (`gh` available, authenticated, detect the repo)
2. Confirm source branch
3. Confirm target branch
4. Fetch and sync check
5. Check for conflicts with the target
6. Check for a PR template on disk
7. Gather real git context (commits, diff, files)
8. Draft the PR title and body
9. Show draft, resolve open items (one chosen mode), loop until approved
10. Push (if needed) and create the PR — one combined approval
11. Report the PR URL

---

## Step 0: Preflight

```bash
command -v gh || { echo "gh CLI not found — install it from https://cli.github.com"; exit 1; }
gh auth status
git rev-parse --is-inside-work-tree
git status --short
```

Detect the actual GitHub org/repo — never hardcode one:
```bash
REPO=$(gh repo view --json nameWithOwner --jq '.nameWithOwner' 2>/dev/null)
if [ -z "$REPO" ]; then
  REPO=$(git remote get-url origin 2>/dev/null | sed -E 's#^(git@github\.com:|https://github\.com/)##; s#\.git$##')
fi
```
If `$REPO` still comes back empty, there's no `origin` remote configured yet — stop and tell the
user a GitHub remote needs to exist before a PR can be opened. Use `$REPO` in every `gh` command
that follows (`--repo "$REPO"`).

If there are uncommitted changes, surface them — they won't be part of the PR unless committed
first. Ask whether to proceed anyway (PR from what's already committed) or pause so the user can
commit. Don't commit on their behalf; that's `git-commit`'s job, not this skill's.

---

## Step 1: Confirm the source (head) branch

```bash
git rev-parse --abbrev-ref HEAD
git for-each-ref --sort=-committerdate refs/heads/ --format='%(refname:short)' --count=6
```

Use `AskUserQuestion`. Always ask, even when the current branch is obviously the intended one —
the point is confirmation, not inference:

- Current branch, e.g. `"add-passkey-flow (current)"` — mark `(Recommended)`
- Up to 3 other recently-committed local branches from the `for-each-ref` output, excluding the
  current one
- The tool's built-in "Other" option covers any branch not listed, including one that only exists
  on `origin`

If the user picks a branch other than current, confirm it exists (locally or on `origin`) before
moving on — `git rev-parse --verify <branch>` or `git ls-remote --exit-code --heads origin
<branch>`. If it exists only on `origin`, note that a local checkout may be needed before pushing
in Step 9.

---

## Step 2: Confirm the target (base) branch

Detect what actually exists on the remote and which one is the default — never offer a fixed list
of branch names, since this repo's branch model isn't a settled three-way convention:

```bash
git fetch origin --prune
DEFAULT=$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')
git branch -r --format='%(refname:short)' | sed 's#^origin/##'
```

Use `AskUserQuestion` with the real branches found:
- The detected `$DEFAULT` (this repo's CI workflow, `.github/workflows/tests.yml`, currently runs
  on pushes to `main`, which is a strong signal for what `$DEFAULT` will resolve to) — mark
  `(Recommended)`
- Up to 3 other real remote branches from the listing above, excluding `$DEFAULT`
- The tool's built-in "Other" option covers anything not listed

If `git branch -r` returns nothing at all (a brand-new repo with no pushed branches yet), tell the
user there's nothing to target yet and stop — a base branch has to exist on `origin` before a PR
can be opened against it.

Do not pre-select a recommendation from the source branch's name (e.g. don't auto-recommend a
particular target just because the branch is named `hotfix/...`) — the user confirms this
explicitly every time per Rule 3, not by pattern-match.

Then check whether a PR already exists for this exact source → target pair, to avoid creating a
duplicate:
```bash
gh pr list --repo "$REPO" --head <source> --base <target> --state open --json number,url,title
```
If one exists, tell the user and ask whether they want to update that PR's description instead
(open it and hand off), stop and let them decide, or proceed anyway (e.g. genuinely intentional).
Don't silently create a second PR for the same branches.

---

## Step 3: Fetch and sync check

Fetch first, before computing anything. A stale local `<target>` is exactly how already-merged
commits end up mis-described as new in Step 6/7 — e.g. some of `<source>`'s commits already landed
on `<target>` through an earlier PR, but nobody fetched, so local `<target>` hasn't caught up:

```bash
git fetch origin <source> <target> --prune
git rev-parse --verify <source>
git status --short
git rev-parse --verify origin/<source> 2>/dev/null && echo "origin/<source> exists" || echo "never pushed"
git log origin/<source>..<source> --oneline 2>/dev/null   # local commits not yet on origin
git log <source>..origin/<source> --oneline 2>/dev/null   # commits on origin not yet local (divergence)
```

Three outcomes:
- **Never pushed, or local is strictly ahead of `origin/<source>`** (only the first log has
  output) — Step 9 needs a push (`git push -u origin <source>`).
- **Local is behind, or diverged from, `origin/<source>`** (the second log has output) — stop and
  tell the user; don't push automatically. Someone else may have pushed to the same branch, or
  history was rewritten — resolving that (pull, rebase, or force-push) is the user's call, not
  something this skill decides silently.
- **They match exactly** — no push needed.

From here on, use `origin/<target>` and `origin/<source>` — the refs just fetched — for every
diff/log/merge-check in Step 4 onward. Never the local `<target>` branch; that's the specific ref
that goes stale.

---

## Step 4: Check for conflicts with the target

Before drafting anything, check whether `<source>` would actually merge cleanly into `<target>` —
better to surface this now than after the whole PR body is written. This is a read-only check; it
never touches either branch's working tree or history:

```bash
git merge-tree --write-tree origin/<target> origin/<source>
echo "exit: $?"
```

A non-zero exit (or `<<<<<<<` conflict markers in the output) means a real conflict. On older `git`
that lacks `--write-tree`, use the legacy three-argument form instead, which only prints and never
touches anything:
```bash
git merge-tree "$(git merge-base origin/<target> origin/<source>)" origin/<target> origin/<source>
```

**No conflicts:** continue to Step 5.

**Conflicts found:** apply Rule 8 strictly, regardless of how urgent the PR is or how the branch
got here:

- **Never merge `<target>` back into `<source>` to resolve it.** Doing so permanently pulls
  `<target>`'s entire history into `<source>`, including whatever wasn't meant for it — exactly the
  unwanted-changes risk this rule exists to prevent.
- **The one exception: `<target>` is the repo's detected default branch** (`$DEFAULT` from Step 2).
  That branch only ever holds already-integrated code, so merging it into `<source>` doesn't drag
  in anything risky — that's the normal, accepted way to bring a long-running branch back in sync.
  Still ask before doing it — it's a real merge commit landing on `<source>` — but it isn't the
  forbidden case.
- **Every other target:** don't resolve it yourself. Tell the developer exactly which files
  conflict (from the merge-tree output) and ask how they want to proceed — rebasing `<source>` onto
  `<target>` (only sensible if nobody else depends on `<source>`'s current history), resolving the
  specific files by hand together, or opening the PR anyway and resolving on GitHub's UI. Don't
  pick one of these silently: a wrong pick either rewrites shared history or drags in commits the
  developer never intended.

**Done when:** either the merge-tree check came back clean, or a conflict was found and handled per
Rule 8 — the default branch merged in only with explicit approval, any other target left for the
developer's explicit decision.

---

## Step 5: Check for a PR template on disk

Per Rule 1, always re-check fresh — never rely on a remembered finding from an earlier run:

```bash
cat .github/pull_request_template.md 2>/dev/null \
  || cat .github/PULL_REQUEST_TEMPLATE.md 2>/dev/null \
  || cat docs/pull_request_template.md 2>/dev/null \
  || cat pull_request_template.md 2>/dev/null
```

As of this writing, none of these exist in appsuite. If that's still true when this runs, tell the
user plainly and proceed with a minimal ad hoc body (Step 7 covers both shapes). If a template does
exist by the time this runs, walk it section by section per Step 7 instead of using the ad hoc
shape.

---

## Step 6: Gather real git context

Diff against `origin/<target>` (fetched in Step 3), not the local `<target>` branch — this is the
part that actually excludes commits `<target>` has already absorbed elsewhere:

```bash
git log origin/<target>..<source> --no-merges --pretty=format:'%h|%an|%s'
git diff origin/<target>...<source> --stat
git diff origin/<target>...<source> --name-only
```

If the range is empty, stop and tell the user — either there's genuinely nothing to PR, or
everything on `<source>` is already merged into `<target>` and this PR would be a no-op. Don't
fabricate a body for an empty diff either way.

Look for a related issue/ticket reference to pre-fill: check the branch name and every commit
subject for a plausible pattern — a GitHub issue reference (`#\d+`), or a ticket-key shape like
`[A-Z]+-\d+` (Jira/Linear-style). appsuite has no established ticket-ID convention yet, so treat
any match as a loose hint, not a confirmed convention. If found, use it as the suggested value
(still shown in the draft for confirmation, not silently inserted). If not found, leave it blank
and ask the author directly whether there's a related issue — don't assume one is required just
because the diff doesn't reference one.

Note which domains the changed files actually touch (`Domains/Auth`, `Domains/CMS`, `Domains/Core`,
`Domains/Ecommerce`, `Domains/Identity`, `Domains/Shared`, or root `app/` for cross-cutting/Teams
code) — useful for the Summary section in Step 7.

---

## Step 7: Draft the PR title and body

**Title:** a short, imperative, sentence-case summary of the change. Not the branch name verbatim.
Check recent title conventions with `gh pr list --repo "$REPO" --state merged --limit 10` if
unsure of tone — if that returns nothing (no merged PRs yet, e.g. this is one of the first PRs in
the repo), just use a clear, specific imperative title; there's no house style to match yet.

**Body:** structure depends on what Step 5 found.

**If a real template exists:** walk it section by section. For each section, do one of three
things — never leave a section as the bare unfilled template text:
- **Fill it from real data** where the diff/commits genuinely answer it.
- **Leave an explicit open question, with a real guess wherever one can be reasoned out**, marked
  inline as `[ASK: ...]`, per the same rules as the ad hoc case below.
- **Leave unchecked and flag** any security-sensitive checkbox per Rule 2.

**If no template exists (currently the case in appsuite):** draft this minimal ad hoc structure,
filled the same way:

```
## Summary
[Problem/Solution, drawn from the commit subjects and diff shape.]

## Changes
- [bullet per meaningful change, drawn from the diff/commit log]

## Domains touched
[Auth / CMS / Core / Ecommerce / Identity / Shared / root app/, from Step 6]

## Testing
[ASK: what was actually run — never invent this]

## Related issue
[ASK: with a guess if Step 6 found a plausible reference, otherwise no guess]

## Notes for reviewers
[ASK: anything the author wants to flag — rollback plan, risk, follow-ups]
```

Whichever shape is used: for every `[ASK: ...]` marker, write a real guess inline wherever the
diff, commits, or this conversation give a genuine basis for one — the same way this skill reasons
through domain-impact and title judgment calls. Leave a marker guess-less only when there's
genuinely nothing to base one on (testing evidence and the related issue are the standing
examples) — say so plainly rather than inventing a guess with no grounding. Step 8's
guess-accepting resolution modes depend on a real guess existing here, not a bare placeholder.

Write the assembled draft to a temp file to avoid shell-escaping issues later:
```bash
cat > /tmp/pr_body_<source-branch-slug>.md << 'PR_EOF'
[assembled body]
PR_EOF
```

---

## Step 8: Show the draft, resolve open items, loop

Show the full title and body rendered (real markdown, not a code fence — same reasoning as this
repo's other git skills: this is for a human to read, not inspect markup).

If the draft contains any `[ASK: ...]` markers, ask **one** `AskUserQuestion` for how to resolve
all of them, per Rule 7 — don't default to asking about each one individually:

- **"Agree with your guesses, proceed with it"** — accept the guess written inline next to each
  `[ASK: ...]` marker as its final answer, and fill it into the draft in place of the marker. For
  any marker that has no guess at all, make a best-effort guess for it now and fill that in too —
  choosing this option means the developer is trusting your judgment across every open item, not
  only the ones you'd already reasoned through. Rule 2 still applies regardless: no safety-
  sensitive checkbox gets checked under this option either.
- **"I'll answer in bulk"** — stop here and wait for the developer's next message. When it arrives,
  match each part of it back to the specific `[ASK: ...]` item it resolves by content, not by
  position in the list, and fill each one in. If some part of the reply doesn't clearly map to a
  specific item, ask a short, specific follow-up about just that part — don't re-ask everything
  that was already answered clearly.
- **"Ask me one by one"** — go through the remaining open items one at a time, each its own
  `AskUserQuestion` with:
  - **"Agree with your guess"** — only offer this when Step 7 actually wrote one for this specific
    item; when it didn't, say so and skip straight to the next option instead of offering an empty
    "agree."
  - **"N/A"** — this item genuinely doesn't apply to this PR.
  - The tool's built-in "Other" option covers the developer's own free-text answer — no need to add
    it explicitly.

Whichever mode is used, if the developer also wants to edit something else in the draft — not just
resolve an `[ASK: ...]` item — treat that as a normal free-form edit request: apply it and re-show
the updated draft; don't assume a partial edit means full approval of the rest.

Do not proceed to Step 9 until every `[ASK: ...]` marker is gone from the draft (a real value, not
a placeholder) and the developer has given explicit final approval of the whole thing.

**Done when:** no `[ASK: ...]` marker remains in the draft, and the developer has explicitly
approved the final version.

---

## Step 9: Push (if needed) and create the PR — one combined approval

Per Rule 4, this is a single explicit checkpoint covering both actions, since creating the PR
requires the branch to exist on `origin` anyway:

Ask directly: **"Push `<source>` to origin and open the PR against `<target>` now?"**

Only on explicit yes:
```bash
git push -u origin <source>   # only if Step 3 found it never-pushed or strictly ahead
gh pr create --repo "$REPO" --base <target> --head <source> \
  --title "<approved title>" --body-file /tmp/pr_body_<source-branch-slug>.md
```

If Step 3 found `<source>` behind or diverged from `origin/<source>` instead, don't push here —
that case was already flagged for the user to resolve, and pushing over it risks a rejected
non-fast-forward push or, worse, force-pushing over commits nobody's looked at yet.

If the user declines, stop here and leave everything as drafted locally — the temp file stays in
place so they (or a later run) can pick it back up without redrafting.

---

## Step 10: Report

Show the PR URL from `gh pr create`'s output. If any `[ASK: ...]` items were resolved with
placeholder-ish answers (e.g. "TBD"), call that out explicitly rather than letting it pass silently
— a PR with a real URL but a hollow body isn't actually done.

---

## Principles

- A checked box that isn't true is worse than an unchecked one — a safety checklist only works if
  it reflects a real check, not a confident auto-fill.
- Whatever the live state of the repo actually is — a template or none, one default branch or
  several plausible ones, an org/repo detected from the remote — is the one source of truth; this
  skill never substitutes a remembered or assumed version of any of it.
- Confirmation of source/target branch is cheap; a PR opened against the wrong base is not — ask
  every time, even when it seems obvious.
- One confirming choice beats a wall of questions — but the choice has to be real: a guess with no
  basis, offered as something to "agree" with, is worse than an honest blank.
- A merge conflict is information, not an obstacle to route around — merging the target into the
  source to make it go away can quietly rewrite what the source branch is even for.
