# Working Agreement

How every skill in this repo works with the developer. Skills link here and never restate these rules.

## Decisions belong to the developer

- **Consult before deciding.** Ask with concrete options (a closed question, with your recommendation marked) before you choose any of these:
  - default values, seeded values, notification or UI copy
  - which records qualify, who gets notified, eligibility rules
  - any change to a shared or core class (traits, global scopes, base classes, providers, abstract actions)
  - any change to a schema or data model the developer proposed
- **Build on the developer's proposed schema.** Treat it as the baseline. Do not split or add columns, rename the owner concept, or move it to another domain on your own. First ask about future plans (other owner types, sharing), then raise only the changes the code or a real constraint forces, each as a short question with its reason.
- **Never present your own picks as settled.** State pure implementation details plainly in the review summary. Never add behavior nobody asked for.
- **When the developer asks "why do we have X"**, first check whether their mental model differs from the code, and correct that before defending the design.

## Stay inside your change

- **Never fix issues you did not create.** This covers PHPStan errors, Pint findings, bugs and smells in code you did not write this session, including errors your new code only triggers inside existing shared code (for example trait boot closures). Report them as pre-existing. Do not add baseline or ignore entries for them.
- **Ask before editing any shared core file** (trait, global scope, base class, provider, anything under `Domains/Core`) that is not in the approved plan. These files touch every model, so a "behavior-identical" edit still needs approval.
- **Do not create new issues either.** Your own lines must be clean: Pint-formatted and covered by a test (see the project `CLAUDE.md`).
- **Format only your own files.** Run Pint on explicit paths (`vendor/bin/pint <paths> --format agent`). `pint --dirty` also reformats the developer's unrelated work in progress.

## Git: the index belongs to `git-commit`

- **Never stage or commit outside the `git-commit` skill.** That includes indirect staging: no `git add`, `git mv`, `git rm`, `git stash`, `git add -N`, `git commit`. Rename files with plain `mv`.
  - Exception: merge commits that a delivery step needs (conflict resolution in `git-open-pr`, merges in the `tech-engineering` delivery phase). Each one is shown first and needs its own explicit approval.
- **Commit only on an explicit ask, every time.** An earlier "commit this" does not cover later changes in the same session. Each commit needs its own fresh request.
- **Subagents you dispatch must not commit either.** Tell them to leave changes in the working tree. If one commits anyway, undo it at once with `git reset --soft <pre-task commit>`.

## Files and hand-offs

- **Generated files stay inside `.claude/`.** Specs and design notes go to `.claude/specs/YYYY-MM-DD-<topic>-design.md`. Never create a root `docs/` folder or any new top-level folder, even when another skill (for example a plugin) defaults to one.
- **Hand-offs pass context only:** plan pointer, ticket ID, branch, base, decisions. They never tell the next skill to skip, reuse or shorten one of its steps (for example "tests already ran"). If a skill rule looks wrong, propose a change to that skill instead of patching around it in a prompt or in hand-off args.
- **The change must live in this repo.** If the work turns out to belong to another repo (for example the separate `appsuite-frontend` SPA), report what you found, pause, and stop. Do not plan branches or tests in the other repo unless the developer asks for that repo too.

## Phase and step outcomes

Use these two labels exactly. They mean different things.

| Label | Meaning | Who decides |
|---|---|---|
| `not required: <reason>` | The phase or step does not apply to this change. | The skill decides from the actual change and moves on without asking. |
| `skipped: <reason>` | It applies, but the developer chose not to do it. | Only the developer. |

If a phase is required, do it. Never ask "skip this?" for something that is not required.

## Verify what is uncertain, not what is textbook

- Verify facts that are uncertain in this codebase or its library versions (a third-party API's rules, a package's behavior here, a repo convention) before they become a rule, finding or plan step.
- Do not write throwaway scripts to confirm well-known PHP or Laravel behavior (for example that a backed enum serializes to its value). State it and move on.
