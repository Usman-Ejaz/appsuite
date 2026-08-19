# Output Formatting: Showing Content for Review

Referenced by any skill that shows a draft, plan, checklist, or report to the user in chat for
review, confirmation, or approval.

## Rule: render markdown live, never wrap the whole thing in a code fence

When showing a draft ticket, a PR body, an implementation plan, a review, or any other markdown
document for the user to read and approve, write it as real markdown directly in the chat
response.

- Use real `#` / `##` headings, real `-` bullets, real checkboxes, real bold text.
- Do not wrap the whole document in a single ``` code fence.

A code fence shows the literal `##`, `-`, and `**` characters as plain text instead of rendering
them. That is much harder to read, and it defeats the point of showing a draft for review in the
first place.

**One exception:** a small, genuinely literal snippet meant to be copied verbatim (an exact shell
command, an exact string to paste somewhere else) can still use a code fence. That content is
meant to be copied as-is, not read as prose.

A code fence inside a skill's own `SKILL.md` file, showing the shape of a template, is a
different thing. That fence is there to show the *skill* what structure to produce, it is not
itself what gets shown to the user. When the skill actually produces that content for a real
user to read, it renders it live per the rule above.
