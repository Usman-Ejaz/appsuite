# PHPDoc Style Rules

These apply to every docblock you write or edit in this skill, from Step 3 through Step 6,
whether or not a generator is installed (see `SKILL.md` Step 0). Step 8's pre-completion
checklist rechecks the internal-leak rule specifically; the rest are on you to apply as you
write, not just at the end.

- **Always multi-line blocks.** `/** ... */` on one line is never acceptable here, even for a
  single short sentence:

  ```php
  /**
   * Short sentence here.
   */
  ```

- **Never expose internal logic, code, or database structure.** No class, service, job, queue, or
  event names; no table or column names; no cache keys or feature flag names; no fenced code
  blocks or JSON dumps in the prose. A short example *value* (`@example 2026-01-15T12:00:00Z`) is
  fine, that's data, not code. This rule is about *this app's own* server-side implementation, not
  about withholding integration instructions a caller genuinely needs. If an endpoint can't be
  called correctly without a client-side computation (constructing a signature for a required
  header, for instance), documenting that algorithm, with a short code example in the caller's
  language, is exactly what the endpoint's PHPDoc is for, that's not "our code," it's the
  caller's code. Don't invoke the "keep it short" rule below to cut it down either, this is the
  one kind of content that's allowed to run long and keep its example, because the endpoint is
  unusable without it.
- **This includes internal names dressed up as an aside, not just a bare identifier list.** A
  parenthetical like "(see `Domains\Identity\Models\Company`)" or "stored on the
  `role_permissions` pivot" is exactly the class/table name the bullet above already forbids, it
  doesn't stop being one just because it's phrased as an explanation instead of a flat name. This
  has actually slipped through before because it reads like ordinary prose while writing it. Same
  goes for Eloquent-internal vocabulary with no class name attached at all, "relation",
  "per-model", "pivot", describe the field or behavior in plain resource terms instead. Never
  write the word "model", a public third-party developer doesn't recognize backend ORM
  vocabulary, describe the actual resource or record by name instead (say "the company this API
  key belongs to," not "the related model"). Before finishing a pass, grep every docblock you
  touched for `Domains\`, `Models\`, and the literal word `model`, catching these by rereading
  each sentence is unreliable, they read like normal explanations at the time they're written.
- **Write for a public third-party developer.** No internal jargon, no domain/module names that
  aren't also the public field or endpoint name, no "we" or "our system" voice, no ticket or PR
  references.
- **No em dashes or en dashes anywhere you write.** Rephrase with a comma, a period, or "and" /
  "with" instead.
- **Wrap field names and literal values in backticks**, `company_id`, `is_active`, `Integration`,
  so they read as identifiers rather than plain English words. When the value is one the API
  itself accepts or returns (a field name, an enum value, a polymorphic type name), write it
  exactly as the API spells it, matching its real casing, not a lowercased paraphrase.
- **Keep it short.** One sentence for most fields and enum cases. Two to four sentences for an
  endpoint description or a genuinely nuanced field. If a field's behaviour needs more than that
  to explain honestly, that nuance belongs on the product-facing docs (once this app has a docs
  site, per the `document` skill), add one short pointer sentence here rather than letting PHPDoc
  grow into an essay. The one exception is the client-side integration content described above (an
  algorithm the caller must implement to use the endpoint at all), that's allowed to run long,
  because without it the endpoint can't be called correctly, so trimming it isn't a style win,
  it's a real information loss.
