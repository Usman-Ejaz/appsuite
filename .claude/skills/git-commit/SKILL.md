---
name: git-commit
description: Commits the current uncommitted changes in appsuite as one or more logical commits, split by concern, after a gitleaks secret check, with messages in the repo's style (imperative subject, optional ticket suffix, why-focused body). Use only when the developer explicitly asks to commit in this turn, e.g. "commit this", "commit the changes", "/git-commit". Does not push or open PRs; that is git-open-pr.
---

# Git Commit

Turn the working tree into one or more clean commits, one per logical change. Never flatten unrelated changes into one commit because they happened in the same session.

Follow [Working Agreement](../shared/working-agreement.md) (this skill is the only place that stages or commits) and [Presentation](../shared/presentation.md) for how the plan is shown. Lessons go through [Auto Learning](../shared/auto-learning.md); read `references/gotchas.md` at Step 1 if it exists.

## Rules

1. **A fresh, explicit ask triggers this skill, every time.** An earlier "commit this" does not cover later changes. When another skill (for example `tech-implementation`) hands off, the trigger is the developer's own "commit" answer in this turn; if you cannot see one, ask. Commit only what the ask covers.
   - **One standing exception:** a `tech-engineering` run's tracking file. The developer pre-approved its change and its commit, so commit it without asking whenever it differs from `HEAD`, and never put it in a question (rule 9). This covers that one file only, never a push and never another file.
2. **Stage by explicit path.** Never `git add -A`, `git add .` or another blanket stage, so a stray `.env`, dump or scratch file never rides along.
3. **Read content, not just file names**, before staging. A harmless-looking name can hold a credential or customer data.
4. **Gitleaks gate.** A file that breaks the gitleaks default rules is never committed, and no code changes to fix a finding until the developer picks a fix. Clean groups can still be committed. Procedure: [references/gitleaks-gate.md](references/gitleaks-gate.md).
5. **Whole files only.** No hunk staging (`git add -p` or a scripted equivalent). A file that mixes two concerns is a question for the developer (Step 3).
6. **New commits only.** No `--amend`, `--no-verify`, `--no-gpg-sign` or `-c commit.gpgsign=false`, unless the developer asks for that exact exception on that exact commit.
7. **A failing pre-commit hook means fix the cause**, re-stage, and make a new commit. Bypassing it would commit unformatted code.
8. **Committing and pushing are separate approvals.** This skill ends with local commits. Never push, and do not offer to.
9. **A run's tracking file is never left behind.** When a `tech-engineering` state file exists for this branch or ticket (`.claude/knowledge-base/tech-engineering/<slug>.md`, named in the hand-off), it is part of your commits.
   - Always stage it in the run-record group (Step 2, group 2). Never hold it back, never ask whether to include it, never ask for approval to commit it, never leave it untracked or modified. The standing exception in rule 1 covers it.
   - The parent writes the phase and status update into the file **before** it calls you, so the commit carries the current state. If it did not, say so and ask it to update the file first. Do not edit the file yourself.
   - The file records the hashes of the other commits, so make the run-record commit **last**. Its own hash is not written in it; the next record names it.
   - At the end, the file must not appear in `git status --short`.

## Formatting: no pre-commit hook in this repo

This repo has no `core.hooksPath` and no pre-commit hook, so nothing formats or re-stages files at commit time. This skill does that job:

- Before staging a group, run Pint on **that group's PHP files only**: `vendor/bin/pint <paths> --format agent`. Never `pint --dirty`: it also reformats the developer's unrelated work in progress.
- `.claude/skills/git-commit/scripts/commit-survey.sh --pint` lists the files Pint will change; mention them in the plan, since Pint changes content after the developer reviewed it.
- Git commits the **staged** version. A file edited again after `git add` must be re-staged, or the newer edits stay behind (the survey lists these).
- If a pre-commit hook is added later (check `git config core.hooksPath` and `.git/hooks/pre-commit`), rule 7 applies to it.

## Step 1: Survey

```bash
.claude/skills/git-commit/scripts/commit-survey.sh --pint
```

It prints: the branch and its ticket ID, merge-in-progress state, every changed, staged and untracked file (untracked folders expanded) with a group hint, files staged and edited again, red flags, whether gitleaks is installed, Pint's preview, and the last 8 commit messages.

