# ADR 0001 — Deviasi Stack dari MTS v2.0 dan Rencana Remediasi

**Status**: Diterima (sementara, perlu konfirmasi pemilik proyek)  
**Tanggal**: 2026-11-05  
**Konteks**: MTS Bagian 0/2 menetapkan PHP 8.3, Laravel 11, Livewire 3, Tailwind 3.4, PostgreSQL 16, Redis 7, Caddy+Nginx, CI GitHub Actions. Repositori saat ini berjalan pada PHP 8.4.27, Laravel 13.40.0, Livewire 4.4, Tailwind 4/Vite 8, dengan default dev/test SQLite dan tanpa workflow/Docker produksi.

## Keputusan
1. **Jangka pendek (remediasi)**: mempertahankan stack runtime saat ini agar 118 test existing tetap dapat dijalankan, sambil memperbaiki ketidakpatuhan domain/keamanan/arsitektur yang bersifat stack-independen (RBAC enforcement, 2FA, lockout, integritas data, migrasi/constraint yang kompatibel, struktur modul, i18n, CI).
2. **Konfigurasi target produksi** tetap diarahkan ke PostgreSQL 16 + Redis 7 + Docker/Caddy/Nginx sesuai MTS; `.env.example` dan infrastruktur akan diselaraskan.
3. **Downgrade Laravel/Livewire/Tailwind** tidak dilakukan dalam perubahan besar otomatis karena risiko regresi tinggi; evaluasi realignment penuh dilakukan sebagai pekerjaan terpisah dengan rencana migrasi dan persetujuan pemilik proyek.
4. Testing kritis integritas (trigger PG, exclusion constraint, locking) akan ditambahkan dengan jalur PostgreSQL di CI; suite SQLite tetap dipertahankan untuk kecepatan unit/feature non-PG-specific.

## Konsekuensi
- Kepatuhan MTS stack **belum penuh** sampai realignment disetujui/dieksekusi; deviasi ini dicatat dan harus ditinjau.
- Semua perbaikan domain/keamanan tetap wajib mengikuti aturan MTS (Bagian 0, 6, 8, 11).
- Risiko: perbedaan perilaku PG vs SQLite; mitigasi: guard DB-specific DDL dan tambah test PG.
