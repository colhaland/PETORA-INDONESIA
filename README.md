# PETORA

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis&logoColor=white)](https://redis.io/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

PETORA adalah sistem manajemen terintegrasi untuk petshop dan pet care: pelanggan, hewan, produk, stok, pembelian, POS, booking, grooming, klinik, rekam medis, farmasi, loyalty, promosi, keuangan, dan laporan — mendukung multi-cabang.

## Fitur Utama

- **POS & Penjualan** — kasir, pembayaran multi-metode, refund, idempotency key.
- **Stok** — `stock_movements` append-only, FEFO/FIFO, saldo per cabang.
- **Klinik & Rekam Medis** — kunjungan, diagnosis, revisi, visibilitas ke pemilik hewan.
- **Booking & Grooming** — cek kapasitas dengan lock, jadwal groomer.
- **Loyalty & Promosi** — poin dari nilai setelah diskon, kupon, program promosi.
- **Keuangan & Akuntansi** — kas, transaksi, laporan.
- **RBAC** — peran dinamis, 2FA, scope data per cabang.
- **Portal Pelanggan** — akses data sendiri (read-only, item `visible_to_owner`).
- **Integrasi** — adapter pihak ketiga terenkripsi di database.

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
