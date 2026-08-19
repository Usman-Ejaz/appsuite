---
name: tech-engineering
description: >
  Umbrella orchestrator for the full engineering SDLC on this repo, chaining eight existing skills
  in order: `tech-discovery` -> `task-creator` -> `tech-implementation` -> `document` ->
  `document-api` -> `code-review` -> `git-commit` -> `git-open-pr`. Use this whenever the developer
  wants to take a feature, use case, or problem all the way from idea to an open PR in one tracked
  run — phrasing like "let's build this end to end", "run the full process for X", "take this from
  idea to PR", "start the engineering flow for X", or "walk me through building this properly" —
  even when they don't say "/tech-engineering" or name this skill directly. A single run can span
  days, so it never relies on conversation memory alone: it keeps a durable, file-backed state file
  per task, reloads it before assuming a fresh start, and never advances to the next phase without
  the developer's explicit approval — even when that phase's own skill already got its own internal
  sign-off. Always carries an up-to-date master checklist of the 8 phases, on top of whatever
  checklist the currently active phase's own skill is maintaining. This skill never substitutes its
  own judgment for a phase's internal rules (it doesn't shortcut `code-review`'s lint/PHPStan/test
  run, commit on `tech-implementation`'s behalf, or push on `git-open-pr`'s behalf) — it only
  sequences hand-offs between the eight member skills and tracks the journey between them.
---

# Tech Engineering: The Full SDLC, One Tracked Run

You are the conductor, not the performer. Every phase below is already a full skill with its own
rules, steps, and approval gates — this skill's only job is deciding when to start the next one,
carrying context forward so the developer never repeats themselves, and making sure a run that
spans hours or days never loses its place. Nothing about *how* a phase does its job is repeated or
overridden here; read that phase's own `SKILL.md` when it's running.

The eight phases, in order:

| # | Phase | Skill | What it produces |
|---|-------|-------|-------------------|
| 1 | Discovery | `tech-discovery` | An approved development plan |
| 2 | Ticket | `task-creator` | A Linear ticket for the approved plan |
| 3 | Implementation | `tech-implementation` | Reviewed, tested code on a branch |
| 4 | Product docs | `document` | Updated product-facing docs (if applicable) |
| 5 | API docs | `document-api` | Updated API documentation (if applicable) |
| 6 | Self-review | `code-review` | A pre-PR self-review report |
| 7 | Commit | `git-commit` | The work landed in local history |
| 8 | Open PR | `git-open-pr` | An open pull request |

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** a pause point developers keep returning to (e.g. always stopping after Phase 3
  to test manually before documenting) or a phase that keeps getting skipped for the same reason
  (e.g. Phase 2 skipped because a ticket already existed) is a pattern worth recognizing across
  runs, not re-deriving each time.
- **Unfamiliar means:** don't guess whether a phase genuinely doesn't apply to this task — if it's
  not obvious from the state file or the conversation, ask rather than silently marking it skipped.
- **Learnings file:** `.claude/knowledge-base/skills/tech-engineering.md` — recurring skip reasons,
  common resume gaps (state recorded as "in progress" with no real evidence the phase started),
  and phrasing patterns for phase-transition checkpoints the developer has approved before.
- **Propose, don't silently apply a new heuristic:** if the fixed 8-phase sequence genuinely
  doesn't fit a recurring shape of task (e.g. a pure bug fix that never needs `document`/
  `document-api`), propose a named variant rather than quietly reordering or dropping phases run to
  run.

---

## Rules (non-negotiable)

1. **Never advance to the next phase without the developer's explicit approval.** This holds even
   when the current phase's own skill already got its own internal sign-off (`tech-discovery`'s
   plan approval, `tech-implementation`'s code-review stop, `git-open-pr`'s draft approval) —
   finishing a phase and moving the whole SDLC forward are two different decisions, and this skill
   always asks the second one separately.
2. **State is durable and file-backed, not conversational.** Every phase transition, approval, and
   piece of accumulated context (ticket ID, branch name, PR URL) is written to this task's state
   file immediately — never left implicit in the chat, since the conversation may get compacted or
   a new session may pick this up days later with none of today's context.
3. **Always resume from state, never restart silently.** Before starting Phase 1, check for an
   existing in-progress state file for this task. If one exists, load it and confirm the resume
   point with the developer — don't begin again from Phase 1 just because this is a new
   conversation.
4. **Two checklists, always both current.** The 8-phase master checklist (this skill's own) and
   whatever checklist the active phase's own skill is maintaining (e.g. `tech-implementation`'s
   `TodoWrite` list) are both kept visible and up to date. Neither replaces the other.
5. **Skipping a phase is allowed, but never silent.** If a phase genuinely doesn't apply (a ticket
   already exists, no product-facing behavior changed, no API surface touched), the developer says
   so explicitly and the state file records it as `skipped: <reason>` — never left blank or assumed.
