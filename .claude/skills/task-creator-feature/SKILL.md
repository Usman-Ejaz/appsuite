---
name: task-creator-feature
description: >
  Create a Linear ticket for a New Feature, something that does not exist yet. Uses a full
  feature template: Objective (Problem statement and Proposed solution), Implementation,
  Verification and acceptance criteria, Release plan. Use this when the user wants to add
  something new. Trigger phrases: "add a way to...", "build a new...", "we need a feature
  that...", "file a ticket for adding X", "create a feature ticket", or when they name this skill
  directly. If the change is to something that already exists, like making it faster or changing
  its behavior, use task-creator-improvement instead. For a bug report, use task-creator-bug. For
  research with no clear answer yet, use task-creator-discovery. In most cases, just use
  /task-creator (the umbrella skill), and it will pick the right one of these 4.
  Usage: /task-creator-feature [optional brief description]
---

# New Feature Ticket

You are a senior developer writing a Linear ticket for a feature that does not exist yet. Your
job: gather what is needed, draft the ticket using the exact template below, show it for review,
then create it in Linear.

See [Shared rules](../shared/task-creator-shared.md) first. It covers tense, size check, labels, team
defaults, editing an existing ticket, and the review-before-create rule. This file only covers
what is specific to a New Feature ticket.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism. It is active through every
step below, not just once. Specific to this skill:

- **Wider means:** a phrasing tweak the user asks for on one feature ticket might be a standing
  preference, not a one-off. Notice when a change would make sense on every future feature
  ticket, not just this one.
- **Unfamiliar means:** an ambiguous business or domain term hit in Step 1 — including which of
  this repo's modules (`Domains/Auth`, `Domains/CMS`, `Domains/Core`, `Domains/Ecommerce`,
  `Domains/Identity`, `Domains/Shared`, or the cross-cutting Teams code in root `app/`) the
  feature actually belongs to. Ask the user instead of guessing.
- **Learnings file:** `.claude/knowledge-base/skills/task-creator-feature.md`. The team's
  preferred title style, sections they often want expanded or trimmed, default project patterns.
- **Propose, don't silently apply:** if the same template tweak gets asked for more than once,
  propose adding it to Step 2's template instead of reapplying it quietly or asking again.

---

## Rules (non-negotiable)

1. **Problem before solution.** The Objective section leads with the pain or gap, then the fix.
2. **Scope must be explicit.** Always include both in-scope and out-of-scope bullets.
3. **Exact template.** Use the sections below in this order, with these exact headings.
4. **Rollback plan is required**, unless the size check (shared Rule 3) marks this as small.

---

## Step 1: Gather information

If `$ARGUMENTS` is given, use it as a starting point for the title and problem area.

Ask for anything not already clear from the arguments or the conversation. You need:

- **Title**: see shared Rule 1 for the format. Example: "Implement X", "Add Y"
- **Problem statement**: what is missing today, and why it matters
- **Proposed solution**: 1-2 sentences, no implementation detail
- **Affected area or module**: which module (or which part of root `app/`) this touches
- **Blast radius**: what breaks if this goes wrong
- **Implementation steps**: what needs to be built. Ask the user to list it roughly, you
  structure it.
- **Business rules**: any validations or non-obvious requirements
- **Scope**: what is in and out
- **Dependencies**: related tickets, migrations, config or environment changes this needs
- **Acceptance criteria**: how do we know it's done (Given/When/Then)
- **Rollback plan**: how to revert in production, unless this is a small ticket
- **Release considerations**: env vars, queue or config changes, anything else that has to
  happen at deploy time, if any

If the user already gave enough context, fill in what you can and confirm rather than asking
again. For a well-described feature, ask only what is missing.

Check the size (shared Rule 3). For a small feature, you can skip Implementation steps detail,
Dependencies, and Release plan, and keep the rest.

Before drafting, also check for an existing ticket that might already cover this (shared Rule 8).

---

## Step 2: Draft the ticket

Build the description with this exact template. Do not add the title at the top.

```
## Objective

**Problem statement**
[What is missing today, and why it matters. Describe the current pain, not the solution.]

**Proposed solution**
[1-2 sentences. No implementation detail here.]

- **Affected area / module:**
- **Blast radius:**

---

## Implementation

### Steps & changes
1.
2.
3.

### Business rules & validations
-

### Scope

**In scope**
-

**Out of scope**
-

### Dependencies & related tickets
-

---

## Verification & acceptance criteria

### Acceptance criteria
- [ ] Given... When... Then...
- [ ]

### Test cases
- [ ] Happy path:
- [ ] Error state:
- [ ] Edge case:

### Rollback plan
1.
2.

---

## Release plan

### Pre-release
- [ ] Code reviewed and approved
- [ ] Tests passing on staging

### Release
- [ ] Deployed to staging and verified
- [ ] Deployed to production

### Post-release
- [ ] Monitored for errors or regressions
- [ ] Ticket closed and linked PR attached
```

For a small ticket (shared Rule 3), drop the `Implementation` steps detail and the whole
`Release plan` section. Keep `Objective`, `Scope`, and `Verification & acceptance criteria`.

---

## Step 3: Review and confirm

Show the full draft (title and description). Ask the user to confirm, adjust, or add anything.
Do not create the ticket until they approve. See shared Rule 7.

---

## Step 4: Create the ticket in Linear

Once approved, create it with the Linear MCP tool. See [Shared rules](../shared/task-creator-shared.md)
for team, assignee, priority, project, and state defaults, and the `Feature` label.

After creating, output the Linear URL.
