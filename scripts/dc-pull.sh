#!/usr/bin/env bash
# Pull production public_html from server to local.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib/dc-deploy-common.sh
source "$SCRIPT_DIR/lib/dc-deploy-common.sh"

dc_deploy_load_env
dc_deploy_setup_transport

LOCAL="$DC_REPO_ROOT/public_html"

echo "→ pull $DEPLOY_HOST:$DEPLOY_ROOT_PRODUCTION/ → $LOCAL"

COPYFILE_DISABLE=1 rsync -avz \
  --exclude 'php_errors.log' --exclude '*.log' \
  --exclude 'make-a-website.tar.gz' \
  -e "$DC_RSYNC_RSH" \
  "$DEPLOY_HOST:$DEPLOY_ROOT_PRODUCTION/" "$LOCAL/"

echo "✓ pulled from production"
