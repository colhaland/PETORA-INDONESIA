# Runbook: Rollback & Restore (Skeleton)

**Status**: Skeleton — drill wajib sebelum produksi (MTS 13.3, 15.4).

## Rollback aplikasi
- Rollback tanpa rollback skema (expand–contract).
- Gunakan image/tag rilis sebelumnya.
- Jalankan health check.

## Restore backup
- `pg_dump` custom format; backup terenkripsi.
- Uji restore mingguan ke staging (otomatis bila memungkinkan).
- Simulasi restore bulanan + dokumentasi hasil.

## TODO
- Integrasi `spatie/laravel-backup` atau skrip `pg_dump`.
- Prosedur rotasi `APP_KEY` (dampak data terenkripsi).
