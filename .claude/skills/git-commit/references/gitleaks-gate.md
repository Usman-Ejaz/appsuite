# Gitleaks gate

Read when a file is about to be committed (Step 2 scan, Step 4 staged check) or when a scan finds something.

The gate uses the gitleaks **default rules** (API keys, tokens, private keys, passwords in connection strings, cloud credentials, JWTs and so on). A committed file that breaks one of these rules is a violation, even if the value "looks fake".

This repo has no `.gitleaks.toml` yet, so the commands below run on the default rules. If a `.gitleaks.toml` is added at the root later, append `--config .gitleaks.toml` to every command.

## How to scan

1. Check the tool: `command -v gitleaks`.
2. Installed: scan each file you plan to commit, untracked files too.
   ```bash
   gitleaks dir <path> --redact --no-banner
   ```
   Older gitleaks (before v8.19, no `dir` command):
   ```bash
   gitleaks detect --no-git --source <path> --redact --no-banner
   ```
3. After staging a group, check what is staged:
   ```bash
   gitleaks git --staged --redact --no-banner
   ```
   Older versions: `gitleaks protect --staged --redact --no-banner`. If it finds anything, unstage the group (`git restore --staged <paths>`) and follow "What to do on a violation".
4. Not installed: do not skip the gate.
   - Review every changed line yourself against the default rule types above. `.claude/skills/git-commit/scripts/commit-survey.sh` lists quick red flags to start from.
   - Tell the developer the scan was manual because gitleaks is not installed, and suggest installing it (`brew install gitleaks` or the release binary from GitHub) for real enforcement.
5. Keep `--redact` on. Never print a found secret in full in the chat.

## Which findings count

- **New violation:** the secret is in a line this change adds or edits. It blocks the commit.
- **Old violation:** the secret was already in the file before this change (check with `git blame` / `git diff`). Report it, do not fix it on your own. It still blocks the commit of that file, because committing the file again spreads it to every new clone and branch. Ask the developer.

## What to do on a violation

1. **Stop.** Do not stage or commit the affected file. Clean groups with no violation can still be committed.
2. Show a short list per finding: file, line, rule id, redacted match.
3. Ask the developer, one question per finding (or one per file when a file has several findings of the same type). Never change code before they answer.
   - List every fix that really solves the finding.
   - Put the best fix first, with `(Recommended)` in its label.
   - Always add a last option: **"I will fix myself"**.
4. Pick options from this table, keeping only the ones that fit:

   | Option | When it fits |
   |---|---|
   | Move the value to `.env` and read it with `config()` / `env()` in a config file | A real secret in app code or config. Usually the recommended fix. |
   | Replace it with a fake placeholder (`xxx`, `fake-token`, `Str::random()`) | Tests, factories, seeders, docs, examples. |
   | Remove the file from this commit and add it to `.gitignore` | Local-only files: `.env` copies, dumps, keys, scratch output. |
   | Mark as a false positive (`# gitleaks:allow` on the line, or a path allowlist in a root `.gitleaks.toml`) | Only when the match is clearly not a secret (a hash, a public id). Never recommend it for a real secret. |
   | I will fix myself | Always the last option. |

5. **Easy fix** (one or two lines, no new config key design) and the developer picks it: apply it, re-run the scan on that file, continue with the commit.
6. **Not easy** (many files, a new config design, or the value is used in many places): recommend "I will fix myself" and say why in one line.
7. "I will fix myself": leave that file unstaged and uncommitted, commit the clean groups, and list the blocked files in the final report.
8. If the secret looks real, remind the developer to **rotate it**. Removing it from the file does not help if it was already shared, pushed or committed on another branch.
