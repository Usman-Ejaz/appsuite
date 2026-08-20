---
paths:
  - Domains/Identity/app/Models/Permission.php
---

# Models

## Permission name format is Module:Resource:Action
Permission `name` (used for authorization checks and API display) must be stored as `{app_code}:{resource}:{action}`, colon-separated, all lowercase — e.g. `crm:contacts:view`. `app_code` matches the owning App's `code` column (Permission already belongsTo App via `app_id`). The `code` column is a separate, unrelated machine key and keeps its own snake_case convention (e.g. `contacts_view`) — only `name` follows the new format. This is the format the profile API's `permissions` field returns verbatim (see Domains/Identity/app/Http/Resources/ProfileResource.php).
