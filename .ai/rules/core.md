---
paths:
  - 'Domains/Core/**'
---

# Core

## Core is for platform bases and third-party plumbing only
Domains/Core holds shared bases (BaseModel, BaseRepository, scopes, traits) and third-party integration plumbing that resolves tenant or platform credentials (SMTP, Twilio, FCM…). Feature code, such as notification triggers, contracts, registries and events, lives in its own domain (e.g. Domains/Notification), never in Core.
