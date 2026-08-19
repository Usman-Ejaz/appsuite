---
name: learn-domain
description: >
    Build or refresh an internal engineering knowledge-base briefing for one Domain
    (architecture, key classes, conventions, cross-domain dependencies, gotchas, test
    coverage) so a fresh chat session already understands the domain before a new feature
    is added to it. Use this whenever the user asks to learn, ramp up on, get context on, get
    briefed on, or ask "what do I need to know about" a Domain before touching its code —
    including phrasing like "learn the Identity domain", "get me up to speed on Ecommerce",
    "brief me on Core before I start", "what's the architecture of CMS", or "refresh the
    knowledge file for Auth" — even when the user doesn't say "/learn-domain" or name this
    skill directly. Reads the domain's code directly (this app has no graphify-style knowledge
    graph today; see Step 2 for the future-proofed fallback if one is ever added). Output is an
    internal, file-path-bearing brief for Claude/engineers, not the product-facing docs site
    (`document`) and not the public API reference (`document-api`).
---

# Learn Domain: Build an Engineering Context Briefing

Usage: `/learn-domain <DomainName>` (e.g. `/learn-domain Identity`, `/learn-domain ecommerce`,
`/learn-domain platform` for the root `app/` code). Case-insensitive, kebab or PascalCase
both accepted.

You are producing an **internal engineering brief** for one Domain, read by a fresh Claude
Code session (or a developer) right before adding a new feature to it. This is different
from its two sibling skills:

- Not `document`: that writes the product narrative for the docs site (once one exists), for
  Product/Tech/QA, and deliberately never cites file paths or class names.
- Not `document-api`: that writes public API contract PHPDoc for external developers.
- This skill is internal-only, for whoever (human or Claude) is about to touch this domain's
  code. File paths and class names are the *point* here, not something to avoid — cite them
  freely and precisely.

The output is one markdown knowledge file plus the same content pasted straight into the chat,
so the current session has the context immediately, without waiting for a future session to
read the file.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** when Step 3 surfaces a base class, request pattern, or convention
  (`BaseRepository`, a shared `FormRequest` base, a shared trait like `HasTeams`), don't treat
  it as this domain's private idiom if it's clearly project-wide infrastructure. Recognizing
  "this is the shared convention, not a Domain-specific one" the first time saves re-deriving
  and re-explaining it as novel on every future run.
- **Unfamiliar means:** a third-party integration's real semantics, a `spatie/laravel-permission`
  behavior you're not certain of, a package's actual documented behaviour. A Convention, Gotcha,
  or Issue finding built on a verified fact beats one built on a plausible-sounding assumption; a
  wrong one doesn't just mislead this pass, it poisons every future session that trusts the
  briefing without re-checking.
- **Learnings file:** `.claude/knowledge-base/skills/learn-domain.md` —
  - Shared base classes/conventions once confirmed project-wide, so later runs don't re-derive or
    re-explain them per domain.
  - Terminology the team actually uses for a concept when it differs from what the code calls it.
  - If a graphify-style knowledge graph is ever added to this project, repo-specific quirks of it
    (actual output-file field shapes, gaps in coverage, query phrasings that work well here) belong
    here too, so a future run doesn't have to re-derive them.
  - Tag each entry with which domain surfaced it when that's relevant.
  - **Never log Issues Found entries here**, false positives included. Anything about a specific
    issue belongs only in that domain's own knowledge file (Step 5), which is regenerated fresh
    from current code every run, so a fixed issue simply stops being observed and drops out on
    its own. This file has no such regeneration step, so anything issue-shaped written here would
    just accumulate as unprunable stale noise once the underlying code moves on.
- **Propose a different shape, don't force or silently improvise one:** a domain genuinely
  doesn't fit the fixed Step 5 template (no models at all, purely event-driven, split unusually
  across directories) — say so and propose the specific adjustment. It affects every future
  `/learn-domain` run, not just this one, so don't act on it without the user agreeing.

---

## Step 0: Resolve the domain

If no argument was given, read `modules_statuses.json` (repo root) for the authoritative domain
list, its keys are exactly the `Domains/<Name>` folder names, don't `ls Domains/` for this:

```bash
python3 -c "
import json
statuses = json.load(open('modules_statuses.json', encoding='utf-8'))
for name, enabled in statuses.items():
    print(name if enabled else f'{name} (disabled)')
"
```

As of this writing that list is `Auth`, `CMS`, `Core`, `Ecommerce`, `Identity`, `Shared`, all
enabled — but re-read the file each run rather than trusting this list, a new module can be added
at any time. Present that list plus `App` for the root-level `app/` code (cross-cutting shared
code, matching how `document` already treats root `app/` as `platform/`), and ask which one.
Don't guess, and don't default to processing every domain in one pass, this command is scoped to
one target per run.

