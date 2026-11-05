#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
source "${ROOT_DIR}/scripts/compose.sh"

BASE_URL="${1:-}"
if [[ ! "$BASE_URL" =~ ^https://[A-Za-z0-9.-]+(:[0-9]+)?$ ]]; then
    echo "Usage: scripts/smoke.sh https://application.example.com" >&2
    exit 2
fi

curl --fail --silent --show-error --max-time 10 "${BASE_URL}/up" >/dev/null
petora_compose exec -T app sh -c \
    'curl --fail --silent --show-error --max-time 10 -H "Authorization: Bearer $HEALTHCHECK_TOKEN" -H "Host: $APP_DOMAIN" http://web/health >/dev/null'
printf 'Public readiness and authenticated internal dependency checks passed for %s.\n' "$BASE_URL"
