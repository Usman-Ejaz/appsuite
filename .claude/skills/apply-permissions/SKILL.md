---
name: apply-permissions
description: >
    Plan and implement authorization (who can view, create, update, delete, or run a special
    action on a resource) for a Domain, Module, or Feature in this Laravel-modules monorepo,
    using whichever of this app's two real authorization mechanisms actually fits: system/domain-
    level RBAC via `spatie/laravel-permission` (roles and permissions on
    `Domains\Identity\Models\User`, with `is_root`/`is_owner` as bypass-all flags), or
    resource-scoped authorization via Laravel Policy classes (the Team feature under root `app/`
    being the one fully-wired example today). Use this whenever the user asks to add, wire up,
    extend, or fix authorization or "who can see/change this" on a domain or a model — including
    phrasing like "add permissions to X", "who should be allowed to update/delete this", "does
    this action need a Policy", "should this be a Spatie permission or a Policy", or "lock this
    down by role" — even when the user doesn't say "/apply-permissions" or name this skill
    directly. There is no OWN/TEAM/ALL access-scoping trait in this app and no
    `HasDataAccessScope`-style mechanism; don't invent or port one in under any name. Always
    produces a written plan and stops for explicit approval before changing any code — never jump
    straight to implementation. Usage: /apply-permissions <Domain[.Module[.Feature]]> or
    /apply-permissions <ModelName>
---

# Apply Permissions: Plan & Implement Authorization

Usage: `/apply-permissions <Domain>`, `/apply-permissions <Domain>.<Module>`, or
`/apply-permissions <ModelName>` (e.g. `/apply-permissions Identity.Company`,
`/apply-permissions Team`). If no argument is given, ask which Domain/Module/model this pass
targets — don't guess.

You are wiring up (or completing/fixing) authorization for one target: which users can view,
create, update, delete, or run a special action against it. This is different from its sibling
skills:

- Not `learn-domain`: that produces a general engineering briefing. This skill produces an
  **authorization-specific implementation plan**, though it reuses `learn-domain`'s briefing as a
  starting point when one already exists (Step 0 below).
- Not `document`/`document-api`: those write the product-facing docs and the public API
  reference *after* a feature ships. This skill does the engineering work of adding the
  authorization itself. Once implementation is approved and done, remind the user to run those
  two separately if this project ends up using them for this kind of change — don't run them
  automatically as part of this skill.

**There is no OWN/TEAM/ALL row-level access-scoping mechanism anywhere in this app**, and no
trait resembling `HasDataAccessScope`. If you've seen that pattern in another codebase, do not
carry it over here under any name; it doesn't fit what this app actually has. What this app
actually has, verified by reading the real code, is two independent mechanisms:

1. **System/domain-level RBAC**, via the `spatie/laravel-permission` package (a direct composer
   dependency). `Domains\Identity\Models\User` uses `Spatie\Permission\Traits\HasRoles`, and
   overrides `can(...$permissions): bool` to call `$this->hasAnyPermission($permissions)` — so
   plain Laravel `$user->can('some-permission')` / `@can` / `Gate::allows(...)` calls already
   route through Spatie's permission check for this User model, no extra wiring needed to make
   that connection. The same User model also carries `is_root` and `is_owner` boolean flags
   (columns, cast to `boolean`, with `isRoot()`/`isOwner()` accessor methods) — these are
   bypass-all flags, checked explicitly wherever "root/owner always wins regardless of granted
   permissions" is the intended behavior; they are not part of Spatie's own grant model and
   `can()` does not consult them automatically. `Domains\Identity\Models\Permission` and
   `Domains\Identity\Models\Role` extend Spatie's own `Permission`/`Role` models (configured in
   `config/permission.php`), with a few app-specific columns added: `Permission` adds `app_id`
   (belongs to `Domains\Core\Models\App`), `label`, `code`, `description`; `Role` adds
   `company_id` (belongs to `Domains\Identity\Models\Company`) and `is_active`. Use this
   mechanism for a system-wide or domain-wide capability that isn't about one specific record's
   ownership — "can this user manage integrations at all," "can this user view the permissions
   list."