Resolve the argument to a real target:

- `Domains/<PascalName>/` for a normal domain (`identity` → `Domains/Identity`, `ecommerce` →
  `Domains/Ecommerce`), matched case-insensitively against the keys in `modules_statuses.json`,
  not against the filesystem.
- `app` / `platform` / `core` → the root `app/` directory. (Note this is unrelated to the
  `Core` *domain* under `Domains/Core/`, which is a real module in its own right — the root
  `app/` is cross-cutting code that hasn't been moved into any Domain module. If the user says
  "Core" ambiguously, ask which one they mean.)

If the resolved name isn't a key in `modules_statuses.json` and isn't `app`/`platform`/`core`,
show the list from `modules_statuses.json` again and ask, rather than guessing a close match. If
the matched module is disabled (`false` in the JSON), still proceed, but flag it in the final
report, a disabled domain's code can be stale relative to what's actually running.

**Done when:** a real target (a domain confirmed present in `modules_statuses.json`, or `App` for
the root) is confirmed.

---

## Step 1: Fast path — reuse an existing briefing if nothing changed

```bash
ls .claude/knowledge-base/domains/<domain-slug>.md 2>/dev/null
```

If it exists, read its frontmatter `generated_at_commit: <sha>` and check whether anything
relevant has actually changed since:

```bash
git diff --name-only <stored-sha>...HEAD -- Domains/<Domain>/
```

(For a `platform` briefing, scope the diff to `app/` instead.)

If that's empty, the briefing is still accurate: read the existing file for context, skip
straight to Step 5 (orientation summary, noting it was reused rather than regenerated), then
continue through Step 6 (Issues Found checklist, pulled from the reused file as-is) and Step 7
(ask what's next) as normal. Don't silently regenerate something that's already current, it
wastes tokens for no benefit, but don't skip the presentation steps either, a reused briefing
still needs to be surfaced to whoever just asked for it.

If the file doesn't exist, or the diff is non-empty, continue to Step 2.

**Done when:** either an up-to-date briefing was reused and the rest of this skill is skipped,
or regeneration is confirmed necessary.

---

## Step 2: Choose acquisition mode — direct reading, or a future graph tool

This app has no graphify-style knowledge graph today (no `graphify-out/graph.json` or
equivalent exists in this repo). **Direct Mode is the default; always use it unless a future
graph-based tool shows up.** Check for one defensively before falling back, so this step doesn't
need editing the day one is added:

```bash
test -f graphify-out/graph.json && echo "GRAPH_EXISTS" || echo "NO_GRAPH"
```

- **Graph Mode**: only if `graphify-out/graph.json` (or an equivalent this project later adopts)
  exists AND actually covers this domain — check before committing to it, a graph scoped to a
  different subset of the repo won't have this domain in it. If a graph tool is ever added,
  follow whatever query interface it exposes to gather the same categories Step 3 lists below,
  and only drop into a raw `Read` when the graph's answer is ambiguous on something you need to
  state precisely.
- **Direct Mode**: no graph tool exists (the current, expected state), or it doesn't cover this
  domain. This is not a degraded fallback to apologize for, it's simply how this skill works
  today; don't imply a graph query was skipped when none exists to skip.

**Done when:** the acquisition mode is decided and stated (in practice, almost always Direct
Mode, for now).

---

## Step 3: Gather via direct reading

Build the picture by hand:

```bash
# Enumerate structure by category
find Domains/<Domain>/app -maxdepth 2 -type d
find Domains/<Domain>/app/Models -name '*.php' 2>/dev/null
find Domains/<Domain>/app/Http/Controllers -name '*.php' 2>/dev/null
find Domains/<Domain>/app/Services -name '*.php' 2>/dev/null
find Domains/<Domain>/app/Repositories -name '*.php' 2>/dev/null
find Domains/<Domain>/app/Enums -name '*.php' 2>/dev/null
find Domains/<Domain>/database -maxdepth 2 -type d
find Domains/<Domain>/tests -name '*.php' 2>/dev/null
```

(For a `platform`/root-`app/` briefing, run the equivalent `find app/...` commands instead —
`app/Models`, `app/Http/Controllers`, `app/Actions`, `app/Policies`, `app/Enums`, `app/Concerns`,
`app/Rules`, `app/Data`, `app/Notifications`.)

- Read every Model in full (relationships, casts, scopes are the highest-value facts per file).
- For other classes, a docblock + class signature + public method signatures is usually enough
  for a one-line purpose; only read a full method body when the class is small or genuinely
  central (referenced from many other files).
- Find cross-domain dependencies both directions:

```bash
# What this domain depends on
grep -rho 'use Domains\\[A-Za-z]*\\' Domains/<Domain>/app | sort -u

# What depends on this domain
grep -rl "Domains\\\\<Domain>\\\\" Domains/*/app app 2>/dev/null | grep -v "^Domains/<Domain>/"
```

Also check the module's own manifest for a stated purpose, though as of this writing every
`Domains/<Name>/module.json`'s `"description"` field is empty for every domain in this app,
don't assume one has been filled in without checking:

```bash
cat Domains/<Domain>/module.json
```

**Done when:** models, components, and cross-domain dependencies in both directions are
populated from direct reads.

---

## Step 3b: Flag issues while gathering

While reading the domain (Step 3), keep a running list of anything that looks genuinely wrong,
not just "could be written differently." This is a side effect of the gathering pass, not a
separate audit sweep, don't go looking for extra files beyond what Step 3 already read just to
pad this list. In scope:

- **Logical bugs**: a condition that can't do what it's named for, an off-by-one, a code path
  that's unreachable, a return value that contradicts the method's own contract.
- **Programmatic issues**: missing null/empty checks before a use that will throw, a race
  condition (check-then-act without a lock/transaction), an N+1 query, a resource that's never
  released, inconsistent transaction boundaries around multi-step writes, a `use`d class that
  doesn't actually exist under that namespace (this has happened for real in this app — see the
  Gotcha about `App\Models\User` in `apply-permissions`'s own rules if this pass touches the root
  `app/` Team feature).
