# Production deployment runbook

The production stack targets Ubuntu 24.04 with Docker Compose. It is not safe to deploy until all external prerequisites below are configured. The example environment file contains placeholders only.

## Host and secrets

1. Install Docker Engine and the Compose plugin. Create `/etc/petora/secrets` with mode `0700`; keep the production environment file at `/etc/petora/.env.production` with mode `0600`.
2. Provide PostgreSQL server TLS certificates whose SAN contains `postgres`, Redis TLS certificates whose SAN contains `redis`, and a CA certificate trusted by both services. The PostgreSQL server private key must be readable by the container's `postgres` user (UID 999) and have mode `0600`. Do not expose PostgreSQL or Redis ports on the host firewall.
3. Generate unique database and Redis passwords, a Laravel `APP_KEY`, an unguessable `HEALTHCHECK_TOKEN`, SMTP credentials, and a separate high-entropy backup encryption passphrase. Store the passphrase in `/etc/petora/secrets/backup-encryption-key`, mode `0600`; keep an offline recovery copy separate from the VPS.
4. Copy `.env.production.example` to the host environment file, set every required value, and set `IMAGE_TAG` to a tag already published to GHCR. Set `APP_DOMAIN`, `APP_URL`, and `ACME_EMAIL` to the real HTTPS site. Do not check real values or certificates into Git.
5. Before starting services, verify certificate names with `openssl x509 -in CERT_FILE -noout -checkhost postgres` and `... redis` as applicable. Configure the VPS firewall to allow only SSH and the public HTTPS/HTTP ports.

## First deployment

From a terminal on the VPS, in a trusted clone of this repository:

```sh
PETORA_ENV_FILE=/etc/petora/.env.production \
PETORA_DEPLOY_STATE_DIR=/var/lib/petora \
scripts/first-deploy.sh IMAGE_TAG
```

The script validates Docker Compose, waits for PostgreSQL and Redis, runs migrations through the PostgreSQL advisory-lock command, seeds baseline roles, and interactively creates the first Super Admin. It does not expose a bootstrap HTTP endpoint. Sign in and enroll 2FA. The command refuses to create another user if any user already exists. If the script fails after creating the account, resume with `scripts/deploy.sh IMAGE_TAG`; do not rerun the one-time bootstrap.

## Continuous deployment

Configure GitHub `staging` and `production` environments and their SSH secrets before enabling deployment. Set repository variables `STAGING_DEPLOY_ENABLED=true` and `PRODUCTION_DEPLOY_ENABLED=true` only after validating their respective environments. The SSH account must be restricted to the deployment host and have access to Docker, the trusted repository clone, `/etc/petora/.env.production`, and `/var/lib/petora`. Configure production environment protection with required reviewers. The server must have authenticated pull access to GHCR (or the package must be intentionally public).

- Successful pushes to `develop` publish commit-SHA images and can trigger staging deployment.
- Successful `vMAJOR.MINOR.PATCH` tags publish release-tagged images and can trigger production deployment after environment approval.
- CI deployment uses a detached Git worktree for the exact tested commit; it does not reset the server's base checkout.
- Staging secrets: `STAGING_HOST`, `STAGING_USER`, `STAGING_REPOSITORY_PATH`, `STAGING_SSH_KEY`, `STAGING_KNOWN_HOSTS`.
- Production secrets use the same names with the `PRODUCTION_` prefix.

The deploy script serializes deployments, takes an encrypted database backup before upgrades, uses the advisory-locked migration command, and checks public readiness plus authenticated internal database/Redis health. If an upgrade fails after startup begins, it attempts to restore the previous image tag. Database migrations are never automatically reversed; migrations must be backward-compatible with the previous application image. Investigate failures and restore from a verified backup only through a separately reviewed recovery procedure.

## Backup and recovery

The backup service produces GPG-encrypted PostgreSQL custom-format dumps in a named Docker volume and retains encrypted dumps for the configured retention period. The encryption passphrase is not stored in the backup volume. Copy backups off-host and test decrypt/restore regularly; a local Docker volume is not disaster recovery.

For recovery, first decrypt a selected dump using the separately protected passphrase and restore it into an isolated PostgreSQL instance. Validate the restored data and application compatibility before any production cutover. Never overwrite the production database as an unreviewed deployment or rollback action.
