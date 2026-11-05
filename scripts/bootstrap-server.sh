#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${PETORA_ENV_FILE:-${ROOT_DIR}/.env.production}"
COMPOSE_FILE="${ROOT_DIR}/compose.prod.yaml"
if [[ "$ENV_FILE" != /* ]]; then
    ENV_FILE="${ROOT_DIR}/${ENV_FILE}"
fi

command -v docker >/dev/null || { echo "Docker Engine is required." >&2; exit 1; }
command -v flock >/dev/null || { echo "flock is required for deployment locking." >&2; exit 1; }
docker compose version >/dev/null
docker info >/dev/null

if [[ ! -f "$ENV_FILE" ]]; then
    printf 'Create the production environment file first: %s\n' "$ENV_FILE" >&2
    exit 1
fi

chmod 0600 "$ENV_FILE"
STATE_DIR="${PETORA_DEPLOY_STATE_DIR:-${ROOT_DIR}/.deploy}"
mkdir -p "$STATE_DIR"
chmod 0700 "$STATE_DIR"

docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" config --quiet
printf 'Docker and production configuration validated. Review certificate SANs and VPS firewall before starting services.\n'
