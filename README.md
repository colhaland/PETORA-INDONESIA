# PETORA

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis&logoColor=white)](https://redis.io/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

PETORA adalah sistem manajemen terintegrasi untuk petshop dan pet care: pelanggan, hewan, produk, stok, pembelian, POS, booking, grooming, klinik, rekam medis, farmasi, loyalty, promosi, keuangan, dan laporan — mendukung multi-cabang.

## Modul (31 modul)

| # | Modul | Rilis | Ringkasan |
|---|---|---|---|
| 7.1 | Dashboard | R1 | KPI harian, grafik penjualan, notifikasi stok & kedaluwarsa. |
| 7.2 | User Management | R1 | Pengelolaan pengguna, status, cabang akses. |
| 7.3 | Role & Permission | R1 | Peran dinamis, permission, data scope. |
| 7.4 | Branch Management | R1 | Cabang, gudang, kas, konfigurasi per cabang. |
| 7.5 | Customer Management | R1 | Data pelanggan, riwayat, poin. |
| 7.6 | Pet Management | R1 | Hewan peliharaan, vaksinasi, alergi. |
| 7.7 | Product, Inventory, Warehouse | R1 | Produk, varian, batch, stok, gudang. |
| 7.8 | POS & Sales | R1 | Kasir, pembayaran, refund, void. |
| 7.9 | Purchasing & Supplier | R1 | PO, penerimaan, retur pembelian. |
| 7.10 | Grooming | R1 | Layanan grooming, jadwal, groomer. |
| 7.11 | Booking | R1 | Booking lintas modul, cek kapasitas. |
| 7.12 | Pet Hotel & Daycare | R2 | Kamar, check-in/out, tarif. |
| 7.13 | Transport | R2 | Antar-jemput hewan. |
| 7.14 | Home Grooming | R2 | Grooming di rumah pelanggan. |
| 7.15 | Veterinary Management | R1 | Dokter, jadwal, kunjungan. |
| 7.16 | Medical Records | R1 | Rekam medis, diagnosis, revisi. |
| 7.17 | Pharmacy & Medicine | R1 | Obat, resep, stok obat. |
| 7.18 | Membership | R2 | Paket membership, benefit. |
| 7.19 | Loyalty | R1/R2 | Poin dan reward. |
| 7.20 | Promotion | R1/R2 | Diskon, voucher, bundling, B1G1. |
| 7.21 | Staff Management | R1 | Data staf, jadwal, shift. |
| 7.22 | Attendance | R2 | Absensi, lembur. |
| 7.23 | Commission | R2 | Komisi staf/groomer. |
| 7.24 | Finance | R1 | Kas, transaksi, penutupan kas. |
| 7.25 | Accounting | R3 | Jurnal, COA, laporan keuangan. |
| 7.26 | Consignment | R3 | Barang titipan. |
| 7.27 | Reports & Analytics | R1 | Laporan penjualan, stok, keuangan. |
| 7.28 | Notification | R1 | Email/SMS/WA, template. |
| 7.29 | Customer Portal | R1 | Portal pelanggan (self-service). |
| 7.30 | System Settings | R1 | Pengaturan global, pajak, pembulatan. |
| 7.31 | Integration Settings | R1 | Kredensial integrasi terenkripsi. |

## Fitur Utama

- **POS & Penjualan** — kasir, pembayaran multi-metode, refund, idempotency key.
- **Stok** — `stock_movements` append-only, FEFO/FIFO, saldo per cabang.
- **Klinik & Rekam Medis** — kunjungan, diagnosis, revisi, visibilitas ke pemilik hewan.
- **Booking & Grooming** — cek kapasitas dengan lock, jadwal groomer.
- **Loyalty & Promosi** — poin dari nilai setelah diskon, kupon, program promosi.
- **Keuangan & Akuntansi** — kas, transaksi, laporan.
- **RBAC** — peran dinamis, 2FA, scope data per cabang.
- **Portal Pelanggan** — akses data sendiri (read-only, item `visible_to_owner`).
- **Integrasi** — adapter pihak ketiga terenkripsi di database."

## Tumpukan Teknologi

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.3, Laravel 11 |
| Frontend | Livewire 3, Alpine.js, Tailwind CSS 3.4 |
| Database | PostgreSQL 16 |
| Cache/Queue/Session | Redis 7 |
| Infrastruktur | Docker, Docker Compose v2, Caddy, Nginx + php-fpm |
| PDF/Excel | dompdf, maatwebsite/excel |
| RBAC/2FA | spatie/laravel-permission, pragmarx/google2fa-laravel |
| Testing | Pest, Larastan (level 6), Laravel Pint |

## Persyaratan

- PHP 8.3+ & Composer
- Node.js 20+ & npm
- PostgreSQL 16 & Redis 7 (atau Docker)

## Instalasi

### Lokal

```bash
git clone https://github.com/<org>/petora.git
cd petora
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed
npm run build
php artisan serve
```

### Docker

```bash
docker compose up -d
```

## Pengujian

```bash
vendor/bin/pest            # test
vendor/bin/pint --test     # format
vendor/bin/phpstan analyse # analisis statis
```

## Struktur Proyek

```
app/
├── Modules/        # modul bisnis (Services, Livewire, Policies, Queries, Requests)
├── Integrations/   # adapter pihak ketiga
├── Contracts/
└── ...
database/
├── migrations/
└── seeders/
resources/
├── views/
└── lang/id/
docs/
├── ADR/
└── runbook/
```

Aturan arsitektur dan spesifikasi lengkap: [`PETORA_MTS_v2.md`](PETORA_MTS_v2.md) dan [`AGENT.md`](AGENT.md).

## Kontribusi

1. Buat cabang `feature/<modul>-<deskripsi>` dari `develop`.
2. Ikuti Conventional Commits: `feat(pos): ...`, `fix(stock): ...`.
3. Pastikan Pint, Larastan, dan Pest lulus sebelum PR.
4. Satu commit satu perubahan logis.

## Lisensi

Dirilis di bawah [MIT License](LICENSE).

---

> Sumber kebenaran spesifikasi: `PETORA_MTS_v2.md`. Jika ada pertentangan, ikuti dokumen tersebut.
