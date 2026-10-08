#!/usr/bin/env bash
# Deploy all git-changed files under public_html/ to production.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_ROOT"

if ! git rev-parse --git-dir >/dev/null 2>&1; then
  echo "error: not a git repo — init git first or pass files to dc-deploy.sh" >&2
  exit 1
fi

TMP=$(mktemp)
trap 'rm -f "$TMP"' EXIT

{
  git diff --name-only HEAD 2>/dev/null | grep '^public_html/' | sed 's|^public_html/||' || true
  git ls-files --others --exclude-standard public_html/ | sed 's|^public_html/||' || true
} | sort -u > "$TMP"

FILES=()
while IFS= read -r f; do
  [[ -n "$f" && -f "public_html/$f" ]] && FILES+=("$f")
done < "$TMP"

if [[ ${#FILES[@]} -eq 0 ]]; then
  echo "no changed files under public_html/"
  exit 0
fi

echo "→ production deploy: ${#FILES[@]} file(s)"
exec "$REPO_ROOT/scripts/dc-deploy.sh" --confirm "${FILES[@]}"
