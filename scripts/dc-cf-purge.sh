#!/usr/bin/env bash
# Cloudflare cache purge for dreamcalendars.com
# Usage:
#   ./scripts/dc-cf-purge.sh              # purge everything
#   ./scripts/dc-cf-purge.sh --urls / /css/
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$REPO_ROOT/.cursor/deploy.local.env"

[[ -f "$ENV_FILE" ]] || { echo "error: missing $ENV_FILE" >&2; exit 1; }

set -a
# shellcheck source=/dev/null
source "$ENV_FILE"
set +a

: "${CF_EMAIL:?}"
: "${CF_API_KEY:?}"
: "${CF_ZONE_ID:?}"

BASE="${CF_SITE_URL:-https://www.dreamcalendars.com}"
PURGE_ALL=true
URLS=()

for arg in "$@"; do
  case "$arg" in
    --urls) PURGE_ALL=false; shift; break ;;
    -h|--help)
      echo "Usage: dc-cf-purge.sh [--urls path ...]"
      exit 0
      ;;
  esac
done

while [[ $# -gt 0 ]]; do URLS+=("$1"); shift; done

if $PURGE_ALL; then
  BODY='{"purge_everything":true}'
  echo "→ purge everything (zone $CF_ZONE_ID)"
else
  FILES=$(printf '"%s",' "${URLS[@]/#/$BASE}" | sed 's/,$//')
  BODY="{\"files\":[$FILES]}"
  echo "→ purge URLs: ${URLS[*]}"
fi

RESP=$(curl -sS -X POST \
  -H "X-Auth-Email: $CF_EMAIL" \
  -H "X-Auth-Key: $CF_API_KEY" \
  -H "Content-Type: application/json" \
  "https://api.cloudflare.com/client/v4/zones/$CF_ZONE_ID/purge_cache" \
  -d "$BODY")

echo "$RESP" | python3 -c "import sys,json; d=json.load(sys.stdin); print('✓' if d.get('success') else '✗', d.get('errors', d))"
