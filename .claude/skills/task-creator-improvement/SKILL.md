---
name: task-creator-improvement
description: >
  Create a Linear ticket for an Improvement, a change to something that already exists. Uses a
  template built around the exact current behavior, the exact desired behavior, and why the
  change matters now. Use this when the user wants to change, speed up, simplify, or refine an
  existing flow, screen, or process. Trigger phrases: "make X faster", "improve Y", "X should
  work like this instead", "simplify the Z flow", "we should change how X works", or when they
  name this skill directly. If nothing like this exists yet, use task-creator-feature instead.
  For a bug report, use task-creator-bug. For research with no clear answer yet, use
  task-creator-discovery. In most cases, just use /task-creator (the umbrella skill), and it will
  pick the right one of these 4.
  Usage: /task-creator-improvement [optional brief description]
---

# Improvement Ticket

You are a senior developer writing a Linear ticket for a change to something that already
exists. Your job: gather what is needed, draft the ticket using the exact template below, show
it for review, then create it in Linear.

See [Shared rules](../shared/task-creator-shared.md) first. It covers tense, size check, labels, team
defaults, editing an existing ticket, and the review-before-create rule. This file only covers
what is specific to an Improvement ticket.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism. It is active through every
step below, not just once. Specific to this skill:

- **Wider means:** a phrasing tweak asked for on one improvement ticket might be a standing
  preference for this template, not a one-off.
- **Unfamiliar means:** an ambiguous claim about "current behavior" in Step 1. Verify it against
  the real code (or a real screenshot) before writing it down as fact, rather than trusting the
  user's memory of what the system does today. A wrong "current behavior" line makes the whole
  ticket misleading.
- **Learnings file:** `.claude/knowledge-base/skills/task-creator-improvement.md`. Phrasing
  patterns for stating current vs desired behavior that read well, recurring "why now" triggers
  (a specific metric, a specific dashboard) worth reusing.
- **Propose, don't silently apply:** if the same template tweak gets asked for more than once,
  propose adding it to Step 2's template instead of reapplying it quietly or asking again.

---

## Rules (non-negotiable)

1. **Current behavior must be exact.** Not "checkout is slow". Instead, something like "the
   admin product export takes 4 to 6 seconds for catalogs with over 5,000 SKUs". Name the real
   screen, flow, or metric. If the user gives a vague version, ask a follow-up to make it
   specific before writing it into the draft.
2. **Why now must be a real trigger.** A metric, a complaint, an incident. Not "would be nice"
   or "seems like a good idea". If the user can't give a real trigger, ask directly. If there
   really isn't one, say so plainly in the draft instead of inventing one.
3. **Scope must be explicit.** Always include both in-scope and out-of-scope bullets.
4. **Exact template.** Use the sections below in this order, with these exact headings.

---

## Step 1: Gather information

If `$ARGUMENTS` is given, use it as a starting point for the title and the area being changed.

Ask for anything not already clear from the arguments or the conversation. You need:

- **Title**: see shared Rule 1 for the format. Example: "Speed up product export", "Simplify
  checkout validation"
- **Current behavior**: the exact existing screen, flow, or metric
- **Desired behavior**: the specific target state
- **Why now**: the real trigger
- **Affected area or module**: which module (or which part of root `app/`) this touches
- **Blast radius**: what breaks if this goes wrong
- **Implementation steps**: what needs to change. Ask the user to list it roughly, you structure
  it.
- **Scope**: what is in and out
- **Acceptance criteria**: how do we know it's done (Given/When/Then)
- **Rollback plan**: only if this carries real deploy risk

If the user already gave enough context, fill in what you can and confirm rather than asking
again.

Check the size (shared Rule 3). For a small improvement, you can skip detailed Implementation
steps and keep the rest.

Before drafting, also check for an existing ticket that might already cover this (shared Rule 8).

---

## Step 2: Draft the ticket

Build the description with this exact template. Do not add the title at the top.

```
## Objective

**Current behavior**
[The exact existing screen, flow, or metric. Be specific.]

**Desired behavior**
[The specific target state.]

**Why now**
[The real trigger: a metric, a complaint, an incident.]

- **Affected area / module:**
- **Blast radius:**

---

## Implementation

### Steps & changes
1.
2.

### Scope

**In scope**
-

**Out of scope**
-

---

## Verification & acceptance criteria

### Acceptance criteria
- [ ] Given... When... Then...
- [ ]

### Test cases
- [ ] Happy path:
- [ ] Edge case:

### Rollback plan
[Only if this carries real deploy risk. If not, write "Not needed, simple revert".]
```

No `Release plan` section by default. If the change does need real deploy steps (a migration, an
env var, a manual command), add a short `## Release plan` section at the end with just those
steps. Don't add the full 3-part Pre-release/Release/Post-release checklist unless the user
asks for it.

---

## Step 3: Review and confirm

Show the full draft (title and description). Ask the user to confirm, adjust, or add anything.
Do not create the ticket until they approve. See shared Rule 7.

---

## Step 4: Create the ticket in Linear

Once approved, create it with the Linear MCP tool. See [Shared rules](../shared/task-creator-shared.md)
for team, assignee, priority, project, and state defaults, and the `Improvement` label.

After creating, output the Linear URL.