6. **This skill never substitutes its judgment for a phase's own internal rules.** It doesn't
   shortcut `code-review`'s lint/PHPStan/test run, doesn't stage or commit on `tech-implementation`'s
   or `git-commit`'s behalf, and doesn't push or create a PR on `git-open-pr`'s behalf. It only
   sequences and tracks; each phase's own skill still owns everything that happens inside it.
7. **When a phase's own skill would normally hand off directly to the next named skill** (e.g.
   `tech-discovery`'s own Step 7 hands off to `tech-implementation`; `tech-implementation`'s own
   Step 6 hands off to `git-commit`), that hand-off is intercepted here: under this umbrella, a
   phase finishing means "return control to `tech-engineering`," not "invoke the next skill
   directly." The phase-transition checkpoint (the shared step below) always runs in between.
8. **Going backward is allowed, but it's a recorded decision, not a silent do-over.** If something
   learned in a later phase invalidates an earlier one (implementation reveals the discovery plan
   missed a case), reopening that earlier phase is legitimate — but the state file records why, and
   every phase in between gets flagged for re-check rather than assumed still valid.
9. **Never guess which phase to resume at, or which skill to invoke next.** If the state file's
   recorded phase doesn't match what the conversation or the repo actually shows (e.g. it says
   "Phase 4 in progress" but there's no sign `document` was ever started), ask rather than picking
   one silently.

---

## The state file

One per task, at `.claude/knowledge-base/tech-engineering/<task-slug>.md`. Created at the start of
Phase 1 (or on resuming, if genuinely missing), updated after every phase transition and every
approval — never batched up for later.

```markdown
---
task: <task-slug>
title: <human-readable title>
started: <date>
last_updated: <date>
current_phase: <1-8>
status: in_progress | paused | done
---

## Master checklist

- [x] 1. Discovery — approved <date>. Plan: <one-line summary or link to where it's recorded>
- [x] 2. Ticket — ENG-XXXX
- [ ] 3. Implementation — in progress, branch `<branch-name>`
- [ ] 4. Product docs — not started
- [ ] 5. API docs — not started
- [ ] 6. Self-review — not started
- [ ] 7. Commit — not started
- [ ] 8. Open PR — not started

## Context accumulated so far

- **Problem:** ...
- **Approved approach:** ...
- **Linear ticket:** ENG-XXXX
- **Branch:** ...
- **PR:** ...
- (anything else a later phase's skill would otherwise have to ask the developer to repeat)

## Log

- <date> — Phase 1 approved.
- <date> — Phase 2: created ENG-XXXX.
- <date> — Phase 3 started, paused mid-implementation for the day.
```

The task-slug is short and kebab-case (e.g. `cms-content-block-versioning`), confirmed with the
developer at kickoff — it's also a reasonable default to suggest for the branch name in Phase 3,
though `tech-implementation`'s own branch-confirmation step still applies in full.

---

## Step 0: Resolve the task and load or create state

Per Rule 3: check `.claude/knowledge-base/tech-engineering/` for a state file matching this task
before doing anything else — by an explicit task-slug/ticket/branch the developer mentioned, or by
asking if it's unclear whether this is a new run or a continuation of one already in progress.

- **A matching in-progress file exists:** read it in full. Present the master checklist and the
  accumulated context as a short recap (not a re-interrogation — the developer shouldn't have to
  re-explain what's already recorded), then confirm the resume point per Rule 9: does
  `current_phase`'s status actually match reality (e.g. if it claims Phase 3 is "in progress," is
  there actually a branch with commits, or does this need a genuine restart of that phase)? Resolve
  any mismatch with the developer before proceeding.
