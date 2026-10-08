#!/usr/bin/env bash
# Deploy indexing files + run DB migration + verify IndexNow key file.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

echo "=== DreamCalendars indexing setup ==="

# Deploy indexing payload
"$SCRIPT_DIR/dc-deploy.sh" --confirm \
  cron/index-ping.php \
  includes/indexing/dc_index_env.php \
  includes/indexing/IndexClient.php \
  includes/indexing/inventory.php \
  includes/indexing/queue.php \
  includes/indexing/runner.php

KEY=$(cat "$ROOT/config/indexnow.key")
KEY_FILE="${KEY}.txt"
if [[ -f "$ROOT/public_html/$KEY_FILE" ]]; then
  "$SCRIPT_DIR/dc-deploy.sh" --confirm "$KEY_FILE"
fi

# Deploy config to server (outside public_html)
# shellcheck source=lib/dc-deploy-common.sh
source "$SCRIPT_DIR/lib/dc-deploy-common.sh"
dc_deploy_load_env
dc_deploy_setup_transport
dc_deploy_remote_mkdirs "/home/dreamcalendars/web/dreamcalendars.com/config"
COPYFILE_DISABLE=1 rsync -a -e "$DC_RSYNC_RSH" \
  "$ROOT/config/indexing.php" \
  "$ROOT/config/indexnow.key" \
  "$DEPLOY_HOST:/home/dreamcalendars/web/dreamcalendars.com/config/"
dc_deploy_ssh "chown dreamcalendars:www-data /home/dreamcalendars/web/dreamcalendars.com/config/indexing.php /home/dreamcalendars/web/dreamcalendars.com/config/indexnow.key"

echo "→ DB migration"
"$SCRIPT_DIR/dc-mysql.sh" --remote --file scripts/sql/001_index_queue.sql

echo "→ verify key file HTTP"
KEY_URL="https://www.dreamcalendars.com/${KEY_FILE}"
BODY=$(curl -sS "$KEY_URL" || true)
if [[ "$BODY" == "$KEY" ]]; then
  echo "✓ IndexNow key file OK: $KEY_URL"
else
  echo "✗ key file check failed (deploy $KEY_FILE or purge CF cache)"
fi

echo "→ run first cron"
"$SCRIPT_DIR/dc-index-run.sh"

echo "✓ indexing setup complete"
echo "  IndexNow key: $KEY"
echo "  Add Bing: config/indexing.local.php → bing_api_key"
echo "  Add Yandex: yandex_oauth_token, yandex_user_id, yandex_host_id"
