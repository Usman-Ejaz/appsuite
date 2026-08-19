---
name: document
description: >
    Generate or update product-facing feature documentation for whatever changed on the current
    branch: a new feature, a new module, or a change to existing behaviour. Use this whenever the
    user asks to document a feature, write or update docs, add a docs page for something just
    built, or fix a sidebar/nav for new content — including phrasing like "document this", "write
    docs for X", "update the docs for Y", "add this to the docs site", "the sidebar needs an entry
    for Z", or "docs are out of date for this branch" — even when the user doesn't say "/document"
    or name this skill directly. This app has no product-facing docs site set up yet (no
    `resources/docs`, no VitePress or any other docs generator installed) — see Step 0, which must
    run first and gets explicit user go-ahead before creating any new folder or adding any
    dependency, per this project's own rule against silently scaffolding either. Once a docs site
    exists (here or in a future pass), this skill maps the change to a Domain/Module/Feature
    location, writes or updates the relevant page(s), fixes the sidebar/nav, and removes docs for
    anything deleted. Output is a narrative for Product, Tech, and QA: not code comments, not
    PHPDoc, and not an API reference (that's document-api).
---

# Document a Feature: appsuite Docs

You are maintaining product-facing feature documentation for appsuite. **As of this writing, no
docs site exists in this repo at all**: there is no `resources/docs`, no VitePress (or any other
docs generator) in `package.json`, and no built docs output anywhere. This is different from a
project where the site already exists and this skill just keeps it current — here, the first
thing this skill must do, every time, is check whether that's still true, and if so, stop and ask
before creating anything (Step 0).

This is **not** code documentation and it is **not** API documentation: no PHPDoc, no inline
comments, no request/response schema dumps, no parameter-by-parameter tables. If this app later
adopts an OpenAPI/API-doc generator, that's the `document-api` skill's job; link to it, don't
rebuild it here.

Once a docs site exists, its job is to tell the **narrative** of a feature: what it does, when it
applies, and how it behaves, in product terms, for readers who may never open the codebase or the
API reference (Product, Tech leads doing impact analysis, and QA writing test cases). A reader
should come away understanding the feature's purpose and rules, able to retell the story of how
it works. Every step below serves that one narrative. If a step is producing something else (a
contract, a reference, a class-by-class breakdown), it has drifted off course.

**Writing style.** Avoid em dashes in prose; they read as AI-generated filler, not how people
actually write. Use a period, comma, or semicolon instead, and reach for a plain hyphen only when
a genuine parenthetical is unavoidable. This applies to every page: domain overviews, module
overviews, and feature pages alike.

Your job: figure out what changed on this branch, confirm (or get explicit go-ahead to establish)
where a docs site would live, work out where the change belongs in the doc structure, and
create/update/remove markdown pages plus the sidebar/nav so the site stays accurate.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** skim sibling code in the same domain (similar controllers, shared enums,
  related actions) even when untouched by the current diff. Patterns and terminology it doesn't
  fully reveal are often sitting right next to it, and understanding them catches inconsistencies
  with what's already published.
- **Unfamiliar means:** a third-party API's actual rules, an industry/compliance term, a
  library's documented behaviour, or (specific to this skill, right now) whether a docs
  generator has actually been installed since the last time this skill ran — verify with `find`/
  `grep`, per Step 0, rather than assuming last run's "not set up yet" still holds.
- **Learnings file:** `.claude/knowledge-base/skills/document.md` — naming/terminology the team
  actually prefers, structural patterns that read well once applied (or ones that didn't), which
  docs tool the user ultimately chose in Step 0 (so a future run doesn't have to re-ask once
  it's settled), and anything non-obvious confirmed by searching.
- **Propose restructuring, don't silently do it or silently stay quiet:** a page that's quietly
  grown into two unrelated flows and should split, a domain whose module grouping no longer
  matches how the code is organized, a pattern used elsewhere that fits better than what's here.
  Restructuring published pages affects everyone who already has them bookmarked or linked, so
  don't act on it without the user agreeing.

---

## Step 0: Confirm a docs site exists — never scaffold one silently

Before anything else, check what's actually there:

```bash
find . -maxdepth 2 -iname "docs" -not -path "./vendor/*" -not -path "./node_modules/*"
grep -i "vitepress\|docusaurus\|nextra\|mkdocs" package.json 2>/dev/null
```

As of this writing, both of these come back empty: there is no `resources/docs` (or any other
`docs` folder), and no docs generator of any kind is in `package.json`. Re-run this check every
time regardless of what a previous pass found; a docs site may have been set up since.

**If no docs site exists:** say so plainly to the user, and stop before creating any folder,
config file, or dependency. This project's own rules (`CLAUDE.md`) are explicit that new base
folders and new dependencies need approval before being created, and that documentation files
should only be created when explicitly requested, none of which a docs-site scaffold gets to
skip just because this skill exists. Ask, in one consolidated question:

> There's no product-facing docs site set up in this repo yet. Do you want me to set one up now
> (I'd need to add a docs folder and, depending on what you pick, a new dependency), or would you
> rather I just describe the feature/narrative directly in chat for now and skip creating a site?

**Don't assume VitePress specifically.** A previous version of this skill (on a different
project) defaulted to VitePress without asking; don't carry that assumption over. If the user
says yes to setting up a site, ask what they want to use, or accept whatever they name, rather
than picking a tool on their behalf. Once they confirm a tool, get separate, explicit confirmation
before running any install command, per this project's dependency rule; that's a second, distinct
approval from "yes, set up a docs site" in principle, though the user may give both at once if
they're specific enough in their first answer ("yes, use VitePress, go ahead and install it").

**If the user declines a docs site entirely:** don't block on it. Write the feature narrative
directly into the chat response instead, using the same Step 2-5 content rules (behaviour-first,
Scenarios/Workflow/Constraints/Prerequisites/Permissions as applicable), skip Steps 1 and 6-7
(there's no structure or build to maintain), and note in the final summary that this was written
as a one-off chat response, not persisted anywhere, so the user knows to save it themselves if
they want to keep it.

**If a docs site already exists** (this pass or a future one, once set up): proceed straight to
Step 1, and don't re-ask the setup question, only re-check that the site is actually still there.

**Done when:** either a docs site's existence (or explicit absence) is confirmed and, if absent,
the user has given an explicit answer on whether to create one and with what tool, or the user
has explicitly opted for the no-site chat-response fallback instead.

---

## Step 1: Gather what changed

Run in parallel:

```bash
git rev-parse --abbrev-ref HEAD
git status --short
git diff main...HEAD --stat
git diff main...HEAD --name-only
git diff main...HEAD   # only if the file list is small; otherwise diff file-by-file as needed
```

Include uncommitted changes (`git status`); the user may be mid-feature. If the branch has no
divergence from `main` and everything is uncommitted, that's fine, just work from `git diff` /
`git status` directly. (This repo is not yet a tracked git remote at the time this skill was
written — if `origin/main` doesn't resolve, fall back to whatever the actual default branch is
named, or to comparing against the working tree's own uncommitted state.)

If the diff is large and spans clearly unrelated work, ask the user which part to document rather
than guessing.

**Done when:** every changed file relevant to this documentation pass is identified, committed and
uncommitted, and any unrelated work has been explicitly excluded rather than silently ignored.

---

## Step 2: Map changes to Domain → Module → Feature

For each changed file, resolve its domain:

- `Domains/<DomainName>/...` → domain slug is `<DomainName>` kebab-cased (e.g. `Ecommerce` →
  `ecommerce`, `CMS` → `cms`). The six domains in this app today, per `modules_statuses.json`,
  are `Auth`, `CMS`, `Core`, `Ecommerce`, `Identity`, `Shared` — re-read that file rather than
  trusting this list, a new module can be added at any time.
- `app/...` (root, shared code) or changes that cut across multiple domains → belongs under
  `platform/` (cross-cutting), not a domain folder. As of this writing, root `app/` mainly holds
  the Team/Membership feature (`app/Models/{Team,Membership,TeamInvitation}.php`,
  `app/Policies/TeamPolicy.php`, `app/Enums/{TeamPermission,TeamRole}.php`,
  `app/Http/Controllers/Teams/*`, `app/Concerns/*`) plus Fortify-based authentication scaffolding
  — verify current contents with `find app -maxdepth 2 -type d` rather than trusting this list is
  exhaustive, since root `app/` is exactly the kind of place that accumulates new cross-cutting
  code between passes.

Within a domain, read the actual changed code to understand the **behaviour**, not just the file
names: controllers/routes for endpoints and their contracts, requests for validation rules,
services/actions for the flow and business rules, resources for response shape, enums for allowed
values, tests for edge cases and error scenarios actually being asserted. Tests are a particularly
good source of truth for "what actually happens on the edge cases." You need this depth of
understanding to write a good feature narrative even though the final page won't dump most of it
as a schema; understanding the mechanism is what lets you describe the _behaviour_ accurately.
Don't stop at the diffed files to get there; see Auto Learning above.

Then decide the **Module** (a logical grouping inside the domain) and the **Feature** (one page:
one coherent flow or capability, not one page per class/file, and not one page per endpoint).
Group multiple related endpoints/classes into a single feature page when they're really one flow.

**For a CRUD-shaped resource, split by operation type instead of one page per resource.** When a
module centers on a single resource with standard list/get, create, update, and delete endpoints,
one page trying to narrate all of them gets unwieldy fast. Split by *operation type* instead:

- **Viewing** (list + get): one page. If the module is just this one resource, this can double as
  the module's own overview page.
- **Creating**: one page.
- **Updating**: one page, covering the general update endpoint *and* any special-purpose update
  variants together (status changes, reassignment, bulk update). They're all still "modifying an
  existing record," just via different endpoints, and they share most of their constraints.
- **Deleting**: its own page only when deletion has real behaviour worth narrating (soft- vs.
  hard-delete, cascading effects, bulk-delete edge cases, who's allowed to do it). When deletion
  is a plain "removes the record, that's it," fold it into the end of the Updating page as a
  short section instead of creating a page with two sentences in it.

Write each page from what the code actually supports. Don't invent a Create or Update page for a
resource that has no such endpoint. If a resource only ever comes into existence as a side effect
of something else (no dedicated create endpoint), that's still worth its own page describing *how*
it comes to exist and cross-linking to the real trigger. Just don't title it as if a direct
endpoint exists.

This split doesn't apply to modules that aren't shaped like a single CRUD resource. Auth's login
module, for instance, may have several genuinely distinct authentication *methods* as its
features, not CRUD operations on one resource. Keep groupings like that as they are.

Once a docs site exists, look at its existing structure for this domain first; if a module/
feature already exists that this change extends, update it there rather than inventing a new
grouping.

**Done when:** every changed file from Step 1 has a Domain/Module/Feature assignment (or is
explicitly out of scope for docs, e.g. pure refactor, internal tooling).

---

## Step 3: Audit the existing docs before writing

Once a docs site exists, read, in parallel, whatever its actual layout is for the domain in
scope (paths will vary depending on what got set up in Step 0 and where; there's no fixed
`resources/docs/` path to assume until that decision is made):

- The domain's existing modules and features.
- The domain's own overview/index page and its module list.
- The site's nav/sidebar registration, wherever its config lives.
- The site's home page, if it has one listing domains/features (only relevant for a brand-new
  domain).

Determine, per feature touched by the diff, which of these applies:

- **New domain**: nothing documented for this domain yet
- **New module**: domain exists, this module doesn't
- **New feature**: module exists, this is a new page within it
- **Update**: the feature page already documents this behaviour; the change modifies it (new
  field, changed validation, new error case, changed flow order, etc.)
- **Remove**: code/endpoint/flow was deleted or replaced; the existing page (or the specific
  section of it) is now wrong and must be removed, not just left stale

Never leave a doc page describing behaviour that no longer exists in the code.

**Ask before creating new structure.** When this classification finds a **new domain** or **new
module** with no existing overview, don't just start writing. Check in first, in one consolidated
question covering everything that's new:

> The `Ecommerce` domain doesn't have any documentation yet. Would you like me to craft the full
> domain overview (Problem/Goals/Scope, etc.) before I document this feature, or should I skip
> straight to the feature page and leave a placeholder for the overview for now?

Offer this as a real choice, but **default to "yes, write the full documentation."** That's the
recommended option, since a page worth building at all is worth introducing properly, and a
skipped overview tends to stay skipped. Only skip to a placeholder when the user actively says
to. Ask the same way for a new module. Don't ask about **updates** to pages that already exist;
only about creating new structure. If the user does say to skip it for now, still create a
minimal one-line placeholder so the sidebar has somewhere to point, and note in Step 8 that a
fuller write-up is still pending.

**Ask for a source when writing a real overview.** If the user does want a domain/module overview
crafted now, ask whether there's a ticket, PRD, or discovery doc to pull real Problem/Goals/Scope
content from. A source doc beats inferring from code, since code shows *what* was built, not
*why*. Always offer **"No ticket, write it from source code understanding"** as an explicit,
legitimate option alongside providing one, not just a silent fallback. Every domain in this app
is still early enough that this fallback will likely be the common case for a while; that's fine,
Step 4's honest-summary fallback covers exactly this.

**Ask whenever something needed to write accurately is genuinely unclear**: the business reason
for a behaviour, which of two plausible groupings is right, whether a change is significant
enough to warrant its own feature page, or anything else a quick question would resolve. Ask in
the moment rather than guessing; only fall back to flagging the gap in the Step 8 report (per
Step 5) when there's truly no way to ask before finishing the pass.

**Done when:** every feature from Step 2 is classified as new domain / new module / new feature /
update / remove, and any new-structure or missing-context questions have been put to the user
before writing begins.

---

## Step 4: Write the domain / module overview

A domain's overview page is not a list of links with a one-line blurb. It's a summary of _why the
domain exists_. Where real problem/goals/scope context is available (tickets, PRDs, discovery
docs, or a clear pattern in the commit history), use it.

When you're actually writing a domain or module overview from scratch (Step 3's "new domain" /
"new module" case, or a thin existing overview the user asked to have filled in properly), see
[references/domain-overview.md](references/domain-overview.md) for the heading toolkit, the bar
for including a Key Decision, and the honest-summary fallback for domains with no discovery
context. Skip that reference entirely on a plain update pass; the Permissions requirement below is
the only part of this step that applies on every pass regardless.

A module's overview page (when it needs one) is the same idea at smaller scale: what capability
this module groups, why these features belong together, one paragraph is usually enough. It
rarely needs the full heading toolkit.

**Every module overview also needs a brief Permissions section, kept current on every pass, not
just when the module is new.** This is the one exception to "skip Step 4 for update": whenever a
change touches what an authorization check gates anywhere in this module (a Spatie permission, a
Policy method, either of this app's two real authorization mechanisms — see the `apply-permissions`
skill for what those actually are), add or refresh a **Permissions** section on the module's
overview page, even if nothing else about the overview changed. The module-level section is a
*briefing*, not the full explanation; the full behavior for each check lives on the feature page
it actually gates (Step 5). Keep this section to:

- One or two sentences on which part of this module authorization governs (which actions, and
  whether the check is a Spatie permission or a Policy method), without re-explaining the
  mechanism itself, that belongs on the feature page.
- A table of every check used anywhere in this module, one row each:

  | Check | Mechanism | Description |
  |---|---|---|
  | View Team | Policy (`TeamPolicy::view`) | Requires membership on the team. |
  | Update Team | Policy (`TeamPolicy::update`) | Requires the `team:update` team permission. |
  | Manage Integrations | Spatie permission | Gates access to the Integrations screen entirely. |

  Link the check name (or a "see X" cell in Description) to the feature page's own Permissions
  section rather than repeating that page's explanation here.
- If the module genuinely has no authorization check of its own (everything in it rides on a
  parent resource's check, or it's not gated at all), skip the table and write one line: "This
  module has no permission checks of its own." Say what it inherits from, if anything, in that
  same line.

**Done when:** the domain/module overview reflects current reality and uses only headings that
earn their place for *this* domain, and every module touched by this pass has an accurate,
up-to-date Permissions section (briefing + table, or the one-line "no checks" note) regardless of
whether the rest of Step 4 applied.

---

## Step 5: Write / update / remove the feature markdown

**Frontmatter** on every page (adapt the exact keys to whatever the chosen docs tool expects, once
one exists; this is the informational shape to preserve regardless of tool):

```yaml
---
title: <Feature name>
domain: <Domain display name>
module: <Module display name>
order: <position within module, integer>
---
```

Cross-cutting pages under `platform/` use `section: Platform` instead of `domain`/`module`.

**File location:** `<docs-root>/<domain-slug>/<module-slug>/<feature-slug>.md`, kebab-case
throughout, with `<docs-root>` being wherever Step 0 established the site lives.

**Structure a feature page around behaviour, not endpoints.** Open with one or two sentences on
what the feature is and when it applies. Never open with an HTTP verb and path. Then use whichever
of these sections actually fit the feature; this is a toolkit, not a checklist. Most features need
two or three of them, not all:

- **Scenarios**: concrete situations that trigger or use this feature. Write them as short
  narratives, not as parameter combinations. When a feature has several distinct actor-driven use
  cases and a narrative would get repetitive, a user-story list is a fine alternative: "As a
  `<actor>`, I want `<goal>`, so that `<benefit>`." Pick whichever format reads better for this
  feature, not both.
- **Workflow**: the step-by-step sequence of what happens, in order. Numbered steps, plain
  language. This is where you explain _mechanism_ (what triggers what, what happens async vs.
  sync, what the system checks before proceeding) without listing request fields.
- **Constraints**: rules, limits, and invariants (what's not allowed, what can never happen, and
  why). The "why" is often the most valuable part.
- **Prerequisites**: what must already be true or set up before this feature can be used.
- **Edge cases / gotchas**: `> blockquote` callouts for non-obvious behaviour (ordering
  guarantees, dedup logic, security rationale, side effects).
- **Permissions**: add this section whenever the feature is gated by a Spatie permission or a
  Policy check. This is the canonical, full explanation, the module overview page (Step 4) only
  briefs and links here, don't duplicate the explanation there. Start with a table:

  | Check | Mechanism | Description |
  |---|---|---|
  | Update Team | Policy (`TeamPolicy::update`) | Requires the `team:update` team permission on this specific team. |

  Below the table, in prose, cover whatever actually applies to *this* feature: what the check
  actually gates in plain product language (no class names, no internal method names beyond what
  the table already states plainly), and, for a Policy-based check with a real ownership/role
  concept, what that concept means in product terms (e.g. "any team member can view it; only the
  team owner or someone with the right team role can update it").

**Link to the API, don't document it**, once this app has both a docs site and a live API
reference for the endpoint (see `document-api`; neither exists yet at the time of writing). Until
then, describe the endpoint's existence and purpose in prose without a formal linked reference,
and note in your Step 8 report that the API-reference link is pending until `document-api`
becomes usable.

Do not include parameter tables, full JSON request/response bodies, or field-by-field validation
lists. That's what an API reference is for, and it will drift out of sync with this hand-written
page if duplicated here. The one exception: a single short illustrative JSON snippet is fine
_only_ when prose genuinely can't convey the shape as clearly. Use judgement; default to leaving
it out.

**When updating an existing page:** edit in place, don't append a changelog or a "recently
updated" note. The page should always read as current truth, not a diff log.

**When removing:** delete the page (or the stale section) and remove every link to it: nav/
sidebar, domain overview, module overview, home page feature list if applicable.

If the code doesn't make the _business reason_ for a behaviour clear (only tests and comments in
code + PR context might tell you, and it's genuinely ambiguous), write the observable behaviour
plainly and flag the gap in your final report rather than inventing a rationale.

**Never cite file paths, line numbers, or class names in the page body.** They're accurate today
and wrong within a week. The next refactor breaks them silently, and nobody revisits a docs page
to fix a dangling reference. Describe behaviour in product terms only; the code itself is the
source of truth for where it lives.

**Done when:** every feature classified in Step 3 has a page that's created, updated, or removed to
match, and every such page passes the narrative test above.

---

## Step 6: Update the nav/sidebar and index pages

Once a docs site exists, update whatever nav/sidebar configuration it uses to reflect Step 5's
changes: a new domain gets a top-level entry, a new module gets a group under its domain, a new
feature gets an entry in the right module group in `order`, and anything removed gets its entry
deleted. Also update the domain's own module list and, for a brand-new domain, any home-page
feature list.

**Done when:** every page that exists after Step 5 has exactly one nav/sidebar entry, and no
entry points at a page that no longer exists.

---

## Step 7: Validate

Run whatever build/link-check command the chosen docs tool provides (a VitePress-based site would
use `npm run docs:build`, for instance; adapt to whatever was actually set up in Step 0). It must
succeed with no broken internal links. Fix any before finishing, and clean up any build artifacts
the tool leaves in the working tree afterward, the same way a gitignored build-output folder
shouldn't be left sitting around after a validation build.

**Done when:** the build/validation step exits clean with zero broken-link errors, and no
build-output artifacts are left in the working tree afterward.

---

## Step 8: Report back

Summarize concisely:

- Whether a docs site existed already, was created this pass (with what tool, per Step 0), or
  was declined in favor of the chat-response fallback.
- Domain(s) / module(s) / feature(s) touched, and which action was taken for each (new / updated /
  removed).
- File paths of every page created, edited, or deleted (or, for the chat-response fallback, a note
  that the narrative was delivered in chat only and not persisted).
- Anything flagged as ambiguous business intent per Step 5, needing a human answer.
- Confirmation the build passed, if a build step applied.

**Done when:** the user could locate and review every changed page from this summary alone,
without re-running `git status` themselves.

---

## Principles

- This command never touches PHPDoc, inline code comments, or anything outside the docs site's
  own structure (once one exists) and its config.
- **Never scaffold a docs site or install a docs dependency without the explicit go-ahead Step 0
  requires.** This project's own rules forbid creating new base folders or adding dependencies
  without approval, and this skill existing is not, on its own, that approval.
- **This is not API documentation.** If a page is turning into a list of endpoints with parameter
  tables and JSON bodies, stop and restructure it around scenarios/workflow/constraints instead.
  The test: could a reader who's never seen the API still understand what the feature does and why
  its rules exist, purely from this page? If the page's main content is HTTP contracts, it has
  failed that test regardless of how accurate the contracts are.
- Docs describe behaviour as it exists after this branch's changes, not the diff and not history.
- Prefer updating an existing feature page over creating a near-duplicate one; prefer one feature
  page per coherent flow over one page per class or one page per endpoint.
- If a change is purely internal refactor with no observable behaviour difference (same request
  contract, same response, same business rules), say so and make no doc changes. Don't pad the
  site with a page that adds no information.
- The narrative is the deliverable: plain language, concrete scenarios, and the "why" behind every
  constraint, not the tone of a generated API reference.
