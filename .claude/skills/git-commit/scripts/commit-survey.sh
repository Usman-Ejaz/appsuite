#!/usr/bin/env bash
# Usage: commit-survey.sh [--pint]
#
# Read-only survey before grouping commits. Prints markdown:
#   - branch, ticket ID from the branch name, merge-in-progress state and unresolved files
#   - every changed, staged and untracked file (untracked folders expanded), with a group hint
#   - files that are staged AND edited again (only the staged version would be committed)
#   - quick red flags in file names and added lines (debug calls, keys, local-only files)
#   - whether gitleaks is installed (the real secret gate, see references/gitleaks-gate.md)
#   - the last 8 commit messages, to match their tone
# --pint  also list the changed PHP files Pint would reformat (`pint --test`, changes nothing).
#         This repo has no pre-commit hook, so git-commit runs Pint on each group's files itself.

set -euo pipefail
cd "$(git rev-parse --show-toplevel)"
pint=0; [[ "${1:-}" == "--pint" ]] && pint=1

branch=$(git rev-parse --abbrev-ref HEAD)
# A Linear-style ID (`eng-12`, `APP-345`) as its own segment of the branch name. No team key is
# hardcoded: this workspace has none. `fix-2fa` is not a ticket (digits must end the segment).
ticket=$(echo "$branch" | { grep -oiE '(^|[/_-])[a-z]{2,5}-[0-9]+($|[/_-])' || true; } | head -1 | tr -d '/_' | sed -E 's/^-//; s/-$//' | tr '[:lower:]' '[:upper:]')

echo "## Commit survey"
echo
echo "- Branch: \`$branch\`"
echo "- Ticket from branch name: ${ticket:-none (ask, or leave the suffix off)}"
if git rev-parse -q --verify MERGE_HEAD >/dev/null; then
    msg=$(head -1 "$(git rev-parse --git-path MERGE_MSG)" 2>/dev/null || echo "?")
    echo "- Merge in progress: yes (\`$msg\`). The first commit concludes the merge."
    unresolved=$(git diff --name-only --diff-filter=U)
    [[ -n "$unresolved" ]] && echo "- Stop: unresolved files: $(echo "$unresolved" | tr '\n' ' ')"
else
    echo "- Merge in progress: no"
fi
echo

hint() {
    case "$1" in
        .claude/knowledge-base/*|.claude/specs/*) echo "run record / knowledge base" ;;
        .claude/skills/*) echo "skills" ;;
        */tests/*|tests/*) echo "test (goes with its code)" ;;
        *.md) echo "docs?" ;;
        *) echo "code" ;;
    esac
}

echo "### Files"
echo
echo "| Status | File | Group hint |"
echo "|---|---|---|"
both=()
changed=()
while IFS= read -r line; do
    xy="${line:0:2}"; path="${line:3}"
    [[ "$path" == *" -> "* ]] && path="${path##* -> }"
    changed+=("$path")
    [[ "$xy" != "??" && "${xy:0:1}" != " " && "${xy:1:1}" != " " ]] && both+=("$path")
    echo "| \`$xy\` | \`$path\` | $(hint "$path") |"
done < <(git status --porcelain=v1 -uall)
[[ ${#changed[@]} -eq 0 ]] && echo "| | nothing to commit | |"
echo

if [[ ${#both[@]} -gt 0 ]]; then
    echo "### Staged and edited again"
    echo "Only the staged version is committed (no pre-commit hook re-adds files). Re-stage these, or the newer edits stay behind:"
    printf -- '- `%s`\n' ${both[@]+"${both[@]}"}
    echo
fi

echo "### Red flags (hints only; read every file anyway)"
flags=0
for f in ${changed[@]+"${changed[@]}"}; do
    [[ -f "$f" ]] || continue
    case "$f" in
        .env|.env.*|*.pem|*.key|*.p12|*.sql|*.dump|*.log|*.zip|*.tar.gz|nohup.out)
            [[ "$f" == ".env.example" ]] || { echo "- \`$f\`: local-only or dump file name"; flags=1; } ;;
    esac
    size=$(wc -c <"$f")
    [[ $size -gt 1048576 ]] && { echo "- \`$f\`: larger than 1 MB"; flags=1; }
    if git ls-files --error-unmatch -- "$f" >/dev/null 2>&1; then
        added=$(git diff HEAD -U0 -- "$f" | grep -E '^\+[^+]' || true)
    else
        added=$(grep -I '' "$f" 2>/dev/null || true)
    fi
    hits=$(echo "$added" | grep -nE '(^|[^A-Za-z0-9_])(dd|dump|ray|var_dump|print_r)\(|-----BEGIN [A-Z ]*PRIVATE KEY|AKIA[0-9A-Z]{16}|sk_live_[0-9a-zA-Z]+|APP_KEY=base64:|(password|secret|token)[A-Za-z_]*["'\'' ]*[:=]>? *["'\''][^"'\'' ]{8,}' || true)
    [[ -n "$hits" ]] && { echo "- \`$f\`:"; echo "$hits" | head -5 | cut -c1-120 | sed 's/^/  - /'; flags=1; }
done
[[ $flags -eq 0 ]] && echo "- none"
echo

# With core.fileMode=false (WSL), git records new files as 100644 even after chmod +x,
# so a script with a shebang fails with "Permission denied" on every fresh checkout.
noexec=()
for f in ${changed[@]+"${changed[@]}"}; do
    [[ -f "$f" && "$(head -c2 "$f" 2>/dev/null)" == '#!' ]] || continue
    case "$f" in */lib.sh|*/lib.php) continue ;; esac
    mode=$(git ls-files -s -- "$f" | cut -c1-6)
    [[ "$mode" == "100755" ]] || noexec+=("$f")