- **Conceptual issues**: a model/relationship that doesn't match how the domain's own docs or
  README describe it, two components disagreeing about who owns a piece of state, a convention
  used everywhere else in the domain that this one class quietly breaks.
- **Vulnerabilities**: missing authorization checks, mass-assignment exposure, unescaped output,
  a secret or credential handled unsafely, an input trusted without validation where a sibling
  domain does validate the equivalent input.

Grade each finding into exactly one priority, using the highest one that genuinely applies:

- **Critical**: exploitable now, or actively corrupts/loses data, bypasses auth/authorization, or
  breaks the feature outright (e.g. a class reference that doesn't resolve, so every code path
  through it throws).
- **High**: wrong behaviour on a common, realistic path; a bug a normal feature addition would
  likely trip over.
- **Medium**: wrong behaviour on an edge case, or a real conceptual inconsistency that will
  confuse whoever builds the next feature here, but isn't actively breaking anything today.
- **Low**: a minor inconsistency or smell worth knowing about, not worth interrupting anyone for.

Don't invent findings to fill out every severity, an empty or short list is a fine outcome. Don't
downgrade something Critical to Medium just to avoid alarming the reader, and don't upgrade a
style nit to sound more thorough. When genuinely unsure which bucket fits, say so in the finding
itself rather than picking confidently.

**Do not fix anything found here.** This pass only reads and reports; fixing is a separate,
deliberate task the user chooses to do (or hand off) afterward, with full context and their own
prioritization, not a side effect of asking for a domain briefing.

**Done when:** every issue noticed during the Step 3 reads (not a separate search) has a
one-line description, its file path, and a priority, or the list is empty because nothing
genuine turned up.

---

## Step 4: Supplementary narrative sources

Pull whatever already exists rather than re-deriving it:

- A docs-site overview page for this domain, if one exists (this app has no docs site set up at
  the time of writing — see the `document` skill's Step 0 — so this will usually come up empty;
  check anyway rather than assuming).
- Any root-level domain README inside the domain folder itself (none exist yet for any domain in
  this app, as of this writing; check `Domains/<Domain>/` for one anyway, since a domain may have
  gained one since).
- `Domains/<Domain>/tests/Feature/` and `tests/Unit/` structure: what's actually asserted, which
  is often a better source of real edge-case behaviour than the implementation itself. Note
  plainly if a domain's tests are thin or missing outright; that's itself a fact worth surfacing,
  not a gap to paper over.

**Done when:** any existing narrative/README/test context has been folded in rather than
ignored in favour of re-inferring from scratch.

---

## Step 5: Write the knowledge file

Path: `.claude/knowledge-base/domains/<domain-slug>.md`. Follow the fixed structure in
[references/knowledge-file-template.md](references/knowledge-file-template.md) — only load that
file when you actually reach this step; the sections and their intent are documented there so
this file doesn't need to hold the full skeleton inline for every step that comes before it.

