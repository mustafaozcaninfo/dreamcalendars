#!/usr/bin/env bash
# Verify deploy prerequisites (SSH key, connectivity, remote paths).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib/dc-deploy-common.sh
source "$SCRIPT_DIR/lib/dc-deploy-common.sh"

FAIL=0
pass() { echo "✓ $*"; }
fail() { echo "✗ $*"; FAIL=1; }

echo "=== DreamCalendars deploy doctor ==="
echo ""

if dc_deploy_load_env; then
  pass "deploy.local.env loaded"
else
  fail "deploy.local.env missing or invalid"
  exit 1
fi

echo "  DEPLOY_HOST=$DEPLOY_HOST"
echo "  DEPLOY_ROOT_PRODUCTION=$DEPLOY_ROOT_PRODUCTION"
echo "  DEPLOY_SSH_KEY=${DEPLOY_SSH_KEY:-<not set — password auth>}"

if dc_deploy_setup_transport; then
  pass "SSH transport configured"
else
  fail "SSH transport setup failed"
fi

if dc_deploy_check_ssh 2>/dev/null; then
  pass "SSH connection to $DEPLOY_HOST"
else
  fail "SSH connection failed — check DEPLOY_SSH_KEY, SSHPASS, or network"
fi

if dc_deploy_ssh "test -d $(printf '%q' "$DEPLOY_ROOT_PRODUCTION")" 2>/dev/null; then
  pass "remote production path exists"
else
  fail "remote production path missing: $DEPLOY_ROOT_PRODUCTION"
fi

if command -v rsync >/dev/null 2>&1; then
  pass "rsync available locally"
else
  fail "rsync not found — install rsync for reliable deploys"
fi

if command -v php >/dev/null 2>&1; then
  pass "php available for syntax checks"
else
  echo "  (php not in PATH — deploy will skip php -l)"
fi

echo ""
if [[ "$FAIL" -eq 0 ]]; then
  echo "✓ all checks passed"
  echo "  deploy: scripts/dc-deploy.sh --confirm <files>"
  echo "  pull:   scripts/dc-pull.sh"
else
  echo "✗ fix the issues above before deploying"
  exit 1
fi
