---
name: tech-discovery
description: >
  Run a tech & architecture discovery session with the developer to find the best solution for a
  use case, problem, or new feature — before any code is written. Use this whenever the developer
  wants to think through an approach, discuss architecture, or figure out how to build something
  new — including phrasing like "let's figure out how to build X", "what's the best way to
  approach Y", "before we build this, let's think it through", "how should we architect Z", "I
  have an idea for a feature, can we discuss it", or "walk me through what it'd take to do X" —
  even when the developer doesn't say "/tech-discovery" or name this skill directly. The developer
  describes the problem and maybe the flow as they understand it; this skill traces the actual
  current flow in the codebase to verify or correct that understanding, asks questions where
  business intent isn't decidable from code, and only after full agreement produces a development
  plan (scope of work, related files, checklist). Never writes or edits code — discovery only. On
  explicit approval to proceed, hands off to `tech-implementation`. Not `learn-domain` (a general
  domain briefing with no plan or design decisions — this skill uses it as an input when useful)
  and not `apply-permissions` (a permissions-specific planning flow) — this is the general-purpose
  discovery-to-plan skill for everything else.
---

# Tech Discovery: Architecture & Approach, Before Any Code

You are running a discovery conversation with the developer, not writing code. The point is to
leave with a plan both of you actually agree on — grounded in how the system really works today,
not in how the developer remembers it working, and not in whichever solution first comes to mind.
This is different from its sibling skills:

- Not `learn-domain`: that produces a general engineering briefing with no plan or decision. This
  skill reuses that briefing as a fast orientation input (Step 1) when one exists, but its own job
  is producing a plan for one specific problem.
- Not `apply-permissions`: that is a fixed, rule-heavy planning flow for one specific concern
  (authorization — spatie/laravel-permission roles in `Domains/Identity` plus the Team-membership
  policies in root `app/`). This skill is the general-purpose version for everything else — a new
  feature, a redesign, an open-ended "how should this work."
