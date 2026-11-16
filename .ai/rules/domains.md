---
paths:
  - 'Domains/**'
---

# Domains

## One class per type, picked by a resolver
When behaviour varies by a type value (channel, recipient type, message type, provider…), don't branch inside one class. Give each type its own small class under `Services/<Area>/` and follow engage-core's Inbox shape:
- `Contracts/` holds the interface.
- An `Abstract*` base holds the shared behaviour.
- One concrete class per type, plus an `Unsupported*` fallback where needed.
- A `*Resolver::make($type)` holds the only `match`.
Callers depend on the contract, so adding a type means one new class plus one resolver arm.
