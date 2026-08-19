# Domain / Module Overview Toolkit

Reference for the `document` skill's Step 4. Only needed when actually writing a domain or module
overview from scratch: Step 3's "new domain" / "new module" case, or when the user asks for a
fuller write-up of an existing thin one. Skip this file entirely on a plain feature-update pass;
Step 4's Permissions-section requirement (in `SKILL.md` itself) is the only part of that step that
applies regardless.

## The six domains, honestly, as of this writing

Every domain's own `Domains/<Name>/module.json` has an empty `"description"` field today, and no
domain has a README of its own yet. There is no discovery-level context to pull a real
Problem/Goals/Scope from for any of them. What follows is a plain, honest purpose summary inferred
from each domain's actual models and controllers, not an invented narrative, matching the
"honest-summary fallback" described below. Re-verify against the real code before publishing;
these are early-stage, and several are still thin stubs that will fill in over time:

- **Auth** — Authentication: `LoginController`, `TokenController`. Handles how a user
  authenticates and how API tokens get issued.
- **CMS** — Content management. Currently a thin stub (a single `CMSController`); its real scope
  hasn't taken shape yet.
- **Core** — Cross-domain platform primitives: `App` (an application/tenant-scoping model that
  Identity's `Permission` model relates to) and `Integration` (third-party integration records),
  plus `BaseModel`/`BaseRepository`, the shared base classes other domains build on. Also owns a
  small handful of controllers (`AppController`, `IntegrationController`) for managing those
  records directly.
- **Ecommerce** — Commerce features. Currently a thin stub (`EcommerceController`, no models
  yet); its real scope hasn't taken shape.
- **Identity** — Users, companies, and access control: `User`, `Company`, `Customer`, `ApiKey`,
  and the `spatie/laravel-permission`-backed `Permission`/`Role` models. This is where
  authentication's underlying user record and the app's system-level RBAC live; see the
  `apply-permissions` skill for the real mechanics.
- **Shared** — Cross-cutting content/catalog primitives used across domains: `Page` and
  `Product` models today. Note the naming is a little counter-intuitive: `Product` living under
  `Shared` rather than `Ecommerce` is worth confirming with the user before assuming it's settled,
  rather than treating the current location as obviously correct just because it's where the code
  happens to be.

## Heading toolkit

Below is a standard toolkit of headings used in software design docs, PRDs, and RFCs. **This is
not a fixed template.** Pick whichever fit this specific domain and drop the rest; rename or
reorder freely; add a heading that isn't listed if the domain calls for it. A small utility domain
might need only Overview + Scope. A domain with real product discovery behind it might use most of
these. The goal is a summary that's actually useful for *this* domain, not adherence to a
checklist:

- **Overview / Summary**: one paragraph on what this domain is and for whom
- **Background / Context**: the prior state this domain exists in reaction to
- **Problem**: the specific pain or gap that motivated building this
- **Goals** / **Non-Goals**: what it's meant to achieve, and what's explicitly _not_ trying to be solved
- **Proposed Solution / Approach**: how it addresses the problem, in a sentence or two
- **Scope**: in scope (current phase) vs. out of scope / next phase, what's deliberately deferred
- **Success Metrics / Success Criteria**: concrete, observable outcomes that indicate this is working
- **Key Decisions**: locked-in judgment calls worth surfacing rather than re-litigating
- **Risks / Open Questions**: known unknowns or trade-offs, if any are worth flagging
- **Future Work**: planned follow-on phases, if distinct from Scope

## The bar for a Key Decision

**Keep Key Decisions to the ones that actually earn a mention.** Include a decision only when all
three are true: it would be **hard to reverse**, it would be **surprising** to a future reader who
didn't know the history ("why on earth does it work this way?"), and it came from a **real
trade-off** between genuine alternatives. Skip anything that's just "we did the obvious thing."
That's not a decision worth recording; it's the default. Each one gets one to three sentences: what
was decided, and why. Don't pad a thin list to look thorough.

## When there's no discovery context

If no discovery-level context is available for a domain (currently true for all six domains in
this app), don't fabricate one. Write a shorter, honest purpose summary (what this domain covers
and for whom, per the plain descriptions above) and note that a fuller write-up is pending. A
short truthful overview beats an invented "Problem/Goals" structure with made-up content.
