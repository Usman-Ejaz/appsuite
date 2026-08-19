# Scramble: Not Installed Yet — What It Would Take, and Today's Fallback

This file exists because the skill's Step 0 may lead a future pass to choose Scramble
(`dedoc/scramble`) as this app's API-doc generator, and because a later reader should understand
exactly what changes the day that happens. **Scramble is not installed in this app today** —
verified with `grep -i scramble composer.json`, which returns nothing. Until it (or some other
generator) is actually added, `document-api` operates in **PHPDoc-only fallback mode**, described
at the end of this file.

## What adding Scramble would actually take

If a future pass gets explicit user approval (per this project's dependency-approval rule, and
per `SKILL.md` Step 0) to add Scramble specifically:

1. `composer require dedoc/scramble` (a real new dependency, needs explicit sign-off, not just
   "yes, add a generator" in the abstract, unless the user names Scramble specifically).
2. Scramble auto-registers its own service provider; publish its config with
   `php artisan vendor:publish --tag=scramble-config` to get `config/scramble.php`, since several
   of this skill's own rules (enum-case rendering, in particular) depend on a setting in that
   file.
3. Confirm the routes Scramble should document actually match this app's real API surface.
   Scramble's default detection looks for routes under an `api/{version}/` -shaped URI prefix; in
   this app, each domain owns its own `Domains/<Domain>/routes/api.php`
   (`Auth`/`CMS`/`Core`/`Ecommerce`/`Identity`/`Shared` all have one today), so confirm Scramble's
   own route-matching config actually picks up whatever URI prefix those get registered under by
   each domain's own module service provider, rather than assuming the default matches this app's
   actual routing without checking.
4. The generated document becomes available at whatever path Scramble's config assigns (commonly
   `/docs/api` or `/api/documentation` by default, configurable) — confirm and record the real
   path once set up, don't assume a path from a different project's setup.
5. Once installed, this skill's Steps 3-6 would follow Scramble's real mechanics, summarized
   below from Scramble's own documented behavior (not yet verified against a live install in this
   app, since none exists — re-verify against the actual exported schema the first time this
   applies, per this skill's own Step 8):

   - **Endpoint title and description** come from the controller method's own PHPDoc: the first
     line becomes the short title, a blank line, then everything after becomes the description.
   - **Request parameters** are derived automatically from a `rules()` array; types,
     required/optional, and constraints come from the actual validation rules. A field's prose
     description comes only from a PHPDoc comment placed directly above that field's array key,
     with an optional `@example` (and `@default`) tag inside the same comment.
   - **Response shape** is inferred by reading the Resource or Collection's `toArray()`. Relations
     returned through `whenLoaded()` need an inline `@var <ResourceClass>` (or
     `<ResourceCollectionClass>`) comment directly above that array item to resolve to a named
     type instead of a generic inferred shape.
   - **Enum values** get their description from PHPDoc above the `enum` declaration and above
     each `case`. Per-case descriptions only render if `config/scramble.php`'s
     `enum_cases_description_strategy` is set to `description` or `extension` — this is exactly
     the config-switch check this skill's Step 0 would need to add back in, the day Scramble
     actually gets installed.
   - **Manual attributes** (`#[QueryParameter]`, `#[BodyParameter]`, `#[HeaderParameter]`,
     `#[Response]`, `#[Endpoint]`, `#[Group]`) exist for cases static analysis can't reach.
   - **`#[ExcludeAllRoutesFromDocs]`** on a controller removes every one of its routes from the
     generated document entirely.
   - Common error responses (404 on route model binding, 401/403 from auth middleware, 422 from
     validation) are added automatically from the route and the code.

   Validate with `php artisan scramble:analyze` and `php artisan scramble:export` the same way a
   project with Scramble already installed would, once those commands actually exist in this
   app's `artisan list`.

## Today's fallback: PHPDoc-only mode

Until a generator like this exists in the app, `document-api`'s Steps 3-6 still apply, but their
payoff is different: the PHPDoc you write is read directly by a human (a future developer, an
external API consumer reading the source if this were ever open, or a future generator once
installed), not rendered automatically into a browsable schema today. Because there is no static
analyzer double-checking placement, the discipline that matters most in this mode is:

- Writing complete sentences, not fragments, since there's no rendered UI filling in the missing
  context around a terse comment.
- Not skipping a field just because "the generator would infer it anyway" — there is no generator
  inferring anything right now, so an unlabeled field is genuinely undocumented, not just
  under-annotated.
- Keeping the same structural rules (multi-line blocks, no internal leakage, backticked field
  names) as generator mode, since these are good PHPDoc practice independent of whether a tool is
  reading them, and because writing them this way now means adding a generator later is a
  rendering change, not a rewrite.
