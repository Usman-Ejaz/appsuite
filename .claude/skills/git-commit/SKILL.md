---
name: git-commit
description: >
  Commit the currently uncommitted changes in this repo, automatically splitting unrelated
  logical changes into separate commits instead of one giant commit, and asking specific
  questions only when the grouping is genuinely ambiguous. Use this whenever the user explicitly
  asks to commit — phrasing like "commit this", "commit the changes", "let's commit", "commit
  what we just did", "go ahead and commit that", or "/git-commit" — never proactively and never
  as a silent follow-on to any other task without that explicit ask, matching this project's
  standing rule that a prior "commit this" earlier in the session never authorizes committing
  later, unrelated changes on its own. Reads the actual diff/content of every changed and
  untracked file before grouping (not just file names), stages files explicitly (never
  `git add -A`/`git add .`), screens for anything that looks like a secret or stray scratch/debug
  output before it's staged, matches this repo's real commit-message tone from its own `git log`
  once one exists, and never pushes or amends an existing commit unless separately, explicitly
  asked.
---

# Git Commit: Group and Commit by Logical Concern

You are committing whatever is currently uncommitted in this repo. Its whole job is recognizing
when the working tree holds more than one logical change and never flattening them into a single
commit just because they happened in the same session.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** a grouping pattern that worked once (e.g. "split by domain when the diff spans
  `Domains/*`", "docs land in their own commit separate from the code they describe") is likely to
  recur on the next multi-change commit, not a one-off judgment call to re-derive from scratch.
- **Unfamiliar means:** whether this repo/account uses a specific commit-message convention (a
  ticket-id prefix, Conventional Commits, a particular trailer) — check the real `git log` output
  in Step 1 rather than assuming a style from general habit. This repo has no git history at all
  until its first commit; don't assume a convention exists before there's evidence of one.
- **Learnings file:** `.claude/knowledge-base/skills/git-commit.md` — recurring grouping calls,
  file-to-group judgment calls that came up more than once, phrasing patterns for commit messages
  the user has approved, and any project-specific message convention confirmed from real history.
- **Propose, don't silently apply a new heuristic:** if a grouping rule not covered below comes up
  and seems likely to recur, propose adding it to this skill rather than deciding it ad hoc every
  time the same shape of ambiguity appears.

---

## Rules (non-negotiable)

1. **Only commit what was actually asked for.** This skill firing at all means the user just
   explicitly asked to commit — never expand that into committing additional changes made after
   the request, or changes the user didn't mean to include.
2. **Stage files explicitly, by path.** Never `git add -A`, `git add .`, or any other blanket
   stage — each `git add` names the specific files belonging to the commit currently being built,
   so an unrelated untracked file, a stray `.env`, or scratch output never rides along by accident.
3. **Read content, not just filenames, before staging anything unfamiliar.** A filename can look
   innocuous while its contents hold a credential, a token, or a customer-identifying value that
   shouldn't be committed. Check before `git add`, not after.
4. **Whole-file granularity — never split one file's changes across commits.** No patch/hunk-level
   staging (`git add -p` or a scripted equivalent). If a single file's diff genuinely mixes two
   unrelated concerns, that's a flagged case for Step 3, not something to silently resolve by
   picking hunks.
5. **Always create new commits.** Never `--amend` an existing commit, never `--no-verify`,
   `--no-gpg-sign`, or `-c commit.gpgsign=false`, unless the user explicitly asks for that specific
   exception on that specific commit.
6. **Committing and pushing are two different approvals.** Never run `git push` as a continuation
   of this skill unless the user separately, explicitly asks to push — a completed commit sequence
   ends with local commits, not a push.
7. **A failing pre-commit hook means fix the cause, not bypass the hook.** Fix the underlying
   issue, re-stage, and create a new commit. Never add `--no-verify` to force it through.

---

## Step 1: Survey what's actually uncommitted

Run in parallel:

```bash
git status --short
git diff --stat
git diff --staged --stat
git log --oneline -10
```

If `git log` fails or returns nothing (no commits yet — a real possibility here, since this repo
may not have an initial commit), there's no tone to match yet: write a short imperative subject and
a body explaining *why*, following this project's general conventions (see
`/home/usmanejaz/usman/projects/personal/appsuite/CLAUDE.md`) rather than inferring a style from
history that doesn't exist. Once real history exists, always read it fresh rather than assuming a
style carried over from a previous run.