- Read the last commits for tone. Do not assume a style.
- **Tests for the changed code**: the survey's automatic check needs `scripts/test-changed.php`, which this repo does not have, so check by hand. A source file changed without a changed test for it goes into the plan under `### Needs your decision` (write the tests, or the developer says why none are needed). It never blocks the commit, because a pure refactor needs no new test.
- **"Scripts not recorded as executable"**: run the printed `git update-index --add --chmod=+x ...` as part of staging that file's group (Step 4), so the script does not fail with "Permission denied" on a fresh checkout.
- Something already staged is a hint that grouping started, not the final grouping. Re-derive it in Step 2 and reconcile.
- **Merge in progress** (`MERGE_HEAD` exists): the first commit concludes the merge. It must hold every resolved file and keeps git's default subject (`Merge branch '<x>' into <y>`, plus the ticket suffix if the branch has one). Unresolved files block it. Follow-up cleanup goes into later commits.

## Step 2: Read every change, then group

Read the diff or content of every file. Group by real cause, not by folder:

- Files changed for the same reason belong together, even across domains (a shared enum plus the one controller that now uses it).
- Files in the same folder changed for different reasons are different commits.
- A fix and its regression test go in the same commit.
- Default split:
  1. Code + its tests: `<Imperative summary>` (plus ` (ENG-12)` when the branch has a ticket)
  2. Run records and knowledge base (`.claude/knowledge-base/**`, `.claude/specs/**`): `Record the <topic> engineering run`, `Close out the <topic> engineering run`. Always the last commit, and always present when a state file exists (rule 9).
  3. Skill files and skill lessons (`.claude/skills/**`): `Record skill learnings from the <topic> run`, or a plain summary
  4. Project rules (`.ai/rules/**`, `CLAUDE.md`, `AGENTS.md`): their own commit unless they describe exactly the code change in the same group.
  Ask only when a file fits none of these, or fits two.
- Work the developer did not ask to commit (other in-progress changes in the tree) stays out. List it under `### Held back` with reason `not part of this ask`, never fold it into a group.
- Flag generated files, caches and scratch or debug output on their own; never fold them into a group.
- Run the gitleaks scan (rule 4) on every file you plan to commit now, so a violation shows up in the plan, not halfway through.

## Step 3: Show the plan, then proceed

Show the plan every time, even when it looks obvious. It is the checkpoint that catches a wrong split before it is in history. Shape (per [Presentation](../shared/presentation.md)):

- Headline: `## ⏸️ Commit plan: N commits` (or `## ❌ Commit plan: blocked by gitleaks` when a group is held).
- One table: `# | Subject | Files | Why`. Files as `inline code`, several per cell separated by `<br>` when needed.
- `### Held back` table (`File | Reason`) for gitleaks, unclear or out-of-scope files, only if any.
- `### Needs your decision` numbered list, only if something is unclear.

- **Everything clear:** go on to Step 4 right after showing it. No extra "go ahead?".
- **Something unclear** (a file that fits two groups, a cause the diff does not show, a red flag, a gitleaks finding): ask one specific question per item (not "does this look right?"). Hold only the affected files; clear groups can proceed.
- **Order:** foundations first (migration or shared enum before the feature that uses it). With no dependency, order so `git log` reads naturally.
- When in doubt, split further. Two small correct commits beat one mixed one.

## Step 4: Stage and commit each group

For each group, in order:

1. Pint on the group's PHP files: `vendor/bin/pint <php paths> --format agent` (skip when the group has none).
2. `git add <explicit paths of this group>`.
3. `git status --short`: what is staged must match the group exactly.
4. Staged gitleaks check (see the reference). On a finding: `git restore --staged <paths>`, then the violation steps.
5. Commit with a heredoc:

```bash
git commit -m "$(cat <<'EOF'
<Imperative, sentence-case subject>

<Body: why the change exists and what it changes in behavior, not a
restatement of the diff. Wrapped at about 72 characters.>

<attribution trailer from the session instructions, if any>
EOF
)"
```

Message format:

- Subject: imperative, sentence case, no trailing period, about 50-72 characters.
- Ticket suffix ` (ENG-12)`: only when the branch name carries a ticket ID (the survey prints it). This repo's history has no ticket suffixes, so with none in the branch, leave it off; do not ask.
- Body: the why. Short paragraphs.

6. Hook failure (if a hook exists): fix the cause, re-stage, commit again (rule 7).

## Step 5: Confirm and report

- `git log --oneline -<N>` and `git status --short`: only deliberately excluded files may be left. A run's tracking file is never one of them (rule 9).
- Report, per [Presentation](../shared/presentation.md):
  - Headline: `## ✅ Committed N commits on <branch>` (or `## ⚠️ Committed N of M groups` when some were held back).
  - Table: `# | Hash | Subject | Files`.
  - `### Pint reformatted` (bullet list of files), only if Pint changed any.
  - `### Held back` table (`File | Reason`), only if any.
  - End with: **The commits are local; nothing was pushed.** Don't offer to push or open a PR (rule 8): delivery is a separate request from the developer.
- Stop there. No push.
