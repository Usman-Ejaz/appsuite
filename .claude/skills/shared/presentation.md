# Presentation

How skills show drafts, plans, reports and checklists in chat, and how skill files themselves are written.

## Writing style (chat, templates, tickets, PRs, skill files)

Several readers are non-native English speakers, so:

- Prefer lists and checklists over long prose.
- Use simple English and short sentences.
- Max 3 nesting levels (5 only in rare cases).
- Avoid em dashes. Use a comma, colon or full stop instead. An occasional em dash is fine where it really reads better, never as a habit.
- Use `inline code` for paths, classes, commands and values.

## Response shape (every skill, every stop)

The developer often sees a long run of tool calls before the answer. The result must be the first thing they see, impossible to miss, and the same shape in every skill. Use this order whenever a skill stops: at the end of a run, and at every approval or decision point.

1. **Headline:** one `##` line with a status icon, the skill's short name, and the outcome in plain words. It is the first line of the response, never after logs or narration.
2. **Context line** (optional): one short line, e.g. `` Branch `feature-x` → `develop` · ENG-12 ``.
3. **Main content:** the result itself, as a table whenever there are two or more items with the same fields (findings, commits, pages, endpoints, files, settings). Each skill's own Output section defines its table.
4. **Needs your decision** (only when something is open): a `### Needs your decision` heading and a numbered list, one line per item. Omit the block when nothing is open.
5. **Checklist** (skills that have one): after the result, never before it, per [Checklists](#checklists).
6. **Next:** one bold line, e.g. **Next:** `/git-open-pr`, or the closed question the skill asks.

### Status icons

Use the same icon for the same meaning everywhere:

| Icon | Meaning |
|---|---|
| ✅ | Done, clean, or nothing needed |
| ⚠️ | Done, with warnings or non-blocking issues |
| ❌ | Blocked or failed: something must change first |
| ⏸️ | Waiting for the developer: a draft, plan or question to approve |
| 🔍 | Report only (an audit or a briefing), no change made |
| ⏳ | A background job is running (see [Waiting for a long job](#waiting-for-a-long-job)) |

### Formatting toolkit

Use rich markdown as much as it helps the reader scan. The developer's standing request is: icons, headings, tables, ordered and unordered lists, bold, `inline code`.

- **Headings** (`##` headline, `###` sections) split a response into parts the eye can jump between.
- **Tables** for any set of items that share fields. Keep cells short; one idea per cell.
- **Numbered lists** for steps, ordered actions and questions the developer must answer (so they can reply "1: yes, 2: no").
- **Bullets** for unordered facts.
- **Bold** for the one word or phrase that carries the meaning (a verdict, a blocker, a decision), not whole sentences.
- **Icons** from the table above, plus the severity icons 🔴 (blocking) 🟡 (should fix) 🔵 (minor) for review findings. Use them at the start of a headline, a table cell or a list item, never as decoration in the middle of prose.
- **`Inline code`** for every path, class, command, branch, field and value.

### Rules

- **Nothing-to-do is a real result** with its own clear headline: `## ✅ No issue found`, `## ✅ Product docs not required: <reason>`. Never an empty table, never a silent stop.
- **Summarise tool output, don't paste it.** Counts and check results are short vertical lists, one item per line with its icon (`- ✅ **PHPStan:** 0 new errors`), never squeezed into one `·`-separated line. Only failures get detail, as table rows with the exact error.
- **Links are clickable:** PRs, tickets and releases as `[#123: Title](url)` or `[ENG-12: Title](url)` using the URL the tool returned. Files as `` `path/to/File.php:42` ``.
- **Short text outside tables.** At most two or three short lines between headline and table. No narrative paragraphs.
- **Pre-existing or out-of-scope findings** go in their own small block titled `### Pre-existing (not from this change)`, after the main content, so they are never mixed with the result.
- **Drafts are labelled as drafts:** the headline says ⏸️ and what to approve, then the draft follows rendered (next section).
- **Commands to run later** (deployment steps, cleanup) go in one fenced `bash` block under a `### Run after deploy` (or similar) heading, so they can be copied.

## Waiting for a long job

When a skill starts something slow in the background (full test suite, PHPStan, a Scramble export, a merge worktree that needs its own `vendor/`), the developer should never stare at silence or guess whether it hangs.

1. **Start card**, right after starting the job:

   > ⏳ **Running the full test suite** (`php artisan test --compact`)
   > Usually about 2-3 minutes on this branch. I'll post progress and the result here.
   > Meanwhile you can: review the diff above, or answer the open question.

   Give the expected time from the last run of the same job, never a made-up number.
2. **Progress lines** at real milestones, not on a timer. Watch the job with a background-watch tool and post one short line when something meaningful happens:
   - `⏳ Tests ▓▓▓▓▓░░░░░ 52% · 640 / 1,237 · 0:48 elapsed`
   - `⏳ PHPStan: 12 / 20 files analysed`
   - `⚠️ First failure: BlogControllerTest > it rejects another company's author (continuing)`

   Draw a bar only when the job reports a real count; otherwise give elapsed time and the last milestone. Never invent percentages.
3. **Stuck check:** past twice the usual time with no new output, say so (`⚠️ No output for 4 minutes, checking`) and investigate per the skill's hang rules instead of waiting silently.
4. **Finish** with the normal headline (`## ✅ Tests passed: 1,237 in 2:41` or `## ❌ 3 tests failed`) and the result table.
5. **Long jobs (over 5 minutes):** send a desktop push notification when it ends, if the notification tool is available, so the developer can switch tasks meanwhile.

## Render drafts as real markdown

When you show a draft ticket, PR body, plan, review or any other document for the developer to read or approve, write it as real markdown in the chat response:

- Real `#` headings, real `-` bullets, real checkboxes, real bold.
- Never wrap the whole document in one code fence. A fence shows the literal `##`, `-` and `**` characters, which is much harder to read.
- Exception: a small literal snippet meant to be copied as-is (a shell command, an exact string) may use a fence.
- A fence inside a `SKILL.md` that shows a template's shape is fine. It tells the skill what to produce. When the skill produces it for a real reader, render it live.

## Checklists

The task-list tool shows one unlabeled list and cannot show two side by side. So any skill with a checklist also prints it as markdown in the chat.

### When to show it (event-based)

Show the full checklist after each of these events:

- the run starts (show it even when nothing is done yet)
- an item changes state (started, done, `not required`, `skipped`)
- a review round or fix round ends
- right before any approval question (plan, code review, commit, phase transition)
- the run ends

Never go through a long stretch of tool calls and then show only a final summary.

### How to show it

- **Whole list, every time.** Every item, not only the current one.
- **Fresh state.** `[x]` only for really done items. Re-derive the state each time; never copy the last display.
- **Headed.** `### <Skill Name> Checklist` directly above the list, using the skill's name in readable form.
- **Parent and child together.** When an orchestrator (for example `tech-engineering`) runs a child skill, show both checklists in the same response, the parent's first. The parent owns this, because only the parent knows a child is running.

Format is free: a plain `- [x]` list, or a table (No. | Status | Name | Description) when items need more detail.

```markdown
### Tech Engineering Checklist

| No. | Status | Name | Description |
|---|---|---|---|
| 1 | [x] | Discovery | approved, plan in `.claude/specs/...` |
| 2 | [x] | Ticket | ENG-12 |
| 3 | [ ] | Implementation | in progress, branch `feature-x` |
| ... | ... | ... | (every remaining phase) |

### Tech Implementation Checklist

- [x] 1. Branch confirmed
- [ ] 2. Implementation checklist presented
- [ ] ... (every remaining step)
```
