#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"
source "${ROOT_DIR}/scripts/compose.sh"

ENV_FILE="${PETORA_ENV_FILE:-${ROOT_DIR}/.env.production}"
STATE_DIR="${PETORA_DEPLOY_STATE_DIR:-${ROOT_DIR}/.deploy}"
if [[ "${PETORA_DEPLOY_LOCK_HELD:-0}" != 1 ]]; then
    mkdir -p "$STATE_DIR"
    chmod 0700 "$STATE_DIR"
    exec 9>"${STATE_DIR}/deploy.lock"
    if ! flock -n 9; then
        echo "Another deployment or rollback is already running." >&2
        exit 1
    fi
fi

ROLLBACK_TAG="${1:-}"
if [[ -z "$ROLLBACK_TAG" && -f "${STATE_DIR}/previous_tag" ]]; then
    IFS= read -r ROLLBACK_TAG < "${STATE_DIR}/previous_tag"
fi

if [[ ! "$ROLLBACK_TAG" =~ ^[A-Za-z0-9][A-Za-z0-9._-]{0,127}$ ]]; then
    echo "Usage: scripts/rollback.sh IMAGE_TAG (or provide .deploy/previous_tag)" >&2
    exit 2
fi
if [[ ! -f "$ENV_FILE" ]]; then
    printf 'Production environment file not found: %s\n' "$ENV_FILE" >&2
    exit 1
fi

export IMAGE_TAG="$ROLLBACK_TAG"
petora_compose config --quiet
petora_compose pull app web
petora_compose pull backup
petora_compose run --rm --no-deps app php artisan config:clear
petora_compose run --rm --no-deps app php artisan route:clear
petora_compose run --rm --no-deps app php artisan view:clear
petora_compose run --rm --no-deps app php artisan event:clear
petora_compose run --rm --no-deps app php artisan config:cache
petora_compose run --rm --no-deps app php artisan route:cache
petora_compose run --rm --no-deps app php artisan view:cache
petora_compose run --rm --no-deps app php artisan event:cache
petora_compose up -d --force-recreate --scale worker=2 app worker scheduler web caddy backup
petora_compose exec -T app php artisan up

APP_URL="$(petora_compose exec -T app php -r 'echo getenv("APP_URL");' | tr -d '\r\n')"
"${ROOT_DIR}/scripts/smoke.sh" "$APP_URL"
printf 'Rollback to image tag %s passed health checks. Database migrations were not reversed.\n' "$ROLLBACK_TAG"
