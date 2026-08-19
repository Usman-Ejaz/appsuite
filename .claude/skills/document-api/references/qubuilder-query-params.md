# Query-Parameter Filtering: Not Used in This App

The `document-api` skill this reference set was originally written against had a shared
Qubuilder-based query-parameter convention (`select`/`filter`/`include`/`sort`/`group`/`page`/
`limit`, all inherited for free from two shared base `FormRequest` classes). **That package and
that convention do not exist in this app.** Verified two ways:

```bash
grep -ri "qubuilder" composer.json          # no matches
grep -rl "GetCollectionRequest\|GetResourceRequest" --include="*.php" .   # no matches, excluding vendor/
```

Neither the `kalimulhaq/qubuilder` package nor any shared `GetCollectionRequest`/
`GetResourceRequest`-style base class is present anywhere in this app's `Domains/*/app/Http` or
`app/Http`.

## What this app actually has instead

`Domains/Core/app/Repositories/BaseRepository.php` provides a much simpler shared shape: a
`list(array $filter = [], array $columns = ['*'])` method with basic pagination (`limit`, `page`,
capped by a `maxLimit`), and a protected `$filter` array set via a `filter()` method. This is not
a query-language-style filtering DSL, it's a plain associative filter array each repository's own
`query()` method interprets however it needs to. There is no shared, self-documenting parameter
set the way Qubuilder's base request classes provided; each endpoint's own filter/sort/pagination
parameters, if any, are just ordinary fields in that endpoint's own FormRequest `rules()` and get
documented individually, field by field, like any other request parameter (`document-api`'s own
Step 4 covers this, no special-casing needed).

## What to do instead of porting this reference

Don't document a query-parameter convention that isn't there. If a future domain in this app
does introduce a real shared filtering convention (its own base Request class, its own reusable
query-param set across several list endpoints), that's the point to write a real replacement for
this file, describing that actual mechanism the same way this file once described Qubuilder,
rather than resurrecting Qubuilder-specific content for a package this app has never used.
