# Established Rules

The rule set behind `/apply-permissions`, grounded in the one real implementation this app has
today (the Team feature's Policy-based authorization) plus the verified shape of the
`spatie/laravel-permission` wiring on `Domains\Identity\Models\User`. Because Team is currently
the *only* fully-wired example, this file is deliberately smaller than a project with several
implemented domains behind it — extend it as real passes on other domains and models confirm more
shapes, and say so explicitly in `SKILL.md`'s Step 2 when that happens, rather than silently
letting this file go stale.

## Contents

1. [Two independent mechanisms — there is no third](#1-two-independent-mechanisms--there-is-no-third)
2. [Choosing between them](#2-choosing-between-them)
3. [Policy shape — mirror TeamPolicy](#3-policy-shape--mirror-teampolicy)
4. [Spatie permission shape](#4-spatie-permission-shape)
5. [The `is_root` / `is_owner` bypass](#5-the-is_root--is_owner-bypass)
6. [The `App\Models\User` gotcha](#6-the-appmodelsuser-gotcha)
7. [No resync/migration tooling exists yet](#7-no-resyncmigration-tooling-exists-yet)

---

## 1. Two independent mechanisms — there is no third

Confirmed by reading the actual code, not inferred:

- **Spatie roles/permissions** on `Domains\Identity\Models\User` (`use HasRoles`), with
  `Domains\Identity\Models\{Permission,Role}` extending Spatie's own models
  (`config/permission.php` points Spatie's `models.permission`/`models.role` at them). `User`
  overrides `can(...$permissions): bool` to call `$this->hasAnyPermission($permissions)`, so
  ordinary Laravel authorization calls (`$user->can(...)`, `@can`, `Gate::allows(...)` against a
  permission name with no second policy-model argument) already route through Spatie for this
  User model.
- **Laravel Policies** for resource-scoped, per-record authorization. `app/Policies/TeamPolicy.php`
  against `app/Models/Team.php` is the one real, live example. It does not use Spatie at all;
  every check resolves through `app/Concerns/HasTeams.php` (`belongsToTeam`, `ownsTeam`,
  `hasTeamPermission`) against `app/Enums/{TeamRole,TeamPermission}.php`, both plain PHP enums
  with no database-backed permission catalog.

**Do not invent a third mechanism** (an access-level trait, a scope type, anything resembling
row-level OWN/TEAM/ALL scoping) — nothing like that exists anywhere in this app, verified by
reading every model and trait under `app/` and `Domains/*/app/`. If a future requirement seems to
need one, that's a real, separate design decision for the user to make deliberately, not
something this skill should improvise into an existing pass.

## 2. Choosing between them

- A model with a real per-record ownership or membership concept (a pivot table, a role column,
  "does this user belong to/own this specific record") → **Policy**. Team is the template: one
  Policy class, one method per action, each method resolving the actual ownership/role check.
- A capability with no per-record ownership to speak of (a domain-wide or system-wide "can this
  user do this at all") → **Spatie permission**, checked via `$user->can('permission.name')` or
  `Gate::allows('permission.name')` with no second argument.
- Both can apply to the same resource at different granularities: a Spatie permission gating
  whether a user can see a module at all, a Policy gating whether they can act on one specific
  record within it. Don't treat this as a contradiction; state both explicitly in the plan when
  it applies.
- A model with genuinely no ownership concept and no need for a per-record check doesn't need a
  Policy at all — a Spatie permission check directly in the controller/Request is sufficient, the
  same way `TeamPolicy::create()` and `viewAny()` return a plain `true`/blanket check because
  there's nothing per-record to evaluate for those two actions specifically.

## 3. Policy shape — mirror TeamPolicy

- Standard action names (`viewAny`, `view`, `create`, `update`, `delete`) for the actions that map
  onto them; a plain, recognizable verb for anything else (`TeamPolicy`'s own `leave`,
  `addMember`, `updateMember`, `removeMember`, `inviteMember`, `cancelInvitation`).
- Each method's body is a single boolean expression built from the model's own real
  ownership/role check — a `belongsTo`-style relation query, a role-enum lookup, a boolean column
  — never a hardcoded `true`/`false` unless the action genuinely has nothing to check (Team's own
  `viewAny`/`create` are blanket `true` because any authenticated user may view the team list or
  create a new team; that's a deliberate, stated choice on that model, not a default to copy
  blindly onto a model where creation should actually be restricted).
- Combine conditions the same way `TeamPolicy::leave()` and `::delete()` do — a state check on the
  record itself (`! $team->is_personal`) alongside the actual authorization check, when the
  action has a real business-rule precondition beyond "is this user allowed."
- Register the Policy. Laravel's convention-based auto-discovery finds a Policy automatically only
  when the class name and namespace match its expected convention for the model; if a Domain
  module's Policy doesn't naturally resolve that way (a Policy living under
  `Domains/<Domain>/app/Policies/` for a model under `Domains/<Domain>/app/Models/`, for
  instance — auto-discovery's default mapping assumes `App\Models`/`App\Policies`), register it
  explicitly with `Gate::policy(Model::class, Policy::class)` in a service provider rather than
  assuming it will be found. Confirm which case applies before relying on either path silently.

## 4. Spatie permission shape

- Create through `Domains\Identity\Models\Permission::create([...])` (or `firstOrCreate`), the
  same fillable shape the test fixture in `Domains/Identity/tests/Feature/PermissionTest.php`
  demonstrates: `app_id`, `name`, `label`, `code`, `description`, `guard_name`. Naming itself is
  covered in `references/naming-standard.md` — read that before writing a value for any of these.
- Attach to a `Role` (itself scoped to a `company_id`) rather than directly to a `User`, unless
  the plan has a specific reason a permission should be grantable to an individual user outside
  any role — nothing in the app currently does that, so treat it as a deviation worth confirming
  with the user rather than a default.
- Check with `$user->can('permission-name')` (routes through the overridden `can()` →
  `hasAnyPermission()`) in a controller, Request's `authorize()`, or route middleware — whichever
  matches where this domain's other authorization checks already live; don't introduce a new
  placement style for one permission when a domain already has an established spot for the
  others.

## 5. The `is_root` / `is_owner` bypass

`Domains\Identity\Models\User` carries `is_root` and `is_owner` boolean columns (cast to
`boolean`) with `isRoot()`/`isOwner()` accessor methods. These are **not** part of Spatie's own
grant resolution — `can()`'s override goes straight to `hasAnyPermission()` and does not consult
either flag, so a root/owner user is not automatically granted every permission just by having
`can()` overridden. Wherever "root/owner bypasses this check entirely" is the intended behavior,
that has to be written explicitly, typically as `$user->isRoot() || $user->isOwner() ||
<the real check>` in the Policy method or wherever the permission gets checked. Default to
including this bypass for a new Policy/permission unless the user has a specific reason a given
action shouldn't be bypassable even by root/owner (an audit-log write, an irreversible action
where "even the owner has to go through the same flow" might be a deliberate choice) — ask rather
than assuming either way when it's not obvious.

## 6. The `App\Models\User` gotcha

`app/Policies/TeamPolicy.php`, `app/Concerns/HasTeams.php`'s own imports, every controller under
`app/Http/Controllers/Teams/`, every action under `app/Actions/Teams/`,
`app/Rules/ValidTeamInvitation.php`, `database/factories/UserFactory.php`,
`database/factories/TeamInvitationFactory.php`, `database/seeders/DatabaseSeeder.php`, and every
test under `tests/Feature/{Auth,Teams,Settings,Dashboard}` `use App\Models\User;` — a class that
does not exist. `app/Models/User.php` was never created; the real model is
`Domains\Identity\Models\User`, and `config/auth.php` correctly resolves the `auth` guard to it.
Confirmed by running `php artisan test tests/Feature/Teams/TeamTest.php`: every test fails with
`Class "App\Models\User" not found`.

This is a **pre-existing bug in the app**, not something introduced by (or expected to be fixed
by) a normal permissions pass. If a plan touches any file in the list above:

- Say so explicitly in `SKILL.md` Step 2, don't silently work around it or silently ignore it.
- Ask whether fixing the `use` statements (to `Domains\Identity\Models\User`) is in scope for
  this pass, or should be tracked and fixed separately. Either answer is reasonable; the wrong
  move is proceeding as if the bug doesn't exist when the plan's own Verification step
  (`php artisan test tests/Feature/Teams`) is guaranteed to fail because of it, unrelated to
  whatever this pass actually changed.
- If fixing it is in scope, it's a plain, mechanical `use` statement fix across the files above —
  there's no deeper redesign implied, the trait/enum/Policy logic itself is already correct and
  already imports the right things; only the type hints on `User $user` parameters (and any
  `use App\Models\User;` import) are wrong.

## 7. No resync/migration tooling exists yet

There is no command in this app for reconciling a renamed, retired, or re-parented Spatie
permission against roles that already hold a grant on it — nothing like a
`ResyncPermissionsCommand`. Today, a new permission is simply a new row a seeder creates; nothing
in the app yet needs to handle renaming or retiring one that real accounts already depend on,
because no real accounts hold any Spatie permission grants yet (Section 1 of
`references/naming-standard.md`). If a future pass genuinely needs this (a live permission being
renamed after real roles already hold it), that's new tooling to design deliberately with the
user, not something to invent inline as part of an unrelated authorization pass.