2. **Resource-scoped authorization via Laravel Policies.** The Team feature (root `app/`, not
   yet a Domain module) is the one fully-wired example today: `app/Policies/TeamPolicy.php` gates
   `view`/`create`/`update`/`delete`/`leave`/`addMember`/`updateMember`/`removeMember`/
   `inviteMember`/`cancelInvitation` on `app/Models/Team.php`, and every method's actual check is
   one of three calls on the (imported, see the Gotcha below) User model:
   - `$user->belongsToTeam($team)` — does the user have a membership row on this team at all.
   - `$user->ownsTeam($team)` — is the user's role on this team `TeamRole::Owner`.
   - `$user->hasTeamPermission($team, TeamPermission::X)` — does the user's `TeamRole` on this
     team include this specific `TeamPermission`.

   All three are defined in `app/Concerns/HasTeams.php`, a trait mixed onto the User model (not
   inline on the model itself — trace here, not onto the model file, if you need to see or extend
   the actual logic). `app/Enums/TeamPermission.php` is a string-backed enum (`team:update`,
   `team:delete`, `member:add`, `member:update`, `member:remove`, `invitation:create`,
   `invitation:cancel` — a `resource:verb` shape). `app/Enums/TeamRole.php` (`Owner`, `Admin`,
   `Member`) maps each role to its allowed `TeamPermission`s via a `match` in `permissions()`,
   with `hasPermission()` doing the lookup `HasTeams::hasTeamPermission()` calls through to.
   **None of this rides on Spatie at all** — `TeamRole`/`TeamPermission` are plain PHP enums with
   no database rows of their own; a user's role on a team is just a string column on the
   `team_members` pivot (`Membership`), not a Spatie role assignment. Use this mechanism (a Policy
   class per model, checking whatever ownership/role concept that model actually has) for
   resource-scoped actions — "can this user update *this* record."

**A new domain adopting authorization should follow whichever of these two shapes actually fits**
— a Policy class for a model with a real per-record ownership/membership concept, a Spatie
permission name (checked or defined via the `Permission`/`Role` models) for a system-wide
capability with no per-record ownership to speak of. The two aren't mutually exclusive on the
same domain: a feature can gate "can view this module at all" with a Spatie permission and "can
edit *this specific record*" with a Policy. When it's genuinely not obvious which shape (or
both) fits the target, ask the user rather than picking one — this is exactly the kind of call
Step 1 exists to make explicit.

**Known gotcha, worth understanding before touching the Team feature at all:** `TeamPolicy.php`
and every other file that authorizes against a user in this feature (`HasTeams.php`'s own
`use` block, `app/Http/Controllers/Teams/*`, `app/Actions/Teams/*`, `app/Rules/ValidTeamInvitation.php`,
several factories and tests) import `App\Models\User`. **That class does not exist** —
`app/Models/User.php` was never created; the real User model lives at
`Domains\Identity\Models\User` and `config/auth.php` correctly points the `auth` guard at it.
Running `php artisan test tests/Feature/Teams/TeamTest.php` today fails every test with `Class
"App\Models\User" not found`. This is a pre-existing bug in the app, not something this skill
introduces or should silently "fix" as a side effect of an unrelated permissions pass — if a plan
touches any of these files, say so explicitly in Step 2 and ask whether fixing the `use` statements
(to `Domains\Identity\Models\User`) is in scope for this pass or belongs in its own, separate
fix. Don't assume it's already been fixed by the time you read this; verify with the test run
above if it matters to the current pass.

---

## Auto Learning

See [Auto Learning](../shared/auto-learning.md) for the shared mechanism (active throughout every
step below, not a one-time phase). Specific to this skill:

- **Wider means:** a rule confirmed while wiring up one domain or model (which of the two
  mechanisms fits a given shape, a Spatie permission-naming choice, a Policy registration
  pattern) is project-wide infrastructure the moment it's confirmed, not a quirk of that one
  target. Apply it directly to the next target rather than re-deriving or re-justifying it.
  Conversely, if this pass finds a genuinely new shape neither mechanism cleanly covers, that's a
  candidate to fold into `references/rules.md`, not just this run's plan.
