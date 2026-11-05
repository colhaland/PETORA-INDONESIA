#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${PETORA_ENV_FILE:-${ROOT_DIR}/.env.production}"
COMPOSE_FILE="${ROOT_DIR}/compose.prod.yaml"
if [[ "$ENV_FILE" != /* ]]; then
    ENV_FILE="${ROOT_DIR}/${ENV_FILE}"
fi

petora_compose() {
    if [[ ! -f "$ENV_FILE" ]]; then
        printf 'Production environment file not found: %s\n' "$ENV_FILE" >&2
        return 1
    fi

    PETORA_DOTENV_FILE="$ENV_FILE" docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

if [[ "${BASH_SOURCE[0]}" == "$0" ]]; then
    petora_compose "$@"
fi
