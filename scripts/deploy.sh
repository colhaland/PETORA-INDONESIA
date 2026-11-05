#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"
source "${ROOT_DIR}/scripts/compose.sh"

IMAGE_TAG="${1:-}"
if [[ ! "$IMAGE_TAG" =~ ^[A-Za-z0-9][A-Za-z0-9._-]{0,127}$ ]]; then
    echo "Usage: scripts/deploy.sh IMAGE_TAG" >&2
    exit 2
fi

ENV_FILE="${PETORA_ENV_FILE:-${ROOT_DIR}/.env.production}"
if [[ ! -f "$ENV_FILE" ]]; then
    printf 'Production environment file not found: %s\n' "$ENV_FILE" >&2
    exit 1
fi

STATE_DIR="${PETORA_DEPLOY_STATE_DIR:-${ROOT_DIR}/.deploy}"
mkdir -p "$STATE_DIR"
chmod 0700 "$STATE_DIR"
exec 9>"${STATE_DIR}/deploy.lock"
if ! flock -n 9; then
    echo "Another deployment or rollback is already running." >&2
    exit 1
fi
export PETORA_DEPLOY_LOCK_HELD=1

previous_tag=""
app_container="$(petora_compose ps -q app)"
if [[ -n "$app_container" ]]; then
    current_image="$(docker inspect --format '{{.Config.Image}}' "$app_container")"
    previous_tag="${current_image##*:}"
    if [[ ! "$previous_tag" =~ ^[A-Za-z0-9][A-Za-z0-9._-]{0,127}$ ]]; then
        echo "Cannot safely determine the currently deployed image tag." >&2
        exit 1
    fi
    printf '%s\n' "$previous_tag" > "${STATE_DIR}/previous_tag.tmp"
    chmod 0600 "${STATE_DIR}/previous_tag.tmp"
    mv "${STATE_DIR}/previous_tag.tmp" "${STATE_DIR}/previous_tag"
fi

deploy_started=false
rollback_after_failure() {
    status=$?
    trap - ERR
    if [[ "$deploy_started" == true ]]; then
        if [[ -n "$previous_tag" ]]; then
            echo "Deployment failed; restoring image tag ${previous_tag}." >&2
            "${ROOT_DIR}/scripts/rollback.sh" "$previous_tag" || {
                echo "Automatic rollback failed. Keep the system in maintenance and investigate immediately." >&2
                exit 1
            }
        else
            echo "Initial deployment failed; stopping public services without removing persistent data." >&2
            if ! petora_compose stop caddy web app worker scheduler; then
                echo "Could not stop every public service after the initial deployment failed." >&2
            fi
        fi
    fi
    exit "$status"
}
trap rollback_after_failure ERR

export IMAGE_TAG
petora_compose config --quiet
deploy_started=true
petora_compose pull app web
petora_compose pull backup

if [[ -n "$app_container" ]]; then
    petora_compose run --rm --no-deps backup /usr/local/bin/backup-postgres.sh
fi

if [[ -n "$app_container" ]]; then
    petora_compose exec -T app php artisan down --retry=60
fi

petora_compose run --rm --no-deps app php artisan config:clear
petora_compose run --rm --no-deps app php artisan route:clear
petora_compose run --rm --no-deps app php artisan view:clear
petora_compose run --rm --no-deps app php artisan event:clear
petora_compose run --rm --no-deps app php artisan petora:migrate
petora_compose run --rm --no-deps app php artisan config:cache
petora_compose run --rm --no-deps app php artisan route:cache
petora_compose run --rm --no-deps app php artisan view:cache
petora_compose run --rm --no-deps app php artisan event:cache
petora_compose up -d --force-recreate --scale worker=2 app worker scheduler web caddy backup
petora_compose exec -T app php artisan up
APP_URL="$(petora_compose exec -T app php -r 'echo getenv("APP_URL");' | tr -d '\r\n')"
"${ROOT_DIR}/scripts/smoke.sh" "$APP_URL"

deploy_started=false
printf 'Deployment of image tag %s passed health checks.\n' "$IMAGE_TAG"
