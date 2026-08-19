---
name: document-api
description: >
    Document this app's real API surface (Controllers, FormRequests, Resources/Collections, and
    Enums under `Domains/*/routes/api.php` and `Domains/*/app/Http`) so it reads well for external
    developers integrating against it. This app does not have an OpenAPI/API-doc generator
    installed yet (no Scramble, no equivalent) — see Step 0, which checks for one and, if none
    exists, asks whether to add one (subject to this project's own approval-before-dependencies
    rule) or fall back to writing thorough, generator-agnostic PHPDoc that stands on its own and
    is ready to be picked up automatically the day a generator is added. Use this whenever the
    user asks to document an endpoint, write or fix API docs, add PHPDoc to a
    controller/request/resource/enum, backfill or update API documentation for whatever changed
    on the current branch, or says something like "document this API", "add API docs for X",
    "write the PHPDoc for this endpoint", even without naming this skill or typing "/document-api"
    directly. Output lives inside the PHP source itself, never a docs site (that's the `document`
    skill's job for the product-facing narrative), and must never expose this app's internal
    logic, class/table names, or DB structure to the public reference.
---

# Document an API Endpoint

You are annotating this app's real API source, Controllers, FormRequests, Resources,
Collections, and Enums under `Domains/*/app/Http`, `app/Http`, `Domains/*/app/Enums`, and
`app/Enums`, with PHPDoc so a developer who has never seen this codebase and never will can
integrate against it. This is **not** the product-facing feature documentation: that (handled by
the `document` skill, once a docs site exists for this app) tells the product narrative for
Product, Tech, and QA. This skill writes the contract, the request shape, the response shape, and
the allowed values.

Every line you write should read like a public API reference: plain, direct, and complete enough
to integrate against, without ever describing how the app works internally.

---

## Before you start: this app has no API-doc generator installed yet

Unlike a project where Scramble (or an equivalent) is already wired up and rendering
`/api/v1/docs` automatically from PHPDoc, **this app has no such tool today.**
`grep -i scramble composer.json` (and every reasonable equivalent name) comes back empty. That
doesn't mean this skill is inapplicable, though: this app has a real, live API surface —
`Domains/{Auth,CMS,Core,Ecommerce,Identity,Shared}/routes/api.php` all exist and are wired into
their respective module service providers — it just means the payoff of writing PHPDoc (an
automatically rendered, browsable reference) isn't there yet.

## Step 0: Check for a generator, and choose a mode

```bash
grep -i "scramble\|dedoc\|zircote/swagger-php\|apiato\|l5-swagger" composer.json
```

**If one is found:** a generator has been added since this file was last accurate; read its own
docs/config first (its mechanics will differ from the fallback mode this file describes) and
adapt the steps below accordingly, particularly Steps 3-6's placement rules and Step 8's
validation command.

**If none is found (the current, expected state):** ask the user, once, which they'd prefer:

> This app doesn't have an OpenAPI/API-doc generator installed yet (no Scramble or equivalent).
> Would you like me to add one now, so the PHPDoc I write actually renders into a browsable
> reference (this needs your explicit go-ahead before I add a new composer dependency, per this
> project's own rule), or should I write the PHPDoc in a generator-agnostic way for now, so it's
> solid and ready to be picked up automatically whichever generator you add later?

- **If the user wants a generator added:** get their explicit approval for the specific package
  before running `composer require`, per this project's dependency-approval rule; don't treat
  "yes, add one" alone as approval for a specific package choice unless they name one. Once
  installed and configured, this skill's mechanics converge on whatever that tool's own
  placement rules are (Scramble's mechanics, if that's the choice made, are described in
  `references/scramble-mechanics.md` for when this becomes relevant, since Scramble is likely the
  most natural fit for a Laravel app and the tool this reference set was originally written
  against).
- **If the user wants the fallback:** proceed in **PHPDoc-only mode** for the rest of this pass.
  Every step below still applies with one difference: you're writing complete, well-structured
  PHPDoc per this project's own PHP conventions (`CLAUDE.md`'s `php rules`: PHPDoc blocks
  preferred over inline comments, array shape type definitions used in PHPDoc) because it's good
  practice and pays off the moment a generator is added later, not because a specific static
  analyzer is reading it today. Skip Step 0's config-switch equivalent (there is no
  `enum_cases_description_strategy` to set without a generator installed) and skip Step 8's
  generator-specific validation; validate instead by having the PHPDoc read correctly as prose
  and by running the project's own test suite for the endpoints touched, confirming nothing about
  the actual behavior changed.

**Done when:** it's confirmed whether a generator exists, and if not, the user has explicitly
chosen "add one" or "PHPDoc-only fallback" for this pass.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** once you've confirmed a documentation placement pattern works (or renders
  nothing, if a generator is in play) for one endpoint or Resource shape, that pattern applies to
  every other endpoint using the same shape in the codebase. Don't re-verify it from scratch per
  endpoint; recognize the pattern and apply it directly.