- **Unfamiliar means:** don't guess at how a specific model's ownership or membership actually
  works (whether it has a pivot table, a role column, a "who owns this" concept at all). Read the
  model and its relations before proposing which mechanism fits in Step 1; a wrong guess here
  produces a plan the user approves without realizing it's wrong, which is worse than asking.
- **Learnings file:** `.claude/knowledge-base/skills/apply-permissions.md` — genuinely new
  ownership/authorization shapes encountered beyond "Policy checking a membership/role" and
  "Spatie permission check," any Spatie permission-naming convention that becomes established as
  real seeded data accumulates (see `references/naming-standard.md`'s note that no real
  convention exists yet, only a test fixture), and any naming/description drift noticed across
  domains worth eventually normalizing everywhere at once.
- **Propose folding into `references/rules.md`, don't silently apply a one-off:** if this pass
  has to invent an answer to something the rules don't cover, say so explicitly in Step 2 and
  propose adding it as a new rule, rather than deciding quietly and moving on. The next target's
  pass needs to inherit it, not rediscover it.

---

## Established Rules

`references/rules.md` holds the rule set this skill applies today. Because Team is currently the
*only* fully-wired example in this app, the rule set is deliberately smaller and more provisional
than a codebase with several implemented domains would have — it covers choosing between the two
mechanisms, the actual Policy method shapes already in use, the Spatie role/permission model
shape, the `is_root`/`is_owner` bypass, and the one known gotcha above. Treat it as the starting
point to extend as real passes on other domains confirm more shapes, not a closed, exhaustive
list the way a mature project's rules file would be.

**Read `references/rules.md` and `references/naming-standard.md` in full now, before Step 1.**
Both are short. Don't re-litigate the parts that are settled; apply them, and flag a deviation in
Step 2 only if the current target genuinely doesn't fit.

---

## Step 0: Resolve the target and read the existing shape

Resolve the argument to a Domain, a Domain + Module, or a specific model. If ambiguous or
missing, ask.

Read, in this order:

1. `.claude/knowledge-base/domains/<domain-slug>.md`, if it exists and is current for the target
   (same freshness check as `learn-domain`'s own Step 1: compare `generated_at_commit` against
   `git diff --name-only <sha>...HEAD -- Domains/<Domain>/`). If missing or stale, don't
   silently proceed on incomplete context — invoke `/learn-domain <Domain>` first, or read the
   domain directly the same way that skill's Direct Mode would, whichever is faster for the
   scope of this pass.
2. The target's controllers, FormRequests, and routes: which methods already have (or have
   commented-out) an `authorize()`/`$this->authorize()`/`Gate::` call, and where each one
   currently lives — this tells you what's already half-built versus what's genuinely new.
3. The target's model(s): does a `Policy` class already exist for it (check
   `app/Policies/` and, for a Domain module, wherever that domain registers its own policies —
   confirm there's an actual registration; a Policy class existing on disk with nothing binding
   it to its model via `Gate::policy()` or Laravel's naming-convention auto-discovery does
   nothing)? Does the target rely on any existing Spatie permission checks (`$user->can(...)`,
   `hasAnyPermission`, `hasRole`) already?
4. Whether any Spatie permissions/roles already exist for this target — check
   `Domains/Identity/database/seeders/IdentityDatabaseSeeder.php` (as of this writing it only
   seeds three per-company `Role` rows — `Editor`, `Viewer`, `Member` — with **no permissions
   attached to any of them**, and no `Permission` rows seeded at all) and grep the target's own
   domain for any `Permission::`/`Role::` creation. Don't assume a convention is already
   established anywhere in the app; verify before asserting one in Step 3.

**Done when:** the target's current authorization state (nothing wired up, partially wired up
with known gaps, or a genuine extension of something already correct) is understood well enough
to know which mechanism actually fits, and which of Step 1's questions already have an obvious
answer from the code itself (don't ask what's already unambiguous from the model).

---

## Step 1: Ask the standard configuration questions

Use `AskUserQuestion`, batched into as few rounds as make sense, for whatever Step 0 didn't
already answer unambiguously from the code:

