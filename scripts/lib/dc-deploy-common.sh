#!/usr/bin/env bash
# Shared deploy helpers for DreamCalendars (source, do not execute).

[[ -n "${DC_DEPLOY_COMMON_LOADED:-}" ]] && return 0
DC_DEPLOY_COMMON_LOADED=1

_dc_deploy_lib_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DC_REPO_ROOT="$(cd "$_dc_deploy_lib_dir/../.." && pwd)"

export COPYFILE_DISABLE=1

declare -a DC_SSH_CMD=()
declare -a DC_SCP_CMD=()
DC_RSYNC_RSH=""

dc_deploy_load_env() {
  local env_file="$DC_REPO_ROOT/.cursor/deploy.local.env"
  [[ -f "$env_file" ]] || {
    echo "error: missing $env_file (copy from .cursor/deploy.local.env.example)" >&2
    return 1
  }

  set -a
  # shellcheck source=/dev/null
  source "$env_file"
  set +a

  : "${DEPLOY_HOST:?DEPLOY_HOST missing in deploy.local.env}"
  : "${DEPLOY_OWNER:?DEPLOY_OWNER missing in deploy.local.env}"
  : "${DEPLOY_ROOT_PRODUCTION:?DEPLOY_ROOT_PRODUCTION missing in deploy.local.env}"
}

dc_deploy_ensure_ssh_key() {
  [[ -n "${DEPLOY_SSH_KEY:-}" ]] || return 0

  local key_path="$DEPLOY_SSH_KEY"
  [[ "$key_path" != /* ]] && key_path="$DC_REPO_ROOT/$key_path"

  if [[ ! -f "$key_path" ]]; then
    echo "error: DEPLOY_SSH_KEY not found: $key_path" >&2
    return 1
  fi

  local mode
  mode=$(stat -f '%OLp' "$key_path" 2>/dev/null || stat -c '%a' "$key_path" 2>/dev/null || echo "")
  if [[ "$mode" != "600" && "$mode" != "400" ]]; then
    echo "→ fixing SSH key permissions (chmod 600): $key_path"
    chmod 600 "$key_path"
  fi

  DEPLOY_SSH_KEY_RESOLVED="$key_path"
}

dc_deploy_setup_transport() {
  DC_SSH_CMD=(ssh -o StrictHostKeyChecking=no -o ConnectTimeout=15)
  DC_SCP_CMD=(scp -o StrictHostKeyChecking=no -o ConnectTimeout=15)
  DC_RSYNC_RSH="ssh -o StrictHostKeyChecking=no -o ConnectTimeout=15"

  if [[ -n "${SSHPASS:-}" ]] && command -v sshpass >/dev/null 2>&1; then
    DC_SSH_CMD=(sshpass -e ssh -o StrictHostKeyChecking=no -o ConnectTimeout=15)
    DC_SCP_CMD=(sshpass -e scp -o StrictHostKeyChecking=no -o ConnectTimeout=15)
    DC_RSYNC_RSH="sshpass -e ssh -o StrictHostKeyChecking=no -o ConnectTimeout=15"
    export SSHPASS
  fi

  dc_deploy_ensure_ssh_key || return 1

  if [[ -n "${DEPLOY_SSH_KEY_RESOLVED:-}" ]]; then
    DC_SSH_CMD+=(-i "$DEPLOY_SSH_KEY_RESOLVED" -o IdentitiesOnly=yes -o IdentityAgent=none)
    DC_SCP_CMD+=(-i "$DEPLOY_SSH_KEY_RESOLVED" -o IdentitiesOnly=yes -o IdentityAgent=none)
    DC_RSYNC_RSH+=" -i $(printf '%q' "$DEPLOY_SSH_KEY_RESOLVED") -o IdentitiesOnly=yes -o IdentityAgent=none"
  fi
}

dc_deploy_ssh() {
  "${DC_SSH_CMD[@]}" "$DEPLOY_HOST" "$@"
}

dc_deploy_remote_mkdirs() {
  local -a dirs=("$@")
  [[ ${#dirs[@]} -gt 0 ]] || return 0

  local dir
  local -a quoted=()
  for dir in "${dirs[@]}"; do
    quoted+=("$(printf '%q' "$dir")")
  done

  dc_deploy_ssh "mkdir -p ${quoted[*]}"
}

dc_deploy_upload_file() {
  local rel="$1"
  local deploy_root="$2"
  local dest_dir
  dest_dir=$(dirname "$rel")

  local remote_dir="$deploy_root"
  if [[ "$dest_dir" != "." ]]; then
    remote_dir="$deploy_root/$dest_dir"
  fi

  dc_deploy_remote_mkdirs "$remote_dir"

  if command -v rsync >/dev/null 2>&1; then
    rsync -a --no-perms --omit-dir-times \
      -e "$DC_RSYNC_RSH" \
      "$rel" "$DEPLOY_HOST:$remote_dir/"
  else
    COPYFILE_DISABLE=1 "${DC_SCP_CMD[@]}" "$rel" "$DEPLOY_HOST:$remote_dir/"
  fi

  dc_deploy_ssh "chown ${DEPLOY_OWNER%%:*}:${DEPLOY_OWNER##*:} $(printf '%q' "$remote_dir/$(basename "$rel")")"
}

dc_deploy_reload_php_fpm() {
  echo "→ reload php8.3-fpm"
  dc_deploy_ssh "systemctl reload php8.3-fpm"
}

dc_deploy_check_ssh() {
  dc_deploy_load_env || return 1
  dc_deploy_setup_transport || return 1
  dc_deploy_ssh "echo ok" >/dev/null
}

dc_deploy_assert_confirm() {
  if [[ "${1:-}" != true ]]; then
    echo "error: production deploy blocked — use --confirm" >&2
    echo "       example: scripts/dc-deploy.sh --confirm footer.php" >&2
    return 1
  fi
}