- **Nothing matches, genuinely new task:** ask for a short title and derive a task-slug from it
  (confirm the slug, don't just assume). Create the state file with `current_phase: 1`,
  `status: in_progress`, and an empty Context/Log section.

**Done when:** a state file is open (freshly created or loaded) and its `current_phase` reflects
where this run actually is, confirmed with the developer.

---

## The phase-transition checkpoint (used after every phase)

This is the one recurring shape every phase below shares — defined once here rather than repeated
eight times.

1. **Hand off** to the phase's own skill via the `Skill` tool, passing along everything already in
   the state file's Context section so the developer isn't asked to repeat it.
2. **Let it run its own full flow**, including its own internal steps and approval gates — this
   skill does not intervene in how that phase does its job (Rule 6). Per Rule 7, if that skill's own
   instructions describe handing off to the next named skill in this chain, that hand-off doesn't
   happen automatically here — control returns to `tech-engineering` first.
3. **Update the state file** the moment the phase's own skill finishes: mark it `[x]` on the master
   checklist with a one-line result, append any new context (ticket ID, branch name, PR URL,
   whatever that phase produced) to the Context section, and add a dated Log entry.
4. **Show the refreshed master checklist** to the developer — the full 8 items, not just the one
   that just finished — so the whole journey stays visible, not only the current step.
5. **Ask explicitly** what to do next, via `AskUserQuestion`:
   - **Proceed to the next phase** — only on this does Step 1 of this checkpoint run again for the
     next phase in sequence.
   - **Pause here** — stop the run cleanly; `status: paused` in the state file. Nothing more happens
     until the developer picks this back up (Step 0 handles the resume, whenever that is).
   - **Skip the next phase** — only with a stated reason (Rule 5); record `skipped: <reason>` on
     that phase's checklist line and move the pointer past it, then ask again whether to proceed
     into the phase after that.
   - **Go back to an earlier phase** — per Rule 8, ask for the reason, record it in the Log, flag
     every phase in between as needing re-check on the master checklist, and re-run the
     phase-transition checkpoint starting from the reopened phase.

**Done when:** the state file reflects the phase that just finished, the master checklist has been
shown, and the developer has made an explicit choice about what happens next.

---

## Phase 1: Discovery — `tech-discovery`

Hand off with the developer's original problem/use-case description. `tech-discovery` traces the
real flow, brainstorms genuine design forks with the developer, and stops for plan approval on its
own — that approval is also this phase's completion signal. Capture the approved plan (or a clear
reference to where it's recorded in the conversation) into the state file's Context section before
running the checkpoint.

## Phase 2: Ticket — `task-creator`

Hand off with the approved plan from Phase 1. `task-creator` figures out the right ticket type and
delegates to the matching type-skill, which drafts, confirms, and creates the Linear ticket on its
own. Capture the resulting ticket ID into Context — every phase from here on (especially Phase 8's
PR draft) needs it.

Skippable per Rule 5 when a ticket already exists for this work — record its ID directly into
Context and the reason ("ticket already existed: ENG-XXXX") on the checklist line, without
skipping the recording itself.

## Phase 3: Implementation — `tech-implementation`

Hand off with the approved plan and the ticket ID. `tech-implementation` confirms the branch, keeps
its own implementation checklist, and stops hard for code review before its own single test-suite
run — all of that happens inside this phase, not as separate phases here. Capture the branch name
(and, once tests pass, that fact) into Context.

This is the phase most likely to genuinely span multiple days — Rule 2's file-backed state exists
largely for this phase's sake. Pausing mid-implementation is completely normal; the state file's
Log should say roughly where implementation stopped, not just that Phase 3 is "in progress."

## Phase 4: Product docs — `document`

Hand off with the branch name and a summary of what changed. `document` maps the change to a
Domain/module/feature location and writes or updates the relevant product-facing page(s).

Skippable per Rule 5 when nothing product-facing changed (an internal refactor, a pure
infrastructure change) — record the reason.

## Phase 5: API docs — `document-api`

Hand off with the branch name. `document-api` adds or updates API documentation for whatever
Controllers/Requests/Resources/Enums this branch touched.

Skippable per Rule 5 when no public API surface was touched — record the reason.

## Phase 6: Self-review — `code-review`

Hand off with the branch name. `code-review` diffs against the target branch, runs this repo's
lint/PHPStan/test scripts, and produces a severity-ranked report and a PR description draft.
Capture whether it came back clean, and any HIGH/MEDIUM issues that got fixed as a result, into
Context.

Note: `tech-implementation` (Phase 3) already ran its own test-suite gate before code was approved
there. This phase is a second, independent pass specifically before opening the PR — it isn't
redundant with Phase 3's check, it's this repo's standard practice before every PR (see
`code-review`'s own purpose).

## Phase 7: Commit — `git-commit`

Hand off with nothing more than "the working tree is ready" — `git-commit` reads the actual diff
itself, proposes its own grouping, and gets its own approval before staging anything. Capture the
resulting commit list into Context.

## Phase 8: Open PR — `git-open-pr`

Hand off with the branch name, the target branch (ask if not already decided earlier in this run),
and — critically — the Linear ticket ID from Phase 2's Context, so `git-open-pr`'s own draft
doesn't have to leave that field as an open question. Capture the resulting PR URL into Context.
This is the last phase; there's no "next phase" question after it, only Step Final below.

---

## Step Final: Close out

Once Phase 8's checkpoint would normally ask "proceed to the next phase," there isn't one — instead
mark `status: done` in the state file, show the final master checklist (all 8 phases resolved,
`[x]` or `skipped: <reason>`), and report the PR URL. This run is complete; a `chore`/follow-up
task discovered along the way is a new run of this skill, not a reopening of this one.

**Done when:** the state file says `done`, the full master checklist has been shown one last time,
and the developer has the PR URL in hand.

---

## Principles

- A multi-day process forgets nothing on its own — the state file is what remembers, not the
  conversation. If it isn't written down, it didn't happen as far as a resumed run is concerned.
- Finishing a phase and deciding to keep going are two different moments. Conflating them is how a
  developer ends up three phases further than they meant to be.
- The master checklist exists so the developer never has to ask "wait, where are we?" — show it
  at every transition, not just on request.
- Orchestration is not license to shortcut. Every phase's own rules apply in full, every time,
  regardless of how many times this chain has run before.
