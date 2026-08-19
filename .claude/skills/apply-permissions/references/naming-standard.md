# Permission & Policy Naming — Current State

Unlike a codebase with years of real permission passes behind it, **this app does not yet have
an established permission-naming convention**, because it does not yet have any real, seeded
Spatie permissions. Read this file for what's actually verifiable today, and treat every
"convention" below as a proposal to confirm with the user (per `SKILL.md` Step 3), not a locked
standard to enforce silently.

## Contents

1. [What's actually seeded today: nothing](#1-whats-actually-seeded-today-nothing)
2. [The one real data point: a test fixture](#2-the-one-real-data-point-a-test-fixture)
3. [Proposed convention, pending real usage](#3-proposed-convention-pending-real-usage)
4. [Policy method naming — this one *is* established](#4-policy-method-naming--this-one-is-established)
5. [The Team feature's own naming — a second, independent style](#5-the-team-features-own-naming--a-second-independent-style)

---

## 1. What's actually seeded today: nothing

`Domains/Identity/database/seeders/IdentityDatabaseSeeder.php` seeds three `Role` rows per
company (`Editor`, `Viewer`, `Member`), with **no `Permission` rows created anywhere**, and no
permissions attached to any of those roles. There is no other seeder in the app that creates a
`Permission` row either. Before proposing a name for a new permission, re-verify this is still
true (grep the target domain and `Domains/Identity` for `Permission::create`/`firstOrCreate`/
`findOrCreate`) rather than trusting this file's snapshot indefinitely — the first real seeded
permission in this app will set precedent for everything named after it, so it's worth getting
right.

## 2. The one real data point: a test fixture

`Domains/Identity/tests/Feature/PermissionTest.php` creates a `Permission` with:

```php
Permission::create([
    'app_id' => $app->id,
    'name' => 'contacts.view',
    'label' => 'View Contacts',
    'code' => 'contacts_view',
    'guard_name' => 'web',
]);
```

This is a test fixture demonstrating that the model's fillable columns work, not a declared
naming standard — but it's the only shape that exists anywhere in the app, so it's a reasonable
starting point to propose rather than inventing something unrelated.

## 3. Proposed convention, pending real usage

Absent anything better, propose this shape when a plan needs to name a new Spatie permission,
and say plainly that it's a proposal, not an enforced rule:

- **`name`** (the column Spatie's own permission checks match against): lowercase, dot-separated,
  `resource.verb` — `contacts.view`, `integrations.manage`. This is the value `$user->can(...)`
  and `hasAnyPermission()` actually check.
- **`code`**: the same string with dots replaced by underscores — `contacts_view`,
  `integrations_manage`. Since `code` isn't a column Spatie itself reads (it's an app-added
  column on `Domains\Identity\Models\Permission`), confirm with the user what it's actually used
  for in this app (a stable identifier independent of a future `name` rename, a frontend-facing
  key) before assuming its purpose rather than guessing from the fixture alone.
- **`label`**: Title Case, human-readable — `View Contacts`, `Manage Integrations`.
- **`description`**: a real sentence saying what the permission actually grants, never left
  empty and never just the label restated as a sentence — apply the same bar
  `apply-permissions`'s own Step 3 audit uses for any permission naming, even though no
  established prior-art elsewhere in this app currently enforces it.

If a pass on a real domain establishes a genuinely different, better-fitting shape (a
`Domain:Resource:Action` style instead of `resource.verb`, for instance, if the user prefers
matching a different existing pattern they have in mind), record that decision in
`.claude/knowledge-base/skills/apply-permissions.md` per this skill's Auto Learning section, and
propose updating this file the next time it comes up, rather than letting two conventions
silently diverge across domains.

## 4. Policy method naming — this one *is* established

Unlike Spatie permission naming, Policy method naming has one real, live example to match:
`app/Policies/TeamPolicy.php`. Follow Laravel's own convention it already uses —
`viewAny`, `view`, `create`, `update`, `delete` for the standard CRUD-shaped checks, and a plain,
recognizable verb for anything else specific to the resource (`leave`, `addMember`,
`updateMember`, `removeMember`, `inviteMember`, `cancelInvitation`). A new Policy should use the
same standard method names for the actions that map onto them, and name any custom action the
same way TeamPolicy does: a verb (or short verb phrase) a caller would recognize, not an internal
mechanism name.

## 5. The Team feature's own naming — a second, independent style

`app/Enums/TeamPermission.php`'s cases are string-backed with a `resource:verb` shape
(`team:update`, `team:delete`, `member:add`, `member:update`, `member:remove`,
`invitation:create`, `invitation:cancel`) — colon-separated, not dot-separated, and this is a
plain PHP enum with no `Permission`/`Role` database rows behind it at all, entirely separate from
Spatie. Don't confuse this with the Spatie `name`/`code` convention above; it's a different
mechanism with its own, already-established shape, and it isn't a candidate for consolidating
with Spatie's naming just because both happen to use a punctuation-separated `resource:verb` /
`resource.verb` pattern. If a future domain needs a Team-style enum-based permission set of its
own (not backed by Spatie), match `TeamPermission`'s `resource:verb` shape for that; if it needs
a real Spatie permission, use section 3's proposed shape instead.