If anything is already staged, treat that as a hint the user (or an earlier step this session) may
have already started grouping, not as the final, correct grouping — re-derive the grouping in
Step 2/3 regardless, and reconcile against what's pre-staged rather than trusting it blindly.

---

## Step 2: Read every change before grouping

For every changed and untracked file, look at its actual diff or content, not just its path or
filename. Group by genuine causal relationship, not superficial similarity:

- Files touched for the same underlying reason belong together, even across different domains or
  directories — a shared enum in `Domains/Shared` plus the one controller in `Domains/Ecommerce`
  that now uses it is one change, not two.
- Files that merely sit in the same directory but were changed for unrelated reasons are
  different commits, even though they look alike in a file listing.
- A fix and its own regression test belong in the same commit as the fix.
- A docs update describing a specific code change usually belongs with that change — but check
  `git log` for this repo's own recent precedent first once one exists; match whichever pattern the
  actual recent history shows, and if it's genuinely unclear which applies, that's a Step 3
  question. Remember: this project's CLAUDE.md says not to create documentation files unless
  explicitly requested, so an unexpected new `.md` file is itself worth a second look, not just a
  grouping question.
- Anything that looks like a generated artifact, a cache file, or stray scratch/debug output that
  was likely never meant to be committed gets flagged on its own, not folded into any group.
- While reading, watch for anything matching Rule 3 (secrets/credentials) — flag it immediately
  rather than continuing to build a plan around a file that shouldn't be staged at all.

---

## Step 3: Propose the grouping, then proceed unless something is genuinely ambiguous

Present a short, concrete plan: N commits, each listing its files and a one-line reason. This is
the checkpoint that lets a skimming user catch a wrong split before anything is written to
history — always show it, even when the grouping seems obvious.

- If every file's group is clear, proceed straight to Step 4 once the plan is shown; don't wait
  for a separate "yes, go ahead" on top of the plan itself.
- If something is genuinely unclear — a file that could plausibly belong to more than one group,
  a causal relationship that isn't obvious from the diff alone, or a flagged artifact/secret from
  Step 2 — ask a specific, concrete question about that item via `AskUserQuestion` (not a vague
  "does this look right?"), and hold only the affected file/group until answered; unambiguous
  groups can proceed without waiting on it.

**Decide commit order** as part of the same plan: if one group's change only makes sense applied
on top of another (a migration or shared enum before the feature code that uses it), order the
foundational one first. With no real dependency, order however reads most naturally in `git log`
afterward — typically shared/foundational changes before the features built on them.

---

## Step 4: Stage and commit each group, in order

For each group, in the order decided in Step 3:

1. `git add <explicit paths for this group only>` — never a blanket flag.
2. `git status --short` to confirm exactly what's staged matches this group, nothing more and
   nothing less, before writing the message.
3. Write the commit message to match the real tone/length observed in Step 1 (or, if there's no
   history yet, a short imperative subject plus a body explaining *why*): a short imperative
   subject, then a body that explains *why* the change exists, not a restatement of the diff.
   Always via a heredoc to avoid shell-escaping issues:

```bash
git commit -m "$(cat <<'EOF'
<subject line>

<body — the why, not the what>
EOF
)"
```

4. If a pre-commit hook fails (this project runs Pint via `vendor/bin/pint --dirty --format
   agent` on PHP changes per its CLAUDE.md conventions, and may add a real pre-commit hook later),
   fix the underlying issue, re-stage, and commit again per Rule 7 — never skip it.

---

## Step 5: Confirm and report

Once every group is committed, run `git log --oneline -N` (N = the number of commits just made)
and `git status --short` to confirm the working tree now matches expectations — nothing left
uncommitted except anything deliberately excluded in Step 2/3. Report the resulting commit list
to the user. Don't push, and don't offer to — that's always a separate, explicit request per
Rule 6, not a natural next step this skill proposes on its own.

---

## Principles

- A commit that bundles two unrelated concerns is worse than two smaller, correct commits — when
  genuinely in doubt, split further rather than merge for convenience.
- Never guess at a file's purpose when its actual content would settle the question outright —
  read before grouping, every time, not just for the files that look suspicious at a glance.
- Silence isn't neutral. An unasked question about a genuinely ambiguous file is a confident-
  sounding guess wearing the plan's authority; ask instead.
- The plan in Step 3 is not a formality to rush past — it exists so a wrong split gets caught
  before it's written into history, not after.
