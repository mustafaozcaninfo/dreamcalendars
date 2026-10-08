#!/usr/bin/env bash
# Run indexing cron on production via SSH.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib/dc-deploy-common.sh
source "$SCRIPT_DIR/lib/dc-deploy-common.sh"

dc_deploy_load_env
dc_deploy_setup_transport

REMOTE_PHP="${DEPLOY_ROOT_PRODUCTION}/cron/index-ping.php"
echo "→ run indexing cron on $DEPLOY_HOST"
dc_deploy_ssh "php $(printf '%q' "$REMOTE_PHP")"
