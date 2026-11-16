# Auto Learning

How a skill gets better from its own runs. Every skill links here from one line in its body.

## During a run

- **Look wider when a pattern looks reusable.** If something is a project-wide convention, a shared base class or a recurring shape, treat it as shared, not as private to this diff or domain.
- **Look things up when they are really unfamiliar** (see "Verify what is uncertain" in [Working Agreement](working-agreement.md)). An unverified guess that becomes a rule misleads every later run.
- **Read the skill's `references/gotchas.md`** at the step its `SKILL.md` names, if the file exists.
  - Skills not yet synced with this version still name a learnings file at `.claude/knowledge-base/skills/<skill>.md`. Read it the same way if it exists; new lessons for those skills go to their `references/gotchas.md`.

## Recording a lesson

When a run teaches something durable, route it to exactly one place:

| The lesson is ... | It goes to ... |
|---|---|
| A change to how a step works (new step, changed order, new check) | A proposed `SKILL.md` edit, shown as before/after for the developer to approve. Never a gotcha. |
| A narrow trap inside a step | `<skill>/references/gotchas.md` |
| A fact about one domain (a locked decision, a "not a bug") | That domain's brief in `.claude/knowledge-base/domains/` (via `learn-domain`) |
| A team-wide working rule or coding convention | A proposed edit to the right `shared/` file, or a `.ai/rules` entry via Boost's `record-rule` (see the project `CLAUDE.md`) |
| A personal preference of one developer | Not a skill file. It stays in that developer's own memory. |

## `references/gotchas.md` format

- Create it lazily, only when there is a first real entry.
- One heading per entry, 1-3 lines: the trap and what to do instead. Add a pointer to an example file if useful.
- Keep the lesson only: no dates, ticket IDs, commit hashes, PR numbers or incident stories.
- Cap about 40 lines. When it grows past that, propose folding the entries into the `SKILL.md` body and empty the file.
- Name the step it applies to, so the `SKILL.md` pointer stays accurate.

## Proposing changes

- Bring a concrete before/after, not "should I change this?".
- Never change how a skill works without the developer agreeing first. It affects every future run.
- If a developer's correction in this session names a skill and applies to the whole team, propose moving it into that skill or into `shared/`.