done
if [[ ${#noexec[@]} -gt 0 ]]; then
    echo "### Scripts not recorded as executable"
    echo "Each has a shebang but git would store it as 100644 (\`core.fileMode=$(git config core.fileMode || echo unset)\`). Before committing, run:"
    echo
    echo "\`git update-index --add --chmod=+x $(printf '%s ' ${noexec[@]+"${noexec[@]}"})\`"
    echo
fi

# Changed shell scripts under .claude/skills must stay portable (macOS bash 3.2, BSD tools, Git Bash).
sh_changed=()
for f in ${changed[@]+"${changed[@]}"}; do
    case "$f" in .claude/skills/*.sh) [[ -f "$f" ]] && sh_changed+=("$f") ;; esac
done
if [[ ${#sh_changed[@]} -gt 0 && -x .claude/skills/shared/scripts/portability-check.sh ]]; then
    if ! lint=$(.claude/skills/shared/scripts/portability-check.sh ${sh_changed[@]+"${sh_changed[@]}"} 2>&1); then
        echo "### Portability of changed scripts"
        echo "$lint" | sed 's/^/ /'
        echo
    fi
fi

echo "### Tests for the changed code"
if [[ -f scripts/test-changed.php ]] && command -v php >/dev/null 2>&1; then
    tests_report=$(php scripts/test-changed.php HEAD 2>/dev/null || true)
    if printf '%s' "$tests_report" | grep -Eq '⚠️|Test standards'; then
        printf '%s\n' "$tests_report" | sed -n '1p;/^| /p;/^- ⚠️/p;/^### Test standards/,$p'
    else
        printf -- '- %s\n' "$(printf '%s' "$tests_report" | sed -n '1s/^## //p')"
    fi
else
    echo "- skipped: scripts/test-changed.php or php is missing"
fi
echo

echo "### Secret scan tool"
if command -v gitleaks >/dev/null; then echo "- gitleaks $(gitleaks version 2>/dev/null): installed"
else echo "- gitleaks: not installed. The gate is a manual review (see references/gitleaks-gate.md)."; fi
echo

if [[ $pint -eq 1 ]]; then
    echo "### Pint preview"
    php_files=()
    for f in ${changed[@]+"${changed[@]}"}; do [[ "$f" == *.php && -f "$f" ]] && php_files+=("$f"); done
    if [[ ${#php_files[@]} -eq 0 ]]; then echo "- no PHP files"
    elif ./vendor/bin/pint --test ${php_files[@]+"${php_files[@]}"} >/dev/null 2>&1; then echo "- Pint would change nothing"
    else
        echo "- Pint would reformat these (git-commit runs Pint on them before staging):"
        ./vendor/bin/pint --test ${php_files[@]+"${php_files[@]}"} 2>/dev/null | grep -E '^[[:space:]]+⨯|^[[:space:]]+✗|FAIL' | sed 's/^ */  - /' || true
    fi
    echo
fi

echo "### Last 8 commits (tone reference)"
git log -8 --no-merges --format='- %s%n%w(0,2,2)%b' | sed -E '/^ *Co-Authored-By/d; /^ *$/d' | cut -c1-110