- **Unfamiliar means:** whether a specific placement actually renders through a generator is not
  something to assume when the code shape is unusual (a ternary field, a polymorphic relation, a
  custom cast) — verify against the real exported schema (Step 8, generator mode) rather than
  trusting a general rule blindly. In PHPDoc-only mode, "unfamiliar" instead means: don't assume a
  field's meaning from its name alone when the underlying validation/cast logic is genuinely
  non-obvious; read it before describing it.
- **Learnings file:** `.claude/knowledge-base/skills/document-api.md` — whether a generator has
  been added since the last run (so a future pass doesn't have to re-ask Step 0's question),
  per-domain title conventions established beyond the base table in
  `references/title-conventions.md`, and any placement/casts situations that turned out to need a
  workaround.
- **Propose, don't silently re-decide:** if the same title-naming ambiguity keeps recurring
  across a domain, propose formalizing it as an addition to the table in
  `references/title-conventions.md` rather than re-deciding it ad hoc on every pass.

---

## Step 1: Scope the work

Run in parallel:

```bash
git rev-parse --abbrev-ref HEAD
git status --short
git diff main...HEAD --stat
git diff main...HEAD --name-only
```

Include uncommitted changes. Filter the changed-file list down to what's actually part of the API
surface: Controllers, FormRequests, Resources, Collections, and Enums referenced by any of those,
under `Domains/*/app/Http`, `app/Http`, `Domains/*/app/Enums`, `app/Enums`. If the user named a
specific endpoint, domain, or class instead, scope to that and skip the diff entirely.

Before documenting a controller or method, confirm it's actually reachable through a live,
registered route — check the relevant `Domains/<Domain>/routes/api.php` (all six domains have
one) or, for root-level API routes, wherever those are registered. A controller can exist and
even look fully wired up while having no live route at all (a leftover, never finished, or
already superseded), so verify against the actual routes file first rather than assuming a
method's existence means it's reachable. (If a generator is installed per Step 0, it will likely
have its own inclusion rules, e.g. a URI prefix requirement or an exclusion attribute — check its
docs for what determines "reachable" in its terms.)

**Done when:** every route, request, resource, collection, and enum in scope for this pass is
identified, and confirmed to actually be reachable through a live route.

---

## Step 2: Read the real contract before writing

For each endpoint in scope, read the full controller action, its FormRequest `rules()`, the
Resource or Collection `toArray()`, and every Enum it touches, end to end, not just the diff hunk,
so the description is accurate for the whole endpoint. Read deep enough to describe the contract
correctly, but everything you write stays at the contract level: what a field means and how to
use it, never how it is computed, validated, stored, queued, or which internal class or table
handles it.

**Done when:** you can state, for every endpoint in scope, what it does, what it accepts, what it
returns, and what values are allowed, without re-reading the code.

---

## Step 3: Document the endpoint

On the controller method:

```php
/**
 * Short title in a few words.
 *
 * One to three sentences on what this endpoint does and when a developer would call it. Mention
 * externally visible behaviour a consumer needs to know, not internal steps.
 */
```

