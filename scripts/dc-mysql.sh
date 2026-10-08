#!/usr/bin/env bash
# Run MySQL query via SSH tunnel or direct on server.
# Usage:
#   ./scripts/dc-mysql.sh "SELECT COUNT(*) FROM some_table"
#   ./scripts/dc-mysql.sh --remote "SHOW TABLES"
#   ./scripts/dc-mysql.sh --app --remote "SHOW TABLES"
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib/dc-deploy-common.sh
source "$SCRIPT_DIR/lib/dc-deploy-common.sh"

REMOTE=false
USE_APP_DB=false
FILE=""
QUERY=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --remote) REMOTE=true; shift ;;
    --app) USE_APP_DB=true; shift ;;
    --file|-f) FILE="${2:?}"; shift 2 ;;
    -h|--help)
      echo "Usage: dc-mysql.sh [--remote] [--app] [--file path.sql] \"SQL\""
      exit 0
      ;;
    *) QUERY="$1"; shift ;;
  esac
done

dc_deploy_load_env

if $USE_APP_DB; then
  DB_NAME="${DB_APP_NAME:-dreamcalendars_app}"
  DB_USER="${DB_APP_USER:-dreamcalendars_app}"
  DB_PASSWORD="${DB_APP_PASSWORD:?set DB_APP_PASSWORD in deploy.local.env}"
else
  DB_NAME="${DB_NAME:-dreamcalendars_dream}"
  DB_USER="${DB_USER:-dreamcalendars_dream}"
  : "${DB_PASSWORD:?}"
fi

if $REMOTE; then
  dc_deploy_setup_transport

  if [[ -n "$FILE" ]]; then
    dc_deploy_ssh "mysql -u '$DB_USER' -p'$DB_PASSWORD' '$DB_NAME'" < "$DC_REPO_ROOT/$FILE"
  elif [[ -n "$QUERY" ]]; then
    dc_deploy_ssh "mysql -u '$DB_USER' -p'$DB_PASSWORD' '$DB_NAME' -e \"$QUERY\""
  else
    echo "error: pass SQL or --file" >&2
    exit 1
  fi
  exit 0
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3307}"

if [[ -n "$FILE" ]]; then
  mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_NAME" < "$DC_REPO_ROOT/$FILE"
elif [[ -n "$QUERY" ]]; then
  mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_NAME" -e "$QUERY"
else
  echo "error: pass SQL or --file" >&2
  exit 1
fi
