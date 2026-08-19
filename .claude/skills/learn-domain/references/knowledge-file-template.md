# Knowledge File Template

The full markdown skeleton for `.claude/knowledge-base/domains/<domain-slug>.md`, referenced from
Step 5 of the `learn-domain` skill. Populate every section that applies to the domain being
briefed; skip a category line only when that category has no members in this domain (e.g. no
`Jobs/` directory), and say so briefly for a whole section that's genuinely empty rather than
omitting it silently — see the skill's own Step 5 and Principles for that rule.

```markdown
---
domain: <Domain>
generated_at_commit: <current `git rev-parse HEAD`>
generated_mode: direct | graph
---

# <Domain> Domain — Engineering Context

## Purpose
One or two sentences: what this domain owns and why it exists as its own domain. If the domain's
own `module.json` has an empty `"description"` (true for every domain in this app as of this
writing), say so and state the purpose as observed from its actual code rather than implying it
was pulled from a manifest that doesn't actually say anything yet.

## Key Models & Relationships
- `ModelName` (`Domains/<Domain>/app/Models/ModelName.php`): relationships, notable scopes/casts,
  anything a new feature would need to know before touching this model.

## Components
### Services / Repositories / Controllers / Requests / Resources / Enums / Jobs / Events /
  Listeners / Notifications / Policies / Scopes
(only the categories that actually exist in this domain; skip empty ones)

## Conventions & Patterns
Base classes reused (e.g. `Domains\Core\Repositories\BaseRepository`), naming patterns, anything
domain-specific a new feature should follow rather than reinvent.

## Cross-Domain Dependencies
### Depends on
- `Domains\Other\...`, used by `path`, for `<reason>`.
### Depended on by
- `Domains\Other\SomeClass` uses `Domains\<Domain>\...`.

## Known Gotchas / Locked Decisions
Pulled from domain READMEs (none exist yet for any domain in this app), or genuinely load-bearing
non-obvious code, not restated obvious behaviour. Each one: what, and why it's not the more
obvious alternative.

## Test Coverage Map
- `Domains/<Domain>/tests/Feature/...`: what it covers.
- `Domains/<Domain>/tests/Unit/...`: what it covers.
- Gaps: anything conspicuously untested that a new feature should be wary of extending blindly.
  Several domains in this app are early-stage with thin or no test coverage yet; say so plainly
  when that's the case rather than implying coverage that doesn't exist.

## Issues Found
_Reported, not fixed. Surfaced during this briefing's own reads; not a dedicated audit._

### 🔴 Critical
- [ ] `path/to/File.php:<line>` — what's wrong, and the concrete way it can go wrong.

### 🟠 High
- [ ] `path/to/File.php:<line>` — ...

### 🟡 Medium
- [ ] `path/to/File.php:<line>` — ...

### 🔵 Low
- [ ] `path/to/File.php:<line>` — ...

(Omit a severity heading entirely when it has no findings; don't print "None" under it. Each
line is a checkbox so it can be tracked as a punch list; leave every box unchecked, this pass
never fixes anything.)
```

Cite file paths and class names throughout, unlike `document`'s docs site, that's the whole
point of this brief. When updating an existing file, edit it to reflect current reality; don't
append a changelog section, the file should always read as current truth (same rule as
`document`'s feature pages).