- **Which mechanism fits** — a Policy (this target has a real per-record ownership/membership
  concept: a pivot table, a role/owner column, a "who can act on this specific record"
  question), a Spatie permission (a system-wide or domain-wide capability with no per-record
  ownership to speak of), or both (view/list gated by a permission, mutate-this-record gated by a
  Policy). If Step 0 found an obvious answer from the model's own shape, say so as a
  recommendation with a one-line reason rather than asking blind, but still let the user confirm
  or override it. Don't force a Policy onto a model with no ownership concept, and don't force a
  Spatie permission onto an action that's really "does this user own/belong to this specific
  record."
- **For a Policy**: which methods does this model need (mirror `TeamPolicy`'s
  view/create/update/delete plus whatever special actions apply), and what's the actual
  ownership/membership check for each — a column, a pivot relation, a role lookup? Recommend
  based on what Step 0 found on the model; ask to confirm the real column/relation names rather
  than guessing.
- **For a Spatie permission**: what's the permission's `name` (and, if this domain's convention
  ends up following the `code`/`label`/`description` shape `Permission`'s own fillable list
  supports, those too) — see `references/naming-standard.md` for the current, deliberately
  open state of this convention, and propose a concrete value rather than asking the user to
  invent one from nothing.
- **Bypass behavior** — should `is_root`/`is_owner` bypass this specific check the way they do
  elsewhere, or does this target have a reason they shouldn't? Most targets should say yes by
  default; ask only when there's a real reason to deviate.

**Done when:** every question that isn't already answered unambiguously by the code has been put
to the user, and the answers are specific enough to write concrete Policy/permission code from
(real method names, real permission names) rather than placeholders.

---

## Step 2: Surface ambiguity and new findings — always, even when nothing came up

Explicitly check, and say so either way: did this pass hit anything that doesn't cleanly fit
either mechanism, a business rule that changes what "who can act on this" should even mean for
this resource, an existing check whose current behavior contradicts what the code actually does,
or (if the target touches the Team feature) the `App\Models\User` gotcha described above? Bring
each one to the user as a specific question or a specific proposed resolution (per this skill's
own Auto Learning guidance), not a vague "anything else to flag?" If genuinely nothing came up,
say that plainly rather than manufacturing a finding to fill the step.

**Done when:** every genuine ambiguity or new shape found while reading the target has been
surfaced and resolved with the user, or the step has explicitly concluded there were none.

---

## Step 3: Audit existing permission/policy names against the current, open convention

For every Spatie permission this target already has (an extension pass) and every one this plan
is about to introduce (a from-scratch pass), check it against `references/naming-standard.md`.
Because real seeded permissions don't exist anywhere in this app yet (Step 0 confirms this every
run — don't skip re-checking it just because a previous pass already found the same thing), this
step is mostly about *proposing* a name that fits the one shape observed so far (the test fixture
in `Domains/Identity/tests/Feature/PermissionTest.php`: `name` as a lowercase dotted string like
`contacts.view`, `code` as the matching snake_case string `contacts_view`, `label` as a Title Case
phrase like `View Contacts`) and flagging it clearly as a proposal, not an established standard
being enforced. For a Policy target, check that method names match the Laravel convention
(`view`, `create`, `update`, `delete`, plus any custom action name) and that a custom action's
name reads as a verb a caller would recognize, the same way `TeamPolicy`'s `leave`/`addMember`/
`updateMember`/`removeMember`/`inviteMember`/`cancelInvitation` do.

Present any naming decision as a short list for the user to accept, adjust, or reject, the same
way Step 2's ambiguities are presented — don't silently pick a name and move on when this is
plausibly the first permission of its kind in the app; a real precedent doesn't exist yet for the
next pass to inherit, so getting it right (or documented as intentionally provisional) matters
more here than it would in a codebase with an established catalog.

**Done when:** every existing and proposed permission/policy name for this target has been
checked against `references/naming-standard.md`, and any naming decisions have been proposed to
the user rather than applied silently.

---

## Step 4: Write the implementation plan

Write a plan with: Context (what's missing/wrong today, and why), a numbered Design section per
concern:

- **Mechanism choice** — which of the two (or both) applies here, and why, per Step 1's answer.
- **Policy wiring** (if applicable) — the Policy class's methods, each one's real ownership/role
  check, and how it gets registered (Laravel's naming-convention auto-discovery if the model and
  Policy names match the convention it expects, or an explicit `Gate::policy()` call in a service
  provider if they don't — state which, don't assume discovery "just works" without confirming
  the naming lines up).
- **Spatie permission/role wiring** (if applicable) — the permission's `name` (and `code`/`label`/
  `description` if this domain follows that shape), which `App`/`Company` it's scoped under if
  relevant, and where the grant actually gets checked (`$user->can(...)`, a Policy method calling
  through to `hasAnyPermission`, a route middleware).
