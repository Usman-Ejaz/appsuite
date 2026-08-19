---
name: task-creator
description: >
  Create a new Linear ticket for this project. This is the umbrella skill, it figures out which
  ticket type fits (New Feature, Improvement, Bug, or Discovery) and hands off to the right
  skill. Use this whenever the user asks to create a ticket, file a task, write up a Linear
  issue, log a bug, request an improvement, or start a research/discovery ticket, without
  already knowing which type they want. Phrases like "create a ticket for this", "file a Linear
  task for X", "log this as a bug", "write up a ticket", or "add this to Linear", even when the
  user doesn't say "/task-creator" or name a specific type. If the user already knows the exact
  type, they can call task-creator-feature, task-creator-improvement, task-creator-bug, or
  task-creator-discovery directly instead, but most people should just start here.
  Usage: /task-creator [optional brief description]
---

# Task Creator (Umbrella)

You pick the right ticket type, then hand off. You do not gather ticket details or draft
anything yourself. Each type skill owns its own full flow (gather, draft, confirm, create, edit).

See [Shared rules](../shared/task-creator-shared.md) for the rules every type skill follows.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism. It is active through every
step below, not just once. Specific to this skill:

- **Wider means:** a phrase that turned out ambiguous once is likely to come up again. Worth
  remembering which phrases actually needed a follow-up question.
- **Unfamiliar means:** a request that doesn't clearly match any of the 4 types (for example, a
  pure chore or a documentation-only ticket). Ask rather than force it into the closest type.
- **Learnings file:** `.claude/knowledge-base/skills/task-creator.md`. Phrases that were
  ambiguous and how they got resolved, so the guess gets better over time. This is also where the
  Linear team resolved once under [Shared Rule 6](../shared/task-creator-shared.md#rule-6-team-assignee-priority-project-defaults)
  gets remembered, since all 5 task-creator skills share this one file.
- **Propose, don't silently apply:** if a new keyword pattern comes up more than once, propose
  adding it to Step 1's guess list instead of only handling it ad hoc each time.

---

## Rules (non-negotiable)

1. **Never draft a ticket yourself.** Your only job is picking the type and handing off. All
   drafting happens inside the type skill.
2. **Never guess silently on a genuinely unclear request.** If the type isn't clear, ask.
3. **One hand-off per request.** Don't ask the type skill to also loop back through you.

---

## Step 1: Guess the type

Read `$ARGUMENTS` and the conversation context. Look for signal words:

- **Bug**: "bug", "broken", "not working", "error", "crash", "used to work", "throws", "500"
- **New Feature**: "add", "build", "new", "we need a way to", "create a feature for"
- **Improvement**: "improve", "faster", "better", "simplify", "instead of", "change how X works"
- **Discovery**: "not sure", "explore", "research", "spike", "investigate", "figure out if"

Watch for symptom-only reports. This is the trickiest case, and it's easy to get wrong. A
request like "the totals look wrong" or "results seem off" describes a symptom, not a cause.
It could mean:

- **Bug**: the calculation is broken, it should already give the right number
- **Improvement**: the calculation works as built, but the logic itself should change

Words like "broken", "crash", "error", or "throws" point to Bug. Words like "should", "instead
of", or a clear description of what the right answer should be point to Improvement. A symptom
on its own, with neither of those, is genuinely unclear. Treat it as unclear (go to Step 2), do
not default to Bug just because something is described as wrong.

If one type clearly fits, say which one in one short line, then hand off (Step 3). No need to
wait for a reply, this is a confirmation, not a question.

---

## Step 2: Ask if it's genuinely unclear

If no type stands out, two types seem equally likely, or it's a symptom-only report per Step 1,
ask with `AskUserQuestion`. Give all 4 types as options, each with a one-line description:

- **New Feature**: something that doesn't exist yet
- **Improvement**: a change to something that already exists
- **Bug**: something broken today
- **Discovery**: an open question, no clear solution yet

Wait for the answer before moving to Step 3.

---

## Step 3: Hand off

Use the `Skill` tool to invoke the matching skill:

| Type | Skill |
|---|---|
| New Feature | `task-creator-feature` |
| Improvement | `task-creator-improvement` |
| Bug | `task-creator-bug` |
| Discovery | `task-creator-discovery` |

Pass along the original request and anything already gathered in this conversation, so the type
skill doesn't have to ask the user to repeat themselves.
