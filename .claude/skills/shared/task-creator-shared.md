# Task Creator: Shared Rules

Referenced by all 5 task-creator skills (`task-creator`, `task-creator-feature`,
`task-creator-improvement`, `task-creator-bug`, `task-creator-discovery`), the same way every
skill in this repo references [Auto Learning](auto-learning.md).

This file holds the rules that are the same across all 5. It keeps them in one place so they
don't drift out of sync between skills as they get tuned. Each skill's own `SKILL.md` only has
the parts that are specific to its ticket type.

## Rule 1: Title format

- **Short, one line.** Linear list views cut off long titles, keep it readable at a glance.
- **Verb-first, imperative.** Same tense as Rule 2, a title is not exempt from that rule. Write
  it as an instruction, not a name. "Add a resend action for expired team invitations", not
  "Team invitations" or "Expired invitations can't be resent".
- **Specific, with real keywords.** Not "Fix bug" or "Improve performance". Name the actual
  area and behavior. Rule 8's duplicate-ticket search depends on the title carrying real
  keywords, a vague title makes that search useless.
- **No type prefix.** Don't write "[Bug] ..." or "Feature: ...". The Linear label already
  carries the type (Rule 4), repeating it in the title is noise.
- **Never put the title in the description body.** Linear already shows the title there too.
  Repeating it in the description is noise.

Each type skill's own Step 1 gives a type-flavored example title, but the format rules above are
the same across all 4 types.

## Rule 2: Always write in pre-implementation tense

Use imperative or future tense, even if the work is already done.

- Good: "Add a way for team owners to revoke a pending invitation"
- Good: "This will let team owners see who last edited a CMS page"
- Bad: "Added invitation revocation..." (past tense)
- Bad: "This lets team owners see who last edited..." (describes it as already true)

This applies even to a retroactive ticket (one written after the work is done). The reader should
not be able to tell from the tone alone whether the ticket was written before or after the work.

If the work is already done and something needs to be said about the real outcome (what was
actually found, a scope change during the work), that goes in a comment after creation, not in
the description. See Rule 5.

## Rule 3: Check the size before drafting

Before writing the draft, decide:

- **Small**: one file or a few lines, no schema change, no real deploy risk.
- **Full**: anything bigger than that.

For a small ticket, drop the heavier sections. Keep only the core ones (Problem or
Current/Desired behavior, Scope, Acceptance criteria or Test cases). Each type skill below says
exactly which sections to drop for its own template.

If it is not obvious which size fits, ask the user in one short question rather than guessing.

## Rule 4: Apply the matching Linear label — after confirming it exists

| Skill | Label |
|---|---|
| `task-creator-feature` | `Feature` |
| `task-creator-improvement` | `Improvement` |
| `task-creator-bug` | `Bug` |
| `task-creator-discovery` | `Discovery` |

Unlike a workspace with a long-lived, known team, this project has no assumed history of these
labels already existing. Before applying one, check whether a matching label exists on the
resolved team (Rule 6) with `mcp__claude_ai_Linear__list_issue_labels` (`team: <resolved team>`,
`name: <label>`):

- **It exists:** apply it when creating the ticket, without asking further.
- **It doesn't exist:** tell the user and ask whether to create it now
  (`mcp__claude_ai_Linear__create_issue_label`, scoped to the resolved team) or proceed without a
  label. Don't silently create labels or silently skip them.

Do this check once per label the first time it's needed, and note in the learnings file (Rule 6)
once all 4 are confirmed to exist, so later runs don't re-check every time.

## Rule 5: Editing an existing ticket

If the user asks to update a ticket that any of these 5 skills already created:

1. Find the ticket by its Linear id or title.
2. Edit the relevant description section(s) directly, in place.
3. Also post a short comment on the ticket. Say what changed and why.
4. If the change is substantial, re-show the affected section for confirmation. Small wording
   tweaks don't need this.

Editing the description keeps it accurate and current. The comment keeps a record of what
happened and why, since the description itself always stays in Rule 2's pre-implementation tone
and won't say "this changed because...".

## Rule 6: Team, assignee, priority, project defaults

- **Team**: this workspace has no single known team to hardcode, so resolve it once rather than
  guessing:
  1. Check `.claude/knowledge-base/skills/task-creator.md` for a previously confirmed team.
  2. If none is recorded, call `mcp__claude_ai_Linear__list_teams` and ask the user to confirm
     which team these tickets should go to (offer the list if there's more than one obvious
     candidate).
  3. Record the confirmed team name in the learnings file per [Auto Learning](auto-learning.md)
     so every later run — in any of these 5 skills — reuses it without asking again. If the user
     later asks for a different team on a specific ticket, honor that for this ticket only; don't
     overwrite the remembered default without them saying so explicitly.
- **Assignee**: `me`, unless the user says otherwise.
- **Priority**: ask if not specified. 0 = None, 1 = Urgent, 2 = High, 3 = Medium, 4 = Low.
  Default to 3 (Medium) if the user doesn't mind.
- **Project**: ask if not clear from context. Search existing projects by name first, so the
  user can just confirm instead of typing it from scratch.
- **State**: `Todo` (default for new tickets).

## Rule 7: Review before create

Show the user the full draft (title and description) and wait for approval. Never create the
ticket in Linear before the user has confirmed it. After approval, create it and output the
Linear URL.

Show the draft as real, rendered markdown in the chat response, not wrapped in a code fence.
See [Output formatting](output-formatting.md) for why, this is the single most common way a
draft becomes hard to read.

## Rule 8: Check for an existing ticket first

Before drafting, search Linear for a ticket that might already cover this. Use
`mcp__claude_ai_Linear__list_issues` with `team: <resolved team from Rule 6>` and `query:` set to
2 or 3 key words from the title or problem.

- **No close match:** draft as normal, no need to mention the search.
- **A close match exists:** show it to the user (title, status, a link) and ask:
  - Continue anyway, as a separate new ticket
  - Link the new ticket to the existing one as related, then continue
  - Stop, the existing ticket already covers this

Do this once, early, before Step 2's draft. Don't skip it just because the request sounds new,
the same gap gets reported more than once by different people.