- **Bypass behavior** — where `is_root`/`is_owner` short-circuits the check, per Step 1's answer.
- **The `App\Models\User` gotcha** (only if the plan touches the Team feature) — state plainly
  whether this pass is fixing those `use` statements as part of its own scope, or leaving them as
  a known, separately-tracked issue; don't let the plan go silent on a bug it will otherwise walk
  straight into.

Close with a Test plan (deny/allow per Policy method or permission, the bypass flags behaving as
expected, a regression test for anything Step 2 or Step 3 surfaced) and a Verification section
(`php artisan test <Domain>` or the specific test path, plus `php artisan test tests/Feature/Teams`
if the Team feature was touched at all, given its current broken state is worth re-confirming
either way).

**Done when:** a complete, concrete plan exists with real file paths, real method/permission
names, and an explicit statement of which mechanism was chosen and why, rather than a placeholder
acknowledging a decision was made.

---

## Step 5: Get explicit approval before writing any code

Present the plan as real, rendered markdown in the chat response, not wrapped in a code fence.
See [Output formatting](../shared/output-formatting.md), a fenced plan is much harder to read and
defeats the point of showing it for approval.

Present the plan and stop. Do not create or edit a single file — model, Policy, seeder,
controller, Request, or test — until the user has explicitly approved it. This holds even if
Steps 1-3 felt completely unambiguous; the plan itself is the checkpoint, not a formality to rush
past. If the user asks for changes, revise the plan and ask again rather than treating a partial
"sounds good, but..." as a green light to start.

**Done when:** the user has given explicit, unambiguous approval to proceed, or has asked for
changes that this step then incorporates before asking again.

---

## Step 6: Implement

Once approved, execute the plan in the order it was written. Follow `references/rules.md`
exactly as specified, no substitutions:

1. Policy class (if applicable): create with `php artisan make:policy`, methods per the approved
   plan, register it explicitly if naming-convention auto-discovery won't find it on its own.
2. Spatie permission/role rows (if applicable): create via the target domain's own seeder (or
   `Domains/Identity/database/seeders/IdentityDatabaseSeeder.php` if none exists yet for this
   domain), using the name/code/label/description from the approved plan.
3. Controllers/Requests: add the `$this->authorize(...)` / `Gate::allows(...)` /
   `$request->user()->can(...)` call at the point the plan specifies.
4. Bypass wiring: confirm `is_root`/`is_owner` short-circuit where the plan calls for it.
5. The `App\Models\User` fix, only if the approved plan put it in scope for this pass.
6. Tests, matching the plan's Test plan section.

Run the plan's Verification commands. If anything fails, fix it before reporting completion; a
plan that was approved is not a substitute for confirming the implementation actually matches it.

**Done when:** every Design section from the approved plan has a corresponding code change, every
test in the Test plan passes, and Verification's commands run clean.

---

## Step 7: Report and hand off

Summarize what changed and confirm tests are clean. Remind the user that once implementation is
approved and done, the product-facing docs (`document`) and, if this app later adopts an API-doc
generator, the API reference (`document-api`) are separate follow-up passes this skill doesn't
run automatically. Don't commit unless separately asked to.

If this pass added a new Spatie permission or role, remind the user that seeded rows only take
effect wherever the relevant seeder actually gets run (locally now, and again wherever this
migrates to a shared environment) — there is no resync/migration command in this app today the
way a more mature permission catalog might have; a new permission is simply a new row the next
seeder run creates. If a later pass in this app ever needs to rename or retire a live Spatie
permission that real accounts already hold grants on, that's a new problem this skill hasn't had
to solve yet (no such data-migration tooling exists here); flag it to the user rather than
inventing a mechanism on the spot.

**Done when:** the summary is delivered, the follow-up reminder has been given, and no commit was
made unless the user explicitly asked for one.