- Not `tech-implementation`: that skill writes the code. This skill stops at an approved plan and
  hands off.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** a design fork that came up for one feature (e.g. "sync vs. async", "where does
  this validation belong") often recurs on the next one in the same domain (module) — worth
  recognizing as a standing question for that domain rather than re-deriving from scratch each
  time.
- **Unfamiliar means:** a business rule the developer states as fact but that isn't visible
  anywhere in the code — verify against the actual flow (Step 2) before it becomes a plan
  assumption; a plan built on a misremembered rule sends `tech-implementation` down the wrong path
  with full confidence.
- **Learnings file:** `.claude/knowledge-base/skills/tech-discovery.md` — recurring design forks
  per domain, project-wide conventions confirmed while tracing a flow (worth feeding into
  `learn-domain`'s own briefing rather than staying private to this skill), and phrasing/plan
  shapes the developer has approved before.
- **Propose, don't silently apply a new heuristic:** if a genuinely new kind of design fork or
  plan section comes up that doesn't fit Step 5's structure, say so and propose the addition
  rather than quietly reshaping the plan template this run only.

---

## Rules (non-negotiable)

1. **No code changes.** This skill never uses `Edit`, `Write`, or `NotebookEdit` on the codebase.
   Discovery only — the deliverable is a plan discussed and approved in chat, not a file on disk,
   unless the developer explicitly asks for it to be saved somewhere.
2. **Trace the real flow before proposing anything.** A plan built on the developer's description
   of the flow, unverified against the actual code, risks being built on a wrong premise — Step 2
   exists precisely to catch that before it reaches Step 5.
3. **A real design fork gets worked through with the developer, not resolved silently.** If more
   than one technically-sound approach exists, that's a brainstorming moment (Step 3), not a
   judgment call to make alone and present as the only option.
4. **The plan isn't final until the developer explicitly approves it.** A "sounds good, but..." is
   feedback to incorporate and re-present, not a green light to hand off.
5. **Hand off to `tech-implementation` exactly once, only after approval.** Don't hand off
   speculatively in case the answer turns out to be yes.

---

## Step 0: Capture the problem

Read the developer's description of the problem, use case, or feature — and, if they gave one,
their understanding of the current flow. Treat that flow description as a starting hypothesis to
verify in Step 2, not as settled fact; developers describing a flow from memory routinely miss an
edge case, a second entry point, or a step that changed since they last touched it.

If it's unclear which part of the system is even involved — which Domain module
(`Domains/Auth`, `Domains/CMS`, `Domains/Core`, `Domains/Ecommerce`, `Domains/Identity`,
`Domains/Shared`) or the cross-cutting Teams code in root `app/` — ask before going further,
rather than starting to trace code on a guess about scope.

**Done when:** the problem is understood well enough to know which domain(s)/flow(s) to trace next.

---

## Step 1: Orient — reuse existing knowledge first

Check whether a current knowledge-base briefing already exists for the relevant domain(s):

```bash
ls .claude/knowledge-base/domains/<domain-slug>.md 2>/dev/null
```

If it exists, check freshness the same way `learn-domain` does: read its `generated_at_commit`
frontmatter and diff against `HEAD` for that domain's folder. If it's missing or stale, invoke
`/learn-domain <Domain>` (or read the domain directly if that's faster for this specific problem's
scope) before going further — starting Step 2 without this orientation means re-discovering
project-wide conventions from scratch that a fresh briefing would hand you immediately.

**Done when:** you have a working map of the relevant domain(s) — from a fresh briefing or direct
reading — sufficient to know where to look for the specific flow in Step 2.

---

## Step 2: Trace the actual current flow

Read the real controllers, requests, services, jobs, and models involved in this specific use
case, end to end — not just the domain in general. Note concrete file paths and line references as
you go; these feed directly into Step 5's plan.

Ask the developer targeted questions wherever the business intent genuinely isn't decidable from
the code alone (e.g. "should this be scoped per-team or per-account", "what should happen when X
is empty today") — use `AskUserQuestion` for closed choices, plain conversational questions for
open-ended ones. Don't ask about anything the code already answers unambiguously.

**Done when:** the current flow (or its absence, for a genuinely new feature) is understood from
the code itself, with concrete file:line references, and every question the code couldn't answer
has been put to the developer.

---

## Step 3: Explore the solution space — when there's a real fork

If more than one technically-sound approach exists for this problem — a different data-model
shape, sync vs. async, which layer owns a check — don't pick one silently and present it as the
only option. Use `superpowers:brainstorming` to work through the trade-offs together with the
developer.

Skip this step when the right approach is genuinely obvious — extending something that already has
one established, working pattern elsewhere in this codebase doesn't need a brainstorming session;
that capability exists for real forks in the road, not busywork on settled questions.

**Done when:** either a genuine design fork has been worked through with the developer to a chosen
approach, or it's been confirmed there wasn't a real fork to begin with.

---

## Step 4: Surface constraints and open questions — always, even when nothing came up

Explicitly check, and say so either way: did tracing the flow turn up a constraint, edge case, or
existing convention that changes what the plan should look like? Bring each one to the developer as
a specific finding or question, not a vague "anything else to flag?" If genuinely nothing came up,
say that plainly rather than manufacturing a finding to fill the step.

**Done when:** every genuine constraint or open question found while tracing the flow has been
surfaced and resolved, or the step has explicitly concluded there were none.

---

## Step 5: Write the development plan

Write the plan as real, rendered markdown per [Output formatting](../shared/output-formatting.md)
— never wrapped in a single code fence. Structure:

- **Objective** — the problem being solved, in one or two sentences.
- **Current Flow** — what happens today, with concrete file:line references from Step 2 (or "no
  existing flow" for a genuinely new feature).
- **Proposed Approach** — the chosen design, including the reasoning behind it, especially for any
  fork resolved in Step 3.
- **Scope of Work** — a numbered list of the concrete changes needed, grouped logically.
- **Related Files** — existing files that will change, and new files that will be created.
- **Checklist** — an implementation checklist mirroring Scope of Work, written so it can be reused
  directly as `tech-implementation`'s progress checklist without re-deriving it.
- **Open Questions / Risks** — anything still uncertain, or a risk worth the developer knowing
  about before approving.
- **Test Plan (outline)** — what should be verified once built; `tech-implementation` owns actually
  running it.

**Done when:** a complete, concrete plan exists with real file paths, not placeholders, covering
everything decided in Steps 2-4.

---

## Step 6: Get explicit approval

Present the plan and stop. Do not touch a single file. This holds even if every prior step felt
completely unambiguous — the plan itself is the checkpoint, not a formality to rush past. If the
developer asks for changes, revise the plan and present it again rather than treating a partial
"sounds good, but..." as approval.

**Done when:** the developer has given explicit, unambiguous approval to proceed with
implementation, or has asked for changes that this step then incorporates before asking again.

---

## Step 7: Hand off to tech-implementation

Only once Step 6's approval is explicit: invoke `tech-implementation` via the `Skill` tool, passing
along the approved plan (or a clear reference to where it was presented in this conversation) so
the developer doesn't have to repeat it.

If the developer wants this plan tracked as a Linear ticket first, point them at `task-creator` —
don't create the ticket yourself as part of this skill.

If the developer declines to implement now, or wants to revisit later, stop here. Don't hand off
speculatively.

**Done when:** either `tech-implementation` has been invoked with the approved plan, or the
developer has explicitly indicated they're not proceeding right now.

---

## Principles

- A plan grounded in the real code beats one grounded in how the developer remembers the flow
  working — verify, don't transcribe.
- A silently-chosen design is a guess wearing the plan's authority; a real fork gets brainstormed,
  not decided alone.
- The approval step is not a formality. Nothing gets built on an assumed "yes."
