---
name: task-creator-discovery
description: >
  Create a Linear ticket for a Discovery or research task, when there is a real open question
  and no clear solution yet. Uses the agile spike shape: a specific question, a time-box, an
  approach, and a required deliverable (findings, recommendation, follow-up tickets). Use this
  when the user wants to explore, investigate, or research something before committing to a fix
  or a feature. Trigger phrases: "we need to figure out if...", "let's investigate X", "research
  whether Y is possible", "spike on X", "not sure how to approach this yet", "explore options for
  X", or when they name this skill directly. If the solution is already clear and just needs
  building, use task-creator-feature or task-creator-improvement instead. For a bug report, use
  task-creator-bug. In most cases, just use /task-creator (the umbrella skill), and it will pick
  the right one of these 4.
  Usage: /task-creator-discovery [optional brief description]
---

# Discovery Ticket

You are a senior developer writing a Linear ticket for a discovery or research task. Your job:
gather what is needed, draft the ticket using the exact template below, show it for review, then
create it in Linear.

See [Shared rules](../shared/task-creator-shared.md) first. It covers tense, size check, labels, team
defaults, editing an existing ticket, and the review-before-create rule. This file only covers
what is specific to a Discovery ticket.

A discovery ticket without a clear question and a time-box tends to stay open forever. That is
the main failure mode this template exists to prevent.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism. It is active through every
step below, not just once. Specific to this skill:

- **Wider means:** the same open question might already be partly answered in an existing
  knowledge base file or a past ticket. Worth a quick search before treating it as brand new.
- **Unfamiliar means:** a vague question like "look into performance" that doesn't have a real
  yes/no or a real decision attached. Push back and help the user narrow it before drafting,
  rather than writing a vague ticket that can't be marked done.
- **Learnings file:** `.claude/knowledge-base/skills/task-creator-discovery.md`. Time-box
  choices that worked well for similar questions, recurring "why this matters" patterns.
- **Propose, don't silently apply:** if the same template tweak gets asked for more than once,
  propose adding it to Step 2's template instead of reapplying it quietly or asking again.

---

## Rules (non-negotiable)

1. **The question must be specific.** Not "look into X". Instead, something with a real answer,
   like "can our current queue setup handle 3x the current webhook volume without a redesign?".
   If the user gives a vague topic, ask a follow-up to turn it into a real question.
2. **Time-box is required.** Pick one: half day, 2 days, or full sprint. This caps the work and
   forces a clear stopping point.
3. **Definition of done is required**, and stays as the 4 checkboxes below. A discovery ticket
   is not done just because time ran out, it needs an actual answer or an honest "still unknown"
   plus a follow-up ticket.
4. **Exact template.** Use the sections below in this order, with these exact headings.

---

## Step 1: Gather information

If `$ARGUMENTS` is given, use it as a starting point for the title and the question.

Ask for anything not already clear from the arguments or the conversation. You need:

- **Title**: see shared Rule 1 for the format. Frame it as the question or topic. Example:
  "Evaluate whether Domains/CMS needs a content-versioning system"
- **Question to answer**: the specific uncertainty
- **Why this matters**: what decision depends on the answer
- **Time-box**: half day, 2 days, or full sprint
- **Approach**: what will be tried (read code, prototype, benchmark, ask stakeholders)
- **Out of scope**: what this discovery will not try to answer

If this ticket is being created after the research already happened (a retroactive discovery
summary), the `Question to answer` and `Approach` sections still get written in
pre-implementation tense, per shared Rule 2. The real findings go in a comment right after the
ticket is created, per shared Rule 5. Don't put the findings in the description itself.

Before drafting, also check for an existing ticket that might already cover this question
(shared Rule 8).

---

## Step 2: Draft the ticket

Build the description with this exact template. Do not add the title at the top.

```
## Discovery

**Question to answer**
[The specific thing we do not know.]

**Why this matters**
[What decision depends on the answer.]

**Time-box**
- [ ] Half day
- [ ] 2 days
- [ ] Full sprint

**Approach**
[What will be tried: read code, prototype, benchmark, ask stakeholders.]

**Out of scope**
[What this discovery will NOT try to answer.]

---

## Definition of done
- [ ] Question answered
- [ ] Findings documented (in this ticket or a linked doc)
- [ ] Recommendation made
- [ ] Follow-up ticket(s) created, if needed
```

This template is already small. There is no separate small/full version.

---

## Step 3: Review and confirm

Show the full draft (title and description). Ask the user to confirm, adjust, or add anything.
Do not create the ticket until they approve. See shared Rule 7.

---

## Step 4: Create the ticket in Linear

Once approved, create it with the Linear MCP tool. See [Shared rules](../shared/task-creator-shared.md)
for team, assignee, priority, project, and state defaults, and the `Discovery` label.

After creating, output the Linear URL.

If this was a retroactive discovery ticket (per Step 1), post the findings as a comment right
after creating it, following shared Rule 5.
