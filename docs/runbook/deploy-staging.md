# Runbook: Deploy Staging (Skeleton)

**Status**: Skeleton — belum operasional penuh (MTS Fase 0/5.3/15.4).

## Target
- Staging otomatis dari merge ke `develop`.
- Migrasi `--force`, cache/config rebuild, health check.
- Rollback ke image/tag sebelumnya jika health check gagal.

## Prasyarat
- PostgreSQL 16, Redis 7, secrets via GitHub Environments.
- Docker image di GHCR.
- `scripts/deploy.sh` dan `scripts/rollback.sh` diuji di staging.

## TODO
- Implement Dockerfile (Nginx+php-fpm) + Caddy edge config.
- Tambah workflow `deploy-staging.yml`.
- Definisikan health endpoint dan smoke test Playwright.
