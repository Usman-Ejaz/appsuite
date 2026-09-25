---
task: storage-media-redesign
title: Redesign the Media system (Domains/Storage)
started: 2026-09-24
last_updated: 2026-09-24
current_phase: 3
status: in_progress
---

## Master checklist

- [x] 1. Discovery — approved 2026-09-24. Plan: rebuild Domains/Storage's Media/Folder system
      (currently broken/uncommitted) as a lean, Spatie-inspired setup — polymorphic `resource`
      morph resolved via a Laravel `Relation::morphMap()` (replacing the closed `MediaResourceType`
      enum), a reusable `HasMedia` trait any model can add, enum-only `category`, company scoping
      via the app's existing `HasCompany` trait, and upload via request/multi-file array/array of
      URLs. Wires up `Product`, `Company`, `App`, `User`. Full plan:
      /home/usmanejaz/.claude/plans/i-want-to-implement-bright-pebble.md
- [ ] 2. Ticket — skipped: developer chose to go straight to implementation, no ticket needed
- [ ] 3. Implementation — code written, awaiting developer code-review approval (branch: develop, per developer's explicit choice to stay on current branch)
- [ ] 4. Product docs — not started
- [ ] 5. API docs — not started
- [ ] 6. Self-review — not started
- [ ] 7. Commit — not started
- [ ] 8. Open PR — not started

## Context accumulated so far

- **Problem:** The existing Storage/Media scaffolding (uncommitted, untracked) is non-functional —
  schema mismatch (`custom_name`/dead `file_name`) breaks every create, missing
  `MediaCollection`/`FolderCollection` classes break every list, no `Relation::morphMap()` causes
  `resource_type` to round-trip inconsistently (short alias in, FQCN out), only `Product` is wired
  up via a closed registry enum, and there's no batch/array upload.
- **Approved approach:** See plan file above for full design (HasMedia trait, morph map,
  UploadMediaBatch action, schema fix, MediaCategory gains a LOGO case, Product/Company/App/User
  wiring, deletion of MediaResourceType + HasRestrictedMediaCategories).
- **Linear ticket:** none yet
- **Branch:** currently on `develop`, not yet branched for this work
- **PR:** none yet

## Log

- 2026-09-24 — Phase 1 (Discovery) approved via plan mode.
- 2026-09-24 — Phase 2 (Ticket) skipped by developer choice. Proceeding to Phase 3 (Implementation).
