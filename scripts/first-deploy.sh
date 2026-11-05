#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"
source "${ROOT_DIR}/scripts/compose.sh"

IMAGE_TAG="${1:-}"
if [[ ! "$IMAGE_TAG" =~ ^[A-Za-z0-9][A-Za-z0-9._-]{0,127}$ ]]; then
    echo "Usage: scripts/first-deploy.sh IMAGE_TAG" >&2
    exit 2
fi

STATE_DIR="${PETORA_DEPLOY_STATE_DIR:-${ROOT_DIR}/.deploy}"
mkdir -p "$STATE_DIR"
chmod 0700 "$STATE_DIR"
exec 9>"${STATE_DIR}/deploy.lock"
if ! flock -n 9; then
    echo "Another deployment or rollback is already running." >&2
    exit 1
fi
export IMAGE_TAG

"${ROOT_DIR}/scripts/bootstrap-server.sh"
if [[ ! -t 0 || ! -t 1 ]]; then
    echo "Initial Super Admin creation is interactive; run this script from a terminal." >&2
    exit 1
fi

petora_compose up -d postgres redis
database_ready=false
redis_ready=false
for attempt in $(seq 1 60); do
    if [[ "$database_ready" != true ]] && petora_compose exec -T postgres sh -c \
        'pg_isready -U "$POSTGRES_USER" -d "$POSTGRES_DB"' >/dev/null 2>&1; then
        database_ready=true
    fi
    if [[ "$redis_ready" != true ]] && petora_compose exec -T redis sh -c \
        'REDISCLI_AUTH="$REDIS_PASSWORD" redis-cli --tls --cacert /run/secrets/redis-ca.crt -h 127.0.0.1 ping | grep -q PONG' >/dev/null 2>&1; then
        redis_ready=true
    fi
    if [[ "$database_ready" == true && "$redis_ready" == true ]]; then
        break
    fi
    sleep 2
done

if [[ "$database_ready" != true || "$redis_ready" != true ]]; then
    echo "PostgreSQL or Redis did not become ready; inspect container logs before retrying." >&2
    exit 1
fi

petora_compose pull app web backup
petora_compose run --rm --no-deps app php artisan petora:migrate
petora_compose run --rm --no-deps app php artisan db:seed --force
petora_compose run --rm --no-deps -it app php artisan petora:bootstrap-admin
petora_compose run --rm --no-deps app php artisan config:cache
petora_compose run --rm --no-deps app php artisan route:cache
petora_compose run --rm --no-deps app php artisan view:cache
petora_compose run --rm --no-deps app php artisan event:cache

public_services_started=false
stop_public_services_on_failure() {
    status=$?
    trap - ERR
    if [[ "$public_services_started" == true ]]; then
        app_container="$(petora_compose ps -q app)"
        if [[ -n "$app_container" ]] && ! petora_compose exec -T app php artisan down; then
            echo "Could not enable maintenance mode after initial deployment failure." >&2
        fi
        if ! petora_compose stop caddy web app worker scheduler; then
            echo "Could not stop every public service after initial deployment failure." >&2
        fi
    fi
    exit "$status"
}
trap stop_public_services_on_failure ERR

public_services_started=true
petora_compose up -d --scale worker=2 app worker scheduler web caddy backup
petora_compose exec -T app php artisan up

APP_URL="$(petora_compose exec -T app php -r 'echo getenv("APP_URL");' | tr -d '\r\n')"
"${ROOT_DIR}/scripts/smoke.sh" "$APP_URL"
public_services_started=false
trap - ERR
printf 'Initial deployment is healthy. Sign in with the Super Admin account created interactively and enroll 2FA.\n'
