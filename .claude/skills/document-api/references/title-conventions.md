# API Endpoint Title Conventions

Consult this during Step 3 (Document the endpoint) whenever you're naming or renaming an
operation.

Titles need to read the same way across the whole API, a developer scanning the operation list
shouldn't be able to tell which domain an endpoint came from just from how its title is shaped.
Before naming a new endpoint, check how sibling endpoints in the same domain already name the
same kind of operation and match that first, consistency with the immediate neighbours beats a
generic rule when the two disagree.

This app's API surface is still early (all six domains — `Auth`, `CMS`, `Core`, `Ecommerce`,
`Identity`, `Shared` — have a `routes/api.php`, but several are still thin stubs), so there isn't
yet a large body of live titles to verify a dominant pattern against the way a mature API would
have. Absent an established local convention for a given domain, use this table as the default
starting point (a standard, widely-recognized shape for a REST-ish CRUD API, not something
derived from this app's own history the way it would be in a codebase with years of precedent):

| Operation | Pattern | Example |
|---|---|---|
| List (collection) | `Get {Plural}` | `Get Integrations`, `Get Companies` |
| List, when the resource name has no distinct plural (a mass noun) | `List {Resource}` | `List Content` |
| Get one by id | `Get {Singular}` | `Get Integration`, `Get Company` |
| Create | `Create {Singular}` | `Create Integration` |
| Update (the whole resource) | `Update {Singular}` | `Update Company` |
| Update one field or a narrow sub-action | `Update {Singular} {Field}` | `Update Company Status` |
| Delete | `Delete {Singular}` | `Delete Integration` |
| Bulk action on multiple ids at once | `Bulk {Verb} {Plural}` | `Bulk Archive Integrations` |
| Export | `{Plural} Export` | `Companies Export` |
| Nested sub-resource collection | `Get {Parent Singular} {SubResource Plural}` | `Get Company Roles` |
| Nested sub-resource create | `Create {Parent Singular} {SubResource Singular}` | `Create Company Role` |
| One-off action that isn't CRUD | `{Verb} {Singular}` | `Connect Integration`, `Revoke Api Key` |

A few rules that apply across every row above:

- Title Case throughout, and no filler articles, `Revoke Api Key`, not `Revoke the Api Key`.
- Use the resource's real, public name (matching its field values and its Resource class's public
  identity), not an internal model or table name.
- `{Field}` in the "narrow sub-action" row is a real field or concept name, not a paraphrase,
  `Update Company Status` because the field is `status` (or equivalent), not `Update Company
  State`.
- Don't invent a new shape for something the table already covers just because there's no
  existing endpoint yet to imitate; use the table's default instead of guessing at a new
  convention from scratch.

If the same title-naming ambiguity keeps recurring across a domain once real endpoints
accumulate, propose formalizing it as an addition to the table above rather than re-deciding it
ad hoc on every pass (see the Auto Learning section in `SKILL.md`), and record the confirmed,
domain-specific convention in `.claude/knowledge-base/skills/document-api.md` so it's not
re-derived from scratch the next time.
