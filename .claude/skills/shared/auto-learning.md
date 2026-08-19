# Auto Learning (shared capability)

Referenced by every skill under `.claude/skills/`. Each skill's own `## Auto Learning` section
is short: a pointer to this file, plus 2-4 bullets of what's specific to that skill (what
"wider" means for it, what's typically unfamiliar and worth verifying, its own learnings-file
name, what a proposed change would look like). This file holds the mechanism that's identical
across all of them, so it's written once and doesn't drift out of sync between skills as it gets
tuned.

A standing capability, active throughout every step of whichever skill references it. Not a
one-time phase done once and then moved past.

**Read/look wider than the immediate task when a pattern looks reusable.** Don't treat something
as private to the current diff, PR, ticket, endpoint, or domain if it's actually a project-wide
convention, a shared base class, a recurring incident shape, or a naming pattern used everywhere.
Recognizing "this is shared, not local to what I'm looking at right now" the first time saves
re-deriving it, and re-explaining it as if it were novel, on every future run of that skill.

**Look things up when something is genuinely unfamiliar, rather than guessing.** A third-party
API's actual rules, an industry or compliance term, a package's real documented behaviour, a fact
about this repo you're not confident about: verify it before it becomes a Fact, Constraint,
Convention, or Finding in the output. Something built on a plausible-sounding assumption is worse
than an admitted gap, because nothing signals to the reader that it needs re-checking, and a wrong
one poisons every future run that trusts the earlier output without re-verifying it.

**Keep a running note of durable, cross-cutting learnings in this skill's own learnings file.**
Each skill maintains `.claude/knowledge-base/skills/<skill-name>.md` (one file per skill, named for it,
living in this shared folder rather than published anywhere product-facing) as working memory
across runs. Read it at the start of a pass if it exists. Create it lazily, only once there's an
actual learning worth recording; don't scaffold an empty file up front just because this
capability exists. Keep entries to a line or two each; it's a running list, not a report. *What*
belongs in it is skill-specific, defined in that skill's own Auto Learning section, not here.

**Ask when you think there's a better way to do the specific task, rather than silently
deviating or silently staying quiet.** Bring a concrete before/after or a specific proposed
alternative, not just "should I change this?". Don't act on a structural change to how the skill
itself works without the user agreeing first, since it affects every future run of that skill,
not just the current one.
