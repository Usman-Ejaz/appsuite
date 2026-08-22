---
paths:
  - 'Domains/*/app/Enums/**'
---

# Enums

## Backed enum string values must be TitleCase
A string-backed enum's case VALUE must be TitleCase (e.g. `case READ = 'Read';` / `case NEW = 'New';`), never all-lowercase (`'read'`, `'new'`) — this is a separate rule from case KEY casing, covered below. This applies to the value on the right of `=`, not just the case name. When changing a value, also update any DB column default that hardcodes the old casing and any test assertions/fixtures using the literal string.

## Enum case keys are UPPER_CASE; permission enums add code/label/description
A string-backed enum's case KEY must be UPPER_CASE (e.g. `case DRAFT = 'Draft';`, `case BANK_TRANSFER = 'Bank Transfer';`), not TitleCase/PascalCase. The case VALUE stays TitleCase, per the existing value rule.

Exception: permission enums (e.g. `{Domain}Permission`) keep UPPER_CASE keys but their VALUES are lowercase, colon-separated strings — `{app_code}:{resource}:{action}` (e.g. `case BLOG_VIEW = 'cms:blogs:view';`). Permission enums must also define `label()`, `code()`, and `description()` methods with a `match ($this)` arm per case.

Permission enum case KEYS are ordered `MODULE_ACTION`, module first and singular (e.g. `PRODUCT_CREATE`, `ORDER_CANCEL`, `PAYMENT_METHOD_VIEW`) — not `ACTION_MODULE`/`ACTION_MODULES`. This is independent of the VALUE string, which keeps its own existing `{app_code}:{resource}:{action}` shape (resource plural, action last) and is not reordered to match.

Some pre-existing enums (BlogStatus, CmsPermission, TeamRole, CompanyStatus) predate this and may not fully match — don't copy their key casing as a template.
