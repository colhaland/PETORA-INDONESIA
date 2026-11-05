# PETORA

Integrated Petshop & Pet Care Management System — aplikasi web responsif untuk pengelolaan operasional petshop dan pet care (pelanggan, hewan, produk, stok, pembelian, POS, booking, grooming, klinik, rekam medis, farmasi, loyalty, promosi, keuangan, dan laporan) dengan dukungan multi-cabang.

## Teknologi

- PHP 8.3, Laravel 11, Livewire 3, Alpine.js, Tailwind CSS 3.4
- PostgreSQL 16, Redis 7
- Docker, Docker Compose v2, Caddy, Nginx + php-fpm
- spatie/laravel-permission, pragmarx/google2fa-laravel, dompdf, maatwebsite/excel
- Pest, Larastan (level 6), Laravel Pint

## Persyaratan

- PHP 8.3+ dan Composer
- Node.js 20+ dan npm
- PostgreSQL 16, Redis 7 (atau Docker)

## Instalasi

```bash
git clone <repo-url> petora
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

Atau dengan Docker:

```bash
docker compose up -d
```

## Menjalankan Tes

```bash
vendor/bin/pest
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## Struktur

Modul bisnis berada di `app/Modules/<NamaModul>/` (`Services`, `Livewire`, `Policies`, `Queries`, `Requests`, `routes.php`). Aturan arsitektur dan spesifikasi lengkap: `PETORA_MTS_v2.md` dan `AGENT.md`.

## Lisensi

MIT.
