#!/usr/bin/env bash
set -euo pipefail
umask 077

timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
destination="/backups/petora-${timestamp}.dump.gpg"
temporary="${destination}.tmp"
encryption_key="/run/secrets/backup-encryption-key"

if [ ! -r "$encryption_key" ]; then
    echo "Backup encryption key is missing or unreadable." >&2
    exit 1
fi

trap 'rm -f "$temporary"' EXIT
trap 'exit 1' HUP INT TERM
pg_dump --format=custom \
    | gpg --batch --yes --pinentry-mode loopback --passphrase-file "$encryption_key" \
        --symmetric --cipher-algo AES256 --output "$temporary"
chmod 0600 "$temporary"
mv "$temporary" "$destination"
trap - EXIT HUP INT TERM

find /backups -type f -name 'petora-*.dump.gpg' -mtime "+${BACKUP_RETENTION_DAYS:-35}" -delete
printf 'Encrypted PostgreSQL backup completed: %s\n' "$destination"
