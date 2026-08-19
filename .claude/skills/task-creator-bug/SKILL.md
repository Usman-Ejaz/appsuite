---
name: task-creator-bug
description: >
  Create a Linear ticket for a Bug, something that is broken today. Uses a bug report template:
  Description, Steps to reproduce, Expected vs Actual behavior, Environment or scope, Severity,
  Regression info, Fix verification steps. Use this when the user reports something not working.
  Trigger phrases: "X is broken", "this throws an error", "bug report for...", "file a bug for
  X", "this used to work and now doesn't", or when they name this skill directly. No
  Problem/Solution framing here, a bug is already a known problem. If the user wants to build
  something new, use task-creator-feature. To change existing working behavior on purpose, use
  task-creator-improvement. For research with no clear answer yet, use task-creator-discovery. In
  most cases, just use /task-creator (the umbrella skill), and it will pick the right one of
  these 4.
  Usage: /task-creator-bug [optional brief description]
---

# Bug Ticket

You are a senior developer writing a Linear ticket for a bug. Your job: gather what is needed,
draft the ticket using the exact template below, show it for review, then create it in Linear.

See [Shared rules](../shared/task-creator-shared.md) first. It covers tense, size check, labels, team
defaults, editing an existing ticket, and the review-before-create rule. This file only covers
what is specific to a Bug ticket.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism. It is active through every
step below, not just once. Specific to this skill:

- **Wider means:** the same bug shape (like a specific enum used unsafely, a specific race
  condition) might exist in sibling code the user hasn't mentioned — including the same pattern
  repeated across `Domains/Auth`, `Domains/CMS`, `Domains/Core`, `Domains/Ecommerce`,
  `Domains/Identity`, or `Domains/Shared`. Worth a quick check before writing the ticket as if
  it's isolated to one place.
- **Unfamiliar means:** an environment detail (which module, staging vs production) that isn't
  clear from the report. Ask rather than guess, since severity and scope depend on getting this
  right.
- **Learnings file:** `.claude/knowledge-base/skills/task-creator-bug.md`. Recurring bug shapes
  worth naming precisely, severity judgment calls that came up more than once.
- **Propose, don't silently apply:** if the same template tweak gets asked for more than once,
  propose adding it to Step 2's template instead of reapplying it quietly or asking again.

---

## Rules (non-negotiable)

1. **No Problem/Solution framing.** A bug is already a known problem. Skip straight to
   Description and repro steps.
2. **Steps to reproduce must be concrete.** Not "it doesn't work sometimes". A numbered list
   someone else could follow and see the same result.
3. **Severity is always set**, one of Critical, High, Medium, Low. If unsure, ask the user or
   use the same judgment `git-open-pr` applies to a PR's blast radius (who is affected, what
   breaks).
4. **Exact template.** Use the sections below in this order, with these exact headings.

---

## Step 1: Gather information

If `$ARGUMENTS` is given, use it as a starting point for the title and description.

Ask for anything not already clear from the arguments or the conversation. You need:

- **Title**: see shared Rule 1 for the format. Example: "Fix null crash on checkout when the
  cart's team context is missing"
- **Description**: what is wrong, in plain language
- **Steps to reproduce**: numbered, concrete
- **Expected behavior**: what should happen
- **Actual behavior**: what happens instead
- **Environment or scope**: which module is affected (`Auth`, `CMS`, `Core`, `Ecommerce`,
  `Identity`, `Shared`, or the root-app Teams code), and staging or production
- **Severity**: Critical, High, Medium, or Low
- **Regression**: did this work before, and if so, roughly when did it break
- **Fix verification steps**: how to confirm the fix once deployed

If the user already gave enough context (for example, they just found this bug while working on
something else), fill in what you can and confirm rather than asking again.

Check the size (shared Rule 3). Most bug tickets are already small. Only keep a
`Rollback plan` if the fix itself carries real deploy risk (a migration, a config change).

Before drafting, also check for an existing ticket that might already cover this (shared Rule 8).
This matters most for bugs, the same bug is often reported more than once by different people.

---

## Step 2: Draft the ticket

Build the description with this exact template. Do not add the title at the top.

```
## Bug report

**Description**
[What is wrong, plain language.]

**Steps to reproduce**
1.
2.
3.

**Expected behavior**
[What should happen.]

**Actual behavior**
[What happens instead.]

**Environment / scope**
- Module: [Auth / CMS / Core / Ecommerce / Identity / Shared / root app (Teams)]
- Environment: [staging / production]

**Severity**
- [ ] Critical
- [ ] High
- [ ] Medium
- [ ] Low

**Regression?**
- [ ] Yes, this worked before. Broke around: [date, version, or deploy if known]
- [ ] No, new behavior
- [ ] Unknown

---

## Verification

**Fix verification steps**
1.
2.

**Rollback plan**
[Only if the fix itself carries deploy risk. Most bug fixes don't need one. If not needed,
write "Not needed".]
```

---

## Step 3: Review and confirm

Show the full draft (title and description). Ask the user to confirm, adjust, or add anything.
Do not create the ticket until they approve. See shared Rule 7.

---

## Step 4: Create the ticket in Linear

Once approved, create it with the Linear MCP tool. See [Shared rules](../shared/task-creator-shared.md)
for team, assignee, priority, project, and state defaults, and the `Bug` label.

If severity is Critical or High, mention this to the user before creating, so they can raise the
priority field too if it isn't already set high.

After creating, output the Linear URL.