- Group endpoints under a tag/category consistent with sibling endpoints in the same domain. If a
  generator with its own tag mechanism (e.g. Scramble's `@tags`) is installed, use its own syntax;
  in PHPDoc-only mode, a plain, consistent leading noun in the title serves the same purpose.
  Format the tag as `Domain, Human Plural Name`, a clean plural noun a reader would recognise,
  never the controller's own class name (`Integrations`, not `IntegrationControllers`).
- Note authentication requirements plainly in the description if the route genuinely has none
  (rare in this app; all six domains' `routes/api.php` files currently gate on `auth:sanctum`).
- Note deprecation with a one-line reason and, if there is one, the replacement endpoint. Never
  just delete the docs for a deprecated endpoint.
- Never restate the HTTP method or path in prose if a generator is already rendering those from
  the route; in PHPDoc-only mode where nothing else states them, it's fine (and useful) to name
  them plainly in the description since there's no other rendering to duplicate.
- **Never explain a specific field's meaning or behaviour in the endpoint description if that
  field already carries its own description in the request rules (Step 4) or the response
  Resource (Step 5).** Each fact belongs in exactly one place: on the field itself. Before adding
  a sentence to an endpoint description, ask "is this a fact about one named field, or about the
  endpoint/parameter *interaction* as a whole?" Only the second kind belongs in the endpoint
  description; the first kind is a duplicate the moment it exists in both places.

### Title conventions

Titles need to read the same way across the whole API, a developer scanning the operation list
shouldn't be able to tell which domain an endpoint came from just from how its title is shaped.
Before naming a new endpoint, check how sibling endpoints in the same domain already name the
same kind of operation and match that first. Read `references/title-conventions.md` for the full
pattern table before naming or renaming any endpoint in this pass.

**Done when:** every endpoint in scope has a title and description a public developer could read
without any other context, plus any tag/deprecation info set correctly, and every title in scope
matches `references/title-conventions.md` or an already-established, better-fitting local
convention.

---

## Step 4: Document request parameters

Directly above the field's array key, inside the FormRequest's `rules()` or an inline
`$request->validate([...])` call:

```php
public function rules(): array
{
    return [
        /**
         * The company this record is scoped to.
         *
         * @example 42
         */
        'company_id' => ['required', 'integer', 'exists:companies,id'],
    ];
}
```

- Describe what the field means and how it's used, not the validation rule itself, and not why
  the rule exists internally.
- Add `@example` with a realistic-looking value, never real customer data. Add `@default` only
  when the field genuinely has a default the caller should know about.
- Don't restate "required" or "optional" in prose if a generator is already deriving that from
  the rule; in PHPDoc-only mode it's fine to state it plainly, since nothing else will.
- If the field's value is a documented Enum, don't re-list the allowed values here once Step 6's
  enum documentation exists somewhere a reader would find it; just describe what the field
  represents. In PHPDoc-only mode, a short cross-reference to the enum class name in the
  description is reasonable since there's no schema to link to yet.
- There is no Qubuilder-style (or any other) shared query-parameter base class in this app for
  list/get endpoints (no `GetCollectionRequest`/`GetResourceRequest` equivalent exists — see
  `references/qubuilder-query-params.md` for what was checked). Each endpoint's own filter/sort/
  pagination parameters, if any, are plain fields on that endpoint's own `rules()` and get
  documented individually like any other field; there's no shared base class carrying that
  documentation for you.
- If there's no FormRequest (a raw `$request->input()` read), describe the parameter directly in
  the endpoint's own description (Step 3) instead, since there's no `rules()` array to attach a
  field-level comment to and, absent a generator with its own parameter-attribute mechanism,
  nowhere else for it to live.

**Done when:** every field a public developer would actually send has a plain description, and
enum-backed fields aren't duplicating value lists that already live on the enum.

---

## Step 5: Document the response

On the Resource or Collection:

- A plain scalar field (not a relation) only needs a short description when its name and type
  aren't already self-explanatory.
- Every relation field (`whenLoaded`, nested Resource/Collection calls) needs an inline PHPDoc
  comment directly above that array item: a short description of what the related record
  represents, and, if a generator that reads type-hinting comments is in play, a `@var
  <ResourceClass>` line naming the concrete type:

  ```php
  /**
   * The company this API key belongs to.
   *
   * @var \Domains\Identity\Http\Resources\CompanyResource
   */
  'company' => CompanyResource::make($this->whenLoaded('company')),
  ```

  If the related model has no Resource class yet, create one, don't leave the field undocumented.
- When a field can resolve to more than one Resource type at runtime (a polymorphic relation), a
  single type annotation can't express that. Fall back to a plain multi-line description naming
  the possible shapes instead. Name them using the exact literal values the API itself uses for
  that type, in backticks, not a generic lowercase noun.
- Add a short inline comment above a field only when its name and type don't already say what it
  is, a rolled-up count, a formatted string, a status flag, something derived rather than stored
  as-is. Describe what the value represents in output terms, never how it's derived.
- Don't add a class-level docblock that lists every field in prose; only annotate what a plain
  read of the array can't already tell a consumer.

**Done when:** every relation in scope resolves to a clearly-named type in its documentation,
every relation and every non-obvious field reads as a real sentence describing it, and the whole
Resource/Collection reads consistently, no field's PHPDoc looks structurally different from
another's.

---

## Step 6: Document Enums

```php
/**
 * The lifecycle state of an integration connection.
 */
enum IntegrationStatus: string
{
    /**
     * The integration is connected and actively syncing.
     */
    case Active = 'active';

    /**
     * The integration has been disconnected and is not syncing.
     */
    case Disconnected = 'disconnected';
}
```

- Class-level PHPDoc: one or two sentences on what the value represents to an API consumer.
- Per-case PHPDoc: one short sentence on what that specific value means or when it applies,
  written for someone integrating against the API, not the internal rule that produces it.
- Every case gets a comment, a schema with some documented values and one silent one reads as a
  mistake, not a shortcut.
- If a generator is installed and has its own enum-rendering config (Scramble's
  `enum_cases_description_strategy`, for instance), confirm it's actually set to render per-case
  descriptions before relying on them showing up; see `references/scramble-mechanics.md` for what
  that looks like specifically for Scramble, if that ends up being the generator chosen.

**Done when:** every Enum reachable from a request or response in scope has a class description
and a description on every case.

---

## Step 7: Style rules

Read `references/style-rules.md` for the full set with rationale; the two most commonly violated
ones are worth repeating here because they're easy to write without noticing:

- **Always multi-line blocks**, even for a single short sentence, never `/** ... */` on one line.
- **Never expose internal logic, code, or database structure**, including a class or table name
  dressed up as a parenthetical aside, or bare Eloquent vocabulary like "relation" or "pivot" with
  no name attached. Grep every file you touched for `Domains\`, `Models\`, and the literal word
  `model` before finishing (Step 8 does this too, but catch it as you write).

**Done when:** every docblock written or edited this pass follows every rule in
`references/style-rules.md`, not just the two repeated above.

---

## Step 8: Validate

**If a generator is installed:** run its own analysis/export commands (per whatever Step 0 found)
and confirm the descriptions/examples/enum values you added actually appear in the rendered
output, the same way a mis-placed PHPDoc comment silently renders nothing with these tools.

**In PHPDoc-only mode:** there is no schema to export. Validate instead by:

1. Re-reading every docblock you wrote as if you were the external developer it's for, confirming
   it reads as a complete, correct sentence with no internal leakage.
2. Running the actual test suite for the endpoints touched (`php artisan test <path/filter>`),
   confirming the behavior these docs describe still matches reality; PHPDoc describing a
   contract the code doesn't actually honor is worse than no PHPDoc.

### Pre-completion checklist

Before calling the pass done, run through every item below against the files you actually touched
this pass, don't rely on having "kept it in mind" while writing:

1. **Internal-leak sweep**, run this exact grep over every file you touched, then read each hit in
   context, `use` statements and validation rule strings (`exists:companies,id`) are fine, prose
   inside a `/** ... */` block is not:
   ```bash
   git diff --unified=0 -- <files you touched> | grep -nE 'Domains\\|Models\\|\bmodel\b|\brelation\b|\bpivot\b'
   ```
   Zero hits inside doc comments, or every hit rewritten in plain resource terms, before moving on.
2. **No duplicated facts.** For every endpoint description you wrote or edited, confirm no
   sentence in it restates something a request field or response field's own docblock already
   says (Step 3's rule). Reread the description once with only that question in mind.
3. **Every enum reachable in scope has a class description and a description on every case**
   (Step 6), and, if a generator is installed, its own rendering switch is actually set.
4. **Every relation field is clearly typed**, no bare, undescribed relation left undocumented when
   a named Resource/Collection exists for it (Step 5).
5. **Titles match the table in `references/title-conventions.md`** (or an established,
   better-fitting local convention), re-check this explicitly rather than trusting the title felt
   right when it was typed.
6. If a generator is installed, **the exported schema was actually opened and read**, not just
   exported, confirm by name that the specific fields/examples/enum values from this pass appear
   in the right schema at the right path.

**Done when:** every item in the pre-completion checklist has been explicitly confirmed, not
assumed, and (generator mode) the exported document shows the descriptions/examples/enum values
you added exactly where you intended.

---

## Step 9: Report back

Summarize concisely:

- Endpoints, requests, resources, collections, and enums touched, one line each on what was added.
- Whether this pass ran in generator mode or PHPDoc-only fallback mode (per Step 0), and if a
  generator was newly added this pass, what was installed and configured.
- Any related Resource/Collection class you had to create because one didn't exist.
- Any known, pre-existing gap you deliberately didn't try to paper over.
- Confirmation the validation step (generator export or test run) passed.

**Done when:** the user could locate and review every change from this summary alone.

---

## Principles

- This command only touches PHPDoc (and, if a generator is later added, its attributes/config),
  never a product docs site, that belongs to the `document` skill.
- A generator-mode PHPDoc comment the generator doesn't actually read is worse than no comment, it
  looks documented and isn't. Always verify placement against the specific generator's mechanics
  before writing, and check the exported schema in Step 8.
- In PHPDoc-only fallback mode, the same care about accuracy and completeness still applies,
  because it's the right way to document a public contract on its own merits, and because it's
  what makes the switch to a real generator later a formatting change, not a rewrite.
- If a change has no external contract effect (pure refactor, same request and response shape),
  say so and make no doc changes.
- **Say everything exactly once, in the one place it belongs.** Before writing a sentence in an
  endpoint description, check whether that same fact already renders on a specific field's own
  docblock; if it does, don't write it again in the description. The reverse also applies: don't
  put endpoint-level mechanics on an individual field's docblock just because it happens to be the
  field involved, that belongs in the endpoint description instead.
- The reader is a developer outside this company who will never see this codebase. Nothing
  written here should assume otherwise.