Cite file paths and class names throughout, unlike `document`'s docs site, that's the whole
point of this brief. When updating an existing file, edit it to reflect current reality; don't
append a changelog section, the file should always read as current truth (same rule as
`document`'s feature pages).

**Done when:** the file exists, every section that applies to this domain is populated, and
`generated_at_commit`/`generated_mode` reflect this run.

---

## Step 6: Absorb the briefing — don't dump it

The knowledge file written in Step 5 is the deliverable that matters: having gathered and written
it, this session now holds that context for whatever comes next. **Do not paste the full
generated markdown into the chat.** A wall of raw sections is not what makes a session
"understand the domain," and it forces the user to read past everything just to find the two or
three facts they actually needed. The knowledge is retained by having produced it, not by
re-displaying it.

Instead, give a short orientation confirmation, five or six lines at most:

```
Learned <Domain>. [mode: direct | reused]

<One-line purpose.>

- N models, M components across K categories
- Depends on: <domains>, ranked by import weight if that's easy to state, otherwise just named
- Depended on by: <domains>, or "none found"
- <The single most important Gotcha/Locked Decision, if there is one worth surfacing up front>

Full detail: `.claude/knowledge-base/domains/<domain-slug>.md`
```

If a section is genuinely thin or empty for this domain, don't pad this summary to look more
complete than the domain is, just say "no cross-domain dependents" plainly. This is expected to
be common right now: several domains in this app (`Ecommerce`, `CMS`, `Shared`) are still thin,
early-stage modules with only a stub controller or a couple of models, and an honest "not much
here yet" is a correct, useful answer, not a failed briefing.

**Done when:** the user has a short, scannable confirmation that the domain was learned, with a
pointer to the file for anything they want to read in full, and no full-length briefing dump.

---

## Step 7: Present Issues Found as a checklist

Pull the `## Issues Found` section from the knowledge file and present it directly in the chat,
after the Step 6 summary. This is the one section that *does* get shown in full, because it's
actionable, not reference material to skim past. Show it as real, rendered markdown, not wrapped
in a code fence, see [Output formatting](../shared/output-formatting.md) for why:

```
## Issues Found — <Domain>

### 🔴 Critical (N)
- [ ] `path/to/File.php:<line>` — one-line description of what's wrong and how it breaks.

### 🟠 High (N)
- [ ] ...

### 🟡 Medium (N)
- [ ] ...

### 🔵 Low (N)
- [ ] ...
```

Omit a severity heading with zero findings, don't print an empty "(0)" section. If every severity
is empty, say "No issues found in this pass" as a single line instead of an empty checklist shell.
Keep each line to one sentence, this is a punch list to act on later, not the place to re-explain
the finding at length, the knowledge file already can carry more detail if a finding needs it.

**Done when:** every issue from the knowledge file's Issues Found section is visible as an
unchecked checklist item grouped by severity, or the list's absence is stated plainly.

---

## Step 8: Ask what's next

Nothing else proceeds automatically. Once the domain is learned and the issues (if any) are
shown, ask the user directly what they want to do, using the AskUserQuestion tool with these
options:

- **Build something new** — start on a new feature in this domain now, using the context just
  gathered. Proceed straight into normal feature-planning conversation once chosen.
- **Fix the issues first, one by one** — this is the explicit trigger this skill has been
  withholding fixes for; walk the checklist starting from the highest surviving severity,
  confirming each fix before moving to the next rather than batch-fixing silently.
- **Pause for now, decide later** — do nothing further; the knowledge file already persists, so
  nothing is lost by stopping here.
- Leave a third option open-ended (the tool's own "Other" affordance) for anything else the user
  has in mind — including handing the Issues Found list off to whatever task-tracking workflow
  this project ends up using, if one exists.

Don't pick a default and proceed on the user's behalf, this is exactly the kind of fork only the
user can resolve, per the tool's own guidance on genuinely blocking decisions.

**Done when:** the user has explicitly chosen a next step and that step is underway (or the
session is paused, per their choice).

---

## Principles

- This command never touches a docs site or PHPDoc; it only reads code and writes to
  `.claude/knowledge-base/domains/`.
- Direct reads are the norm today (Step 2) — this app has no graphify-style knowledge graph.
  Should one ever get added, prefer it for orientation the same way engineering teams elsewhere
  prefer a knowledge graph over cold grepping, but don't pretend one exists until it actually
  does.
- Unlike a product docs site, file paths and class names are required here, not forbidden. A
  brief that can't be navigated from is useless for its purpose.
- Don't regenerate a briefing that's still accurate (Step 1). Don't skip regenerating one that's
  gone stale just because it exists.
- If a domain genuinely has nothing interesting for a section (no cross-domain dependents, no
  gotchas worth recording, a near-empty module like `Ecommerce` or `CMS` today), say so briefly
  rather than omitting the section silently or padding it with restated obvious behaviour.
- **Never dump the full knowledge file into the chat (Step 6).** The file is the artifact; the
  chat gets a short orientation summary plus the Issues Found checklist, nothing more, by default.
- Issues found during gathering (Step 3b) are reported, never fixed, and never used as an excuse
  to edit code during this pass. This command's only writes are the knowledge-base file itself;
  fixing anything only happens after the user explicitly picks "Fix the issues" in Step 8, never
  as an implicit part of generating the briefing.
