#!/usr/bin/env bash
# Deploy files to dreamcalendars.com production (requires --confirm).
#
#   scripts/dc-deploy.sh --confirm footer.php index.php
#   scripts/dc-deploy.sh --confirm css/styles.css
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib/dc-deploy-common.sh
source "$SCRIPT_DIR/lib/dc-deploy-common.sh"

CONFIRM=false
FILES=()

usage() {
  cat <<'EOF'
Usage: dc-deploy.sh --confirm <file> [file...]

Paths relative to public_html/ (e.g. footer.php, css/styles.css).

DreamCalendars has no staging — all deploys go to production.
EOF
  exit "${1:-0}"
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --confirm|--confirm-production)
      CONFIRM=true
      shift
      ;;
    -h|--help) usage 0 ;;
    *) FILES+=("$1"); shift ;;
  esac
done

[[ ${#FILES[@]} -gt 0 ]] || usage 1
dc_deploy_assert_confirm "$CONFIRM"

dc_deploy_load_env
dc_deploy_setup_transport

DEPLOY_ROOT="$DEPLOY_ROOT_PRODUCTION"
PHP_DIR="$DC_REPO_ROOT/public_html"
cd "$PHP_DIR"

PHP_CHANGED=false
declare -a REMOTE_DIRS=()

echo "⚠️  PRODUCTION deploy → https://www.dreamcalendars.com"
echo "→ target: $DEPLOY_ROOT"

for rel in "${FILES[@]}"; do
  rel="${rel#public_html/}"
  if [[ ! -f "$rel" ]]; then
    echo "error: not found: public_html/$rel" >&2
    exit 1
  fi

  if [[ "$rel" == *.php ]]; then
    if command -v php >/dev/null 2>&1; then
      php -l "$rel"
    else
      echo "  (skip php -l — php not in PATH)"
    fi
    PHP_CHANGED=true
  fi

  dest_dir=$(dirname "$rel")
  if [[ "$dest_dir" == "." ]]; then
    REMOTE_DIRS+=("$DEPLOY_ROOT")
  else
    REMOTE_DIRS+=("$DEPLOY_ROOT/$dest_dir")
  fi
done

if [[ ${#REMOTE_DIRS[@]} -gt 0 ]]; then
  UNIQUE_DIRS=()
  while IFS= read -r _dir; do
    [[ -n "$_dir" ]] && UNIQUE_DIRS+=("$_dir")
  done < <(printf '%s\n' "${REMOTE_DIRS[@]}" | sort -u)
  dc_deploy_remote_mkdirs "${UNIQUE_DIRS[@]}"
fi

for rel in "${FILES[@]}"; do
  rel="${rel#public_html/}"
  echo "  ↑ $rel"
  dc_deploy_upload_file "$rel" "$DEPLOY_ROOT"
done

if $PHP_CHANGED; then
  dc_deploy_reload_php_fpm
fi

echo "✓ deployed to production — https://www.dreamcalendars.com"
echo "  cache: scripts/dc-cf-purge.sh --urls / …"
