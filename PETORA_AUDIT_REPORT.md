# Laporan Audit Codebase PETORA

## Status Remediasi Tahap 0

Status di bawah memperbarui F-01 sampai F-14 berdasarkan perubahan kode dan verifikasi tes setelah audit. **Tahap 0 belum selesai**: F-01 dan F-13 masih terbuka/parsial.

| Temuan | Status | Perbaikan dan bukti | Sisa pekerjaan |
|---|---|---|---|
| F-01 | Parsial | Dashboard menampilkan omzet/order hari ini dan pelanggan bertransaksi berdasarkan cabang aktif; laporan menghitung order lunas/selesai dengan scope cabang. Ditambahkan akses cepat berbasis permission dan tautan Laporan. Tes UI/otorisasi dan build lulus. | Banyak modul masih stub, dan sebagian belum memiliki schema/model maupun alur bisnis yang mendukung implementasi nyata. |
| F-02 | Selesai | Enrollment 2FA Admin/Super Admin, konfirmasi TOTP, secret terenkripsi, dan kode pemulihan diterapkan; alur diuji. | Tidak ada yang diketahui dalam cakupan temuan ini. |
| F-03 | Selesai | Perintah bootstrap Super Admin hanya berjalan pada instalasi tanpa pengguna, meminta kredensial secara interaktif, dan memakai marker database untuk mencegah bootstrap ulang/bersamaan. | Belum ada yang diketahui; tetap jalankan hanya pada database awal yang benar. |
| F-04 | Selesai | Produk dapat ditandai eksplisit untuk pelacakan stok; pembayaran lunas mengeluarkan stok secara atomik, penerimaan PO mendukung penerimaan parsial dan idempotensi, serta gudang/cabang/lot divalidasi server-side. Kekurangan stok membatalkan pembayaran. Tes PostgreSQL 16 lulus. | Sebelum deployment dengan data lama, periksa duplikasi saldo untuk lot yang sama; indeks unik baru sengaja gagal aman jika duplikasi ditemukan. |
| F-05 | Selesai | Pembayaran memvalidasi status dan nominal, membatasi jumlah terhadap saldo, dan menggunakan kunci idempotensi unik. | Tidak ada yang diketahui dalam cakupan temuan ini. |
| F-06 | Selesai (diskon dinonaktifkan) | Harga dan pajak dihitung server-side, harga minimum diperiksa, diskon ditolak saat pembayaran, dan PostgreSQL menegakkan constraint diskon nol. Kebijakan diskon belum tersedia, jadi fitur tetap nonaktif. | Jika diskon akan diaktifkan kelak, tetapkan kebijakan dan implementasikan validasi/otorisasi terpisah sebelum mengubah constraint. |
| F-07 | Selesai | Revisi menyimpan snapshot JSON valid dan menggunakan jalur yang diizinkan trigger PostgreSQL. Tes migrasi dan suite PostgreSQL lulus. | Tidak ada yang diketahui dalam cakupan temuan ini. |
| F-08 | Selesai | Password statis dihapus; akun baru menggunakan kredensial acak dan alur reset password. | Pengiriman email produksi perlu kredensial SMTP yang sah. |
| F-09 | Selesai | Rate limiting/lockout 2FA, pencegahan replay berbasis counter, kode pemulihan satu kali, dan penghapusan fallback plaintext diterapkan. | Tidak ada yang diketahui dalam cakupan temuan ini. |
| F-10 | Selesai | Cabang divalidasi server-side dari akses pengguna; form dan service tidak mempercayai cabang yang dimanipulasi klien. | Tidak ada yang diketahui dalam cakupan temuan ini. |
| F-11 | Selesai | Identitas dokter ditentukan dari pengguna terautentikasi dan diuji di service. | Tidak ada yang diketahui dalam cakupan temuan ini. |
| F-12 | Selesai | Perubahan Settings memerlukan `settings.manage`, dengan whitelist dan validasi nilai di service. | Tidak ada yang diketahui dalam cakupan temuan ini. |
| F-13 | Parsial | Workflow CI PostgreSQL ditambahkan; guard produksi menolak konfigurasi tidak aman; skrip deploy/rollback gagal eksplisit alih-alih mengklaim sukses. | Deployment nyata belum dibuat karena target infrastruktur dan prosedur rilis belum tersedia. |
| F-14 | Selesai | Foreign key data medis/keuangan memakai kebijakan restrict dan trigger revisi medis dilindungi; tes PostgreSQL lulus. | Uji operasional restore/retensi tetap diperlukan sebelum produksi. |

### Verifikasi remediasi

- PostgreSQL 16: suite penuh pada tahap sebelumnya **183 passed (290 assertions)**; verifikasi fokus terakhir setelah perubahan dashboard/laporan **9 passed (17 assertions)**. Tes dashboard/laporan bersama tes RBAC juga lulus (**23 passed, 30 assertions**) sebelum perapian lookup nama cabang.
- SQLite: suite penuh saat ini — **184 passed, 3 skipped (295 assertions)**; tes yang dilewati memerlukan perilaku khusus PostgreSQL.
- `./vendor/bin/pint --test --dirty` — lulus.
- `npm run build` — lulus; Vite menampilkan peringatan opsional bahwa `fontaine` tidak terpasang.
- Tes fixture menggunakan ID cabang aktual, bukan mengasumsikan sequence PostgreSQL selalu menghasilkan ID `1`.

Migrasi `database/migrations/2026_11_05_130000_harden_auth_payments_audit_and_foreign_keys.php` menambahkan state counter TOTP, kode pemulihan, marker bootstrap, idempotensi pembayaran, perlindungan append-only audit, perlindungan revisi medis, dan foreign key restrict. Migrasi `database/migrations/2026_11_05_140000_connect_catalog_purchase_and_stock.php` menambahkan flag pelacakan stok, gudang order, item PO/receipt, marker idempotensi penerimaan, indeks saldo lot, dan constraint diskon nol di PostgreSQL. Kedua migrasi dijalankan oleh suite PostgreSQL 16. Tidak ada data produksi yang dihapus; indeks saldo baru akan gagal aman bila data lama mengandung saldo lot duplikat, sehingga perlu diperiksa sebelum deployment.

Hasil ini **tidak memenuhi Definition of Done Tahap 0**. F-01 belum diselesaikan karena masih ada modul dengan layar stub dan tidak semua memiliki skema/alur bisnis aktual yang cukup untuk implementasi tanpa membuat fungsi palsu. F-13 masih parsial karena target infrastruktur dan prosedur rilis belum tersedia. F-04 dan F-06 sudah ditangani dengan cakupan yang dijelaskan di atas.

Tanggal audit: 5 November 2026
Objek audit: `PETORA-INDONESIA-main` (arsip ZIP yang diunggah)
Acuan: `PETORA_MTS_v2.md`, `AGENT.md`, `docs/ADR/0001-stack-deviation-and-remediation.md`
Metode: tinjauan statis penuh terhadap kode, skema migrasi, konfigurasi, test, infrastruktur, dan dokumentasi.

> **Batasan audit awal (5 November 2026):** Lingkungan audit saat itu tidak memiliki PHP, Composer, maupun akses jaringan, sehingga test suite dan aplikasi tidak dijalankan. Status remediasi di atas merupakan verifikasi runtime berikutnya, termasuk suite PostgreSQL 16.

---

## 1. Ringkasan Eksekutif

### 1.1 Kesimpulan

**Codebase belum siap deployment, dan belum dapat digunakan oleh pengguna akhir.** Fondasi arsitektur (RBAC berbasis Spatie, scope cabang, sekuens nomor dokumen dengan lock, trigger PostgreSQL untuk integritas data) sudah benar arahnya. Namun sebagian besar modul hanya berupa kerangka, beberapa alur inti tidak bekerja, dan ada beberapa celah keamanan yang bisa langsung dieksploitasi atau membuat akun administrator terkunci.

### 1.2 Angka utama

| Metrik | Nilai |
|---|---|
| File PHP di `app/` | 138 file, ±3.763 baris |
| File test | 62 file, 155 fungsi test, ±2.573 baris |
| Livewire component | 67, dan 44 di antaranya hanya ≤16 baris (daftar kosong / stub) |
| Service | 30, dan 25 di antaranya ≤40 baris |
| Service stub murni (mengembalikan data tetap, tanpa database) | 8 (Accounting, Attendance, Consignment, CustomerPortal, HomeGrooming, Hotel, Membership, Transport) |
| Service parsial (hitung tanpa menyimpan) | 4 (Commission, Promotion, Loyalty, Integration) |
| View Blade | 71 file, 36 di antaranya ≤40 byte (stub, misalnya `<div><h1>Hotel</h1></div>`) |
| Tabel database | ±40 tabel (termasuk tabel framework seperti `jobs`, `cache`, `sessions`) dari 33 file migrasi |
| Tabel yang disebut MTS 8.2 | 77, dan 25 di antaranya ada (32%) |
| Trigger PostgreSQL | 4 (stok, order lunas, kas tutup, rekam medis final) |
| Exclusion constraint (anti-bentrok booking/kamar) | 0 (MTS 8.3 mensyaratkan) |
| Permission yang di-seed | 149, dan 72 di antaranya tidak ditemukan sebagai string literal di kode maupun route (lihat catatan Lampiran B) |
| Pipeline CI/CD | Tidak ada (`.github/` tidak ada) |
| Dockerfile produksi, Caddy, compose produksi | Tidak ada |
| Skrip deploy dan rollback | Skeleton, hanya `echo` lalu keluar dengan kode 0 |

### 1.3 Penilaian per fase MTS

| Fase (MTS 15.2) | Status | Alasan singkat |
|---|---|---|
| 0. Repo, Docker, CI/CD, staging | **Belum** | Tidak ada CI, Dockerfile, compose produksi, atau workflow. |
| 1. Auth, 2FA, RBAC, cabang, audit, pengaturan, integrasi | **Gagal gerbang** | Administrator terkunci karena 2FA tidak bisa diaktifkan; pengaturan bisa diubah tanpa izin; kolom audit tertentu terbuang. |
| 2. Pelanggan, hewan, produk, stok, pembelian, POS | **Belum** | POS tidak mengurangi stok, bisa membayar berulang, harga datang dari input klien. |
| 3. Booking, grooming, portal, notifikasi | **Belum** | Pengecekan bentrok tidak pernah dijalankan; portal pelanggan tidak ada. |
| 4. Klinik, rekam medis, farmasi, loyalty, promosi, keuangan, laporan | **Belum** | Revisi rekam medis gagal di PostgreSQL; loyalty dan promosi tidak tersimpan. |
| 5. Stabilisasi Rilis 1 | **Belum** | Prasyarat fase 0–4 belum terpenuhi. |

### 1.4 Lima risiko teratas

1. **Administrator terkunci (F-01, F-02).** Admin dan Super Admin diarahkan ke halaman setup 2FA, yang hanya berisi teks. Tidak ada kode yang menyimpan rahasia 2FA. Sistem tidak bisa dipakai oleh peran ini sama sekali.
2. **Tidak ada cara membuat Super Admin pertama (F-03).** `DatabaseSeeder` tidak membuat pengguna.
3. **Password default untuk staf baru (F-08).** `StaffForm` menyimpan `bcrypt('password')` untuk setiap akun baru.
4. **Penjualan tidak menyentuh stok dan bisa dibayar berulang (F-04, F-05).** Laporan stok dan keuangan akan salah sejak transaksi pertama.
5. **Batas cabang dan identitas dokter bisa dipalsukan dari sisi klien (F-10, F-11).** Form Livewire menerima `branchId` dan `doctorId` sebagai properti publik.

---

## 2. Penilaian Kepatuhan terhadap MTS

| Bagian MTS | Status | Catatan |
|---|---|---|
| 0. Keputusan teknis | Sebagian | D3–D7 (stack) menyimpang dan sudah diakui di ADR-0001. D9, D12, D24 hanya sebagian. D20 (2FA) tidak berfungsi. |
| 2. Arsitektur & teknologi | Sebagian | Laravel + Livewire + Blade sesuai. Namun versi berbeda (Laravel 13, Livewire 4, Tailwind 4, Vite 8). Cache/queue/session masih `database`, bukan Redis. |
| 3. Struktur repositori | Sebagian | Struktur `app/Modules/*` sudah konsisten. Belum ada: `Dockerfile`, `compose.prod.yaml`, `compose.staging.yaml`, `.github/workflows/`, `Makefile`, `phpstan.neon`, `routes/health.php`, `tests/Browser`, `docs/api`, `docs/CHANGELOG.md`, `CONTRIBUTING.md`, `scripts/setup.sh`, `scripts/smoke.sh`, `scripts/bootstrap-server.sh`, `scripts/first-deploy.sh`. |
| 4. Konfigurasi lingkungan | Sebagian | `.env.example` default ke SQLite, `APP_DEBUG=true`, `SESSION_ENCRYPT=false`, `CACHE_STORE=database`. Tidak ada `.env.staging.example` atau `.env.production.example`. `BACKUP_*` tidak ada. |
| 5. Deployment & otomasi | **Belum** | Skrip skeleton; tidak ada CI; tidak ada health check; tidak ada rollback nyata. |
| 6. Peran & RBAC | Sebagian | Empat peran bawaan di-seed. Peran custom tidak bisa dibuat dari UI. Data scope tidak ditegakkan. Dokter tidak dibatasi ke pasien yang ditugaskan. |
| 7. Modul (35) | Sebagian kecil | Lihat Bagian 6 laporan ini. |
| 8. Model data | Sebagian kecil | 25 dari 77 tabel. Lihat Bagian 7. |
| 9. Mesin status | Belum | Tidak ada state machine yang ditegakkan untuk order, booking, grooming, PO, transfer, atau kunjungan. |
| 10. Integrasi | Belum | Semua adapter mengembalikan `false`. Tidak ada tabel kredensial. Tidak ada route webhook. |
| 11. Keamanan | Sebagian | Lihat Bagian 8. |
| 12. UI/UX | Belum | Layout tanpa navigasi dan tanpa aset. Hampir semua halaman stub. |
| 13. Non-fungsional & backup | Belum | Tidak ada backup, tidak ada scheduler, tidak ada `/health`, tidak ada monitoring. |
| 14. Pengujian | Sebagian | Lihat Bagian 9. |
| 15. Roadmap & DoD | Belum | Tidak ada gerbang fase yang dapat diverifikasi. |
| 16. Konvensi | Sebagian | Ada Pint (default). Tidak ada Larastan/PHPStan, tidak ada Pest, `brick/money` tidak dipakai. |

---

## 3. Temuan Kritis (P0): Harus Diperbaiki Sebelum Pekerjaan Lain

Setiap temuan memuat lokasi, dampak, dan rekomendasi.

### F-01. Antarmuka tidak dapat digunakan
- **Lokasi:** `resources/views/layouts/app.blade.php` (38 byte: `<html><body>{{ $slot }}</body></html>`), `resources/js/app.js` (hanya komentar `//`), `resources/css/app.css` (hanya `@import 'tailwindcss'`).
- **Temuan:** Layout tidak memuat `@vite`, sehingga CSS dan JS tidak pernah dimuat. Tidak ada navigasi sidebar, header, atau drawer seperti dalam MTS 12.2. Alpine.js dan Chart.js tidak ada. `dashboard.blade.php` adalah HTML mandiri tanpa layout. Dari 71 view, 36 berukuran ≤40 byte.
- **Dampak:** Pengguna melihat halaman tanpa gaya dan tanpa navigasi. Tidak ada jalur untuk berpindah antar modul kecuali mengetik URL.
- **Catatan verifikasi:** Livewire v3/v4 biasanya menyuntikkan skrip-nya sendiri ke layout lengkap, jadi `wire:submit` mungkin tetap jalan. Tetapi tanpa CSS dan tanpa navigasi, aplikasi tetap tidak layak dipakai. Perlu verifikasi runtime.
- **Rekomendasi:** Bangun layout utama (sidebar, header dengan pencarian, pemilih cabang, notifikasi, profil), tambahkan `@vite`, dan terapkan palet dan tipografi MTS 12.4 (Plus Jakarta Sans, bukan Instrument Sans).

### F-02. Super Admin dan Admin terkunci karena 2FA tidak bisa diaktifkan
- **Lokasi:** `app/Modules/Auth/Middleware/EnsureTwoFactor.php`, `app/Modules/Auth/Livewire/TwoFactorSetup.php`, `resources/views/modules/auth/two-factor.blade.php` (30 byte: `<div><h1>Setup 2FA</h1></div>`).
- **Temuan:** `EnsureTwoFactor` mengalihkan Super Admin dan Admin ke `two-factor.setup` jika `two_factor_confirmed_at` kosong. Halaman tersebut hanya teks. Tidak ada kode di seluruh `app/` maupun `database/` yang menulis `two_factor_secret` atau `two_factor_confirmed_at`. Pencarian `grep` hanya menemukan pembacaan.
- **Dampak:** Setelah login pertama, Super Admin dan Admin tidak bisa mengakses halaman mana pun. Ini sekaligus menggagalkan Acceptance #1 MTS 14.4.
- **Rekomendasi:** Implementasikan alur enrollment: generate secret, tampilkan QR (`pragmarx/google2fa-qrcode` sudah ada di lock), verifikasi kode pertama, simpan secret terenkripsi, set `two_factor_confirmed_at`, buat 8 kode cadangan (MTS 11.1). Tambahkan tes end-to-end untuk alur ini.

### F-03. Tidak ada cara membuat Super Admin pertama
- **Lokasi:** `database/seeders/DatabaseSeeder.php` (hanya memanggil `PermissionSeeder`, `RoleSeeder`, `BranchSeeder`), `README.md` bagian Instalasi.
- **Dampak:** Setelah `php artisan db:seed`, tidak ada pengguna yang bisa login. Tidak ada perintah artisan untuk membuat administrator. MTS 5.5 menjelaskan langkah bootstrap yang tidak pernah diimplementasikan.
- **Rekomendasi:** Buat perintah `petora:bootstrap-admin` yang membaca `SUPER_ADMIN_*` dari lingkungan, membuat pengguna, memberi peran Super Admin, dan mewajibkan penggantian password dan aktivasi 2FA pada login pertama.

### F-04. Penjualan tidak mengurangi stok; pembelian tidak menambah stok
- **Lokasi:** `app/Modules/Pos/Services/PosService.php` (`addItem`, `pay`), `app/Modules/Purchasing/Services/PurchasingService.php` (`receive`), `app/Models/OrderItem.php`.
- **Temuan:**
  - `addItem()` menerima `string $description, float $qty, float $unitPrice`. Item tidak terhubung ke `product_id`, sehingga tidak ada yang bisa dikurangi.
  - `pay()` tidak membuat `stock_movements`. Tidak ada `sale_out` dari jalur POS.
  - `PurchasingService::receive()` hanya mengubah status PO menjadi `received` dan membuat `GoodsReceipt` kosong. Tidak ada `purchase_in`, tidak ada update `stock_balances`, dan tidak ada tabel `purchase_order_items`. Jumlah yang diterima tidak pernah dicatat.
  - `StockService::receive()` dan `issue()` hanya dipanggil dari dirinya sendiri; tidak ada komponen UI atau service lain yang memanggilnya dari alur penjualan atau pembelian.
- **Dampak:** Acceptance #11 MTS (stok otomatis berkurang) gagal. Saldo stok akan selalu tidak sesuai dengan kenyataan. MTS 7.7 dan 7.9 tidak terpenuhi.
- **Rekomendasi:** Tambahkan `order_items.product_variant_id` (atau `product_id` untuk R1 jika varian diputuskan ditunda), buat `purchase_order_items` dan `goods_receipt_items`, dan jalankan `stock_movements` di dalam transaksi `pay()` dan `receive()`. Tambahkan test konkurensi untuk unit terakhir.

### F-05. Pembayaran bisa diulang, dinegatif, dan berlebih
- **Lokasi:** `PosService::pay()`.
- **Temuan:**
  - Tidak ada validasi `$amount > 0`. Nilai negatif lolos dan mengurangi total terbayar.
  - Tidak ada pemeriksaan status. Pembayaran bisa dicatat pada order `draft`, `void`, `refunded`, atau yang sudah `paid`.
  - `$idempotencyKey` hanya disimpan jika kolom kosong. Tidak ada pemeriksaan duplikat. Dua submit dengan kunci yang sama membuat dua baris `payments`.
  - Tidak ada batas: pembayaran boleh melebihi saldo, dan tidak ada perhitungan kembalian.
  - `PaymentService::pay()` adalah duplikat dengan logika yang berbeda (float, tanpa lock, tanpa `paid_at`).
- **Dampak:** Pelanggaran MTS 12.8 (idempotensi) dan Acceptance #16. Risiko pembayaran ganda dan kas tidak cocok.
- **Rekomendasi:** Tambahkan `payments.status`, `payments.idempotency_key` dengan unique index, validasi `0 < amount <= balance`, guard status, dan hapus `PaymentService`.

### F-06. Harga, deskripsi, dan jumlah berasal dari klien
- **Lokasi:** `PosService::addItem()` dan form POS (`resources/views/modules/pos/form.blade.php` hanya stub).
- **Temuan:** Harga satuan tidak diambil dari katalog produk. Tidak ada `min_price` check, tidak ada diskon, tidak ada pajak (`tax` selalu 0, meski `config/petora.php` mengatur PPN). Kolom `discount` dan `tax` di `orders` selalu bernilai 0.
- **Dampak:** Kasir atau siapa pun yang memanggil service bisa menentukan harga sembarang. MTS 6.1 dan 5.5 dilanggar.
- **Rekomendasi:** Harga wajib diambil dari server (`product_prices` per cabang atau harga dasar), diskon diverifikasi terhadap `min_price`, pajak dihitung dari `config`/`system_settings` di service.

### F-07. Revisi rekam medis tidak berfungsi di PostgreSQL
- **Lokasi:** `database/migrations/2026_11_05_120500_add_branch_data_scope_and_medical_guards.php` (trigger `petora_reject_final_medical_update`), `app/Modules/Veterinary/Services/VeterinaryService.php` (`revise()`), migrasi `2026_11_05_120400_create_medical_record_revisions.php`.
- **Temuan (dua masalah berurutan):**
  1. Trigger menolak `UPDATE` pada kolom klinis ketika `status = 'final'`. `revise()` melakukan `$visit->update($newData)` langsung pada visit final, tanpa mengubah status. Akibatnya revisi selalu gagal jika teksnya berubah.
  2. Bahkan jika trigger tidak ada, `revise()` memasukkan `Crypt::encryptString(...)` ke kolom `previous_snapshot` yang bertipe `json`. Keluaran `encryptString` adalah string base64, bukan JSON valid, sehingga PostgreSQL akan menolak `INSERT`.
- **Mengapa test lolos:** Test berjalan di SQLite, tanpa trigger dan tanpa validasi JSON.
- **Dampak:** Fitur revisi (MTS 5.13, 7.16, 11) tidak bisa dipakai di produksi. Ini juga menunjukkan bahwa test SQLite saja tidak cukup.
- **Rekomendasi:** Revisi harus dilakukan dalam transaksi yang (a) menyalin snapshot ke `medical_record_revisions` dalam bentuk JSON valid, (b) mengubah status ke `draft` secara sah melalui jalur khusus, (c) menerapkan perubahan, dan (d) mengembalikan status ke `final`. Trigger harus mengizinkan jalur ini melalui flag sesi atau kolom `revision_in_progress`. Snapshot harus dienkripsi sebagai kolom `text`, bukan `json`.

### F-08. Password default untuk setiap staf baru
- **Lokasi:** `app/Modules/Staff/Livewire/StaffForm.php` baris 31: `'password' => bcrypt('password')`.
- **Dampak:** Setiap akun staf yang dibuat memiliki password yang sama dan diketahui publik. Ini adalah celah yang langsung bisa dieksploitasi.
- **Rekomendasi:** Hasilkan password acak kuat atau kirim tautan undangan sekali pakai (alur reset password yang belum ada, lihat F-25). Jangan pernah menulis password statis ke kode.

### F-09. Tidak ada batas percobaan untuk kode 2FA
- **Lokasi:** `app/Modules/Auth/Livewire/TwoFactorChallenge.php` (`verify()`).
- **Temuan:** Tidak ada `RateLimiter`, tidak ada lockout, dan tidak ada pencatatan kode yang sudah dipakai (replay). Kode TOTP 6 digit memiliki sekitar 10^6 kemungkinan; dengan sesi pending yang terus hidup, serangan brute force terbuka.
- **Lain-lain:** Ada fallback yang menerima secret dalam bentuk plaintext ("stored plaintext in legacy/dev"), yang tidak seharusnya ada di kode produksi.
- **Rekomendasi:** Terapkan lockout yang sama dengan login (5 kali, 15 menit), simpan `last_totp_counter` untuk mencegah replay, dan hapus fallback plaintext.

### F-10. Batas cabang bisa dilewati lewat properti Livewire
- **Lokasi:** `BookingForm.php` (`public int $branchId = 1`), `GroomingForm.php` (`public int $branchId = 1`), `FinanceForm.php` (`public int $branchId = 1`), `VisitForm.php` (`public int $branchId = 1`), `PosForm.php` (`createOrder(1, ...)`), `PurchaseOrderForm.php` (`$branchId = 1`).
- **Temuan:** `BelongsToBranch` (`app/Scopes/BranchScope.php`) hanya memfilter **pembacaan**. Saat membuat data, tidak ada pemeriksaan bahwa pengguna berhak atas `branch_id` yang dikirim. Properti publik Livewire dapat diubah oleh klien. Sementara itu semua form mengunci cabang ke `1` (cabang Pusat), jadi tidak ada cabang lain yang bisa dipilih secara sah.
- **Dampak:** Pengguna cabang A dapat membuat booking, transaksi keuangan, PO, atau kunjungan medis di cabang B dengan mengubah `branchId`. Ini melanggar MTS 6 dan 11.2 serta Acceptance #18.
- **Rekomendasi:** Jangan terima `branch_id` dari klien. Ambil cabang aktif dari sesi, yang dipilih dari daftar cabang yang diizinkan (`user_branch_access`). Tambahkan validasi di service: `abort_unless(in_array($branchId, $user->branchIds()), 403)`.

### F-11. Dokter bisa membuat rekam medis atas nama dokter lain
- **Lokasi:** `app/Modules/Veterinary/Livewire/VisitForm.php` (`public int $doctorId = 0`), `VeterinaryService::createVisit()`.
- **Temuan:** `doctor_id` dikirim dari klien. Tidak ada pemeriksaan bahwa dokter tersebut adalah pengguna yang sedang login, atau memiliki peran Dokter. `petId` juga tidak diperiksa keberadaan maupun cabangnya.
- **Dampak:** Rekam medis bisa dikaitkan ke dokter yang salah, merusak akuntabilitas (MTS 5.13 dan 11).
- **Rekomendasi:** `doctor_id` selalu `auth()->id()` untuk peran Dokter. Validasi `pet_id` dengan `exists` dan cabang.

### F-12. Pengaturan sistem dapat diubah tanpa izin pengaturan
- **Lokasi:** `app/Modules/SystemSettings/Livewire/SettingsPage.php` (`save()`), `app/Support/SettingsService.php` (`set()`), `routes/web.php` (`/settings` dengan `can:settings.view`).
- **Temuan:**
  - Route hanya memeriksa `settings.view`. Peran Admin memiliki `settings.view` (dari `RoleSeeder`), sedangkan `settings.manage` hanya untuk Super Admin.
  - `SettingsService::set()` tidak memanggil Gate apa pun.
  - Kunci dan nilai diterima bebas tanpa daftar kunci yang diizinkan.
- **Dampak:** Admin bisa mengubah pengaturan apa pun di `system_settings`. Konfigurasi runtime tidak terpengaruh karena (F-30) pengaturan database tidak pernah dibaca.
- **Rekomendasi:** Tambahkan `Gate::authorize('settings.manage')` di `save()`, buat daftar kunci yang valid dengan validasi tipe, dan hubungkan pembacaan pengaturan ke runtime (`SettingsService::get` dipakai oleh tax, loyalty, stock, grooming, dan lockout).

### F-13. Tidak ada CI/CD, Dockerfile produksi, atau deploy yang nyata
- **Lokasi:** Tidak ada `.github/workflows/`, tidak ada `Dockerfile`, tidak ada `compose.prod.yaml`. `scripts/deploy.sh` dan `scripts/rollback.sh`:
  ```
  echo "PETORA deploy skeleton - implement pull, migrate --force, config/cache, health check, rollback hook."
  ```
  Kedua skrip keluar dengan kode 0.
- **Dampak:** Tidak ada jalur deploy. Jika skrip ini dipanggil dari CI kelak, deploy akan "berhasil" padahal tidak melakukan apa pun. `docker-compose.yml` hanya menjalankan `php -v` pada container `php:8.4-cli`.
- **Rekomendasi:** Implementasikan MTS 5.2–5.6 secara lengkap. Sebelum itu, ubah kedua skrip agar gagal (`exit 1`) dengan pesan jelas, supaya tidak ada yang tertipu.

### F-14. Data medis bisa terhapus bersama akun dokter atau cabang
- **Lokasi:** `database/migrations/2026_11_05_082406_create_medical_visits_table.php`.
- **Temuan:** `doctor_id` menggunakan `constrained('users')->cascadeOnDelete()`. `pet_id` dan `branch_id` juga `cascadeOnDelete`. Menghapus pengguna dokter (hard delete) akan menghapus semua kunjungan medisnya.
- **Dampak:** Melanggar MTS 10 dan 11 ("Data medis tidak boleh terhapus ketika akun pelanggan dinonaktifkan", "data transaksi keuangan tidak boleh dihapus permanen"). Ini sekaligus melanggar kewajiban retensi.
- **Rekomendasi:** Ubah ke `restrictOnDelete()` untuk `doctor_id`, `pet_id`, dan `branch_id` pada tabel medis dan keuangan.

---

## 4. Temuan Tinggi (P1)

### F-15. Pengaturan dan konfigurasi bisnis tidak dipakai di runtime
- `SettingsService::get()` hanya dipakai di test. Nilai dari halaman Super Admin tidak mengubah perilaku sistem.
- `config/petora.php` dibaca langsung (`config('petora.stock.allow_negative')`, `config('petora.loyalty.*')`, `config('petora.security.lockout_*')`). Artinya perubahan nilai bisnis membutuhkan deploy, bertentangan dengan MTS 4.3 dan 1.1 prinsip 5.
- Tax (`tax.enabled`) dibaca nol kali. `hotel.checkout_time`, `refund.requires_approval`, `void.requires_approval`, `cash.variance_tolerance`, `security.password_history`, `security.idle_timeout_minutes`, `upload.max_mb`, dan `audit.retention_years` tidak dibaca di mana pun.

### F-16. Kebijakan password dan riwayat password tidak ditegakkan
- Tabel `user_password_histories` dibuat, tetapi tidak ada kode yang menulis atau membacanya.
- Tidak ada validasi minimal 10 karakter atau kombinasi huruf dan angka (MTS D21). Form staf bahkan menetapkan password statis (F-08).

### F-17. Tidak ada alur reset password, registrasi pelanggan, atau lupa kata sandi
- `password_reset_tokens` ada di migrasi, tetapi tidak ada route atau komponen untuk reset, lupa kata sandi, maupun registrasi (MTS 7.29, 11.1).
- Akibatnya pengguna yang lupa password tidak punya jalan keluar selain intervensi manual.

### F-18. Customer tidak punya akun login dan portal tidak ada
- Tabel `customers` tidak memiliki `user_id`. Peran Customer hanya diberi `dashboard.view` (`RoleSeeder`), dan `dashboard.blade.php` tidak menampilkan data apa pun.
- `CustomerPortalList` dan `CustomerPortalService` adalah stub. Tidak ada route customer.
- Dampak: MTS 7.29 (Portal Pelanggan, R1) belum ada. Pemilik hewan tidak bisa melihat booking, riwayat, atau rekam medis yang diizinkan.

### F-19. Audit log kehilangan data
- `AuditService::log()` mengisi `user_agent`, `actor_id`, `auditable_type`, dan `auditable_id` (jika kolom ada). Namun `app/Models/AuditLog.php` hanya memiliki `$fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'old_values', 'new_values', 'ip_address']`. Keempat kolom tadi diabaikan diam-diam oleh mass assignment.
- Akibatnya `user_agent` selalu kosong dan `actor_id`/`auditable_*` selalu `NULL`, padahal kolomnya ada di database.
- Tidak ada perlindungan append-only pada `audit_logs` (MTS 6.7). Baris audit bisa diubah atau dihapus oleh siapa pun yang punya akses database.
- Tidak ada pencatatan akses rekam medis oleh non-dokter penanggung jawab (MTS 11.4).

### F-20. Pengecekan bentrok booking tidak pernah berjalan
- `BookingForm` hanya mengirim `branch_id`, `customer_id`, `pet_id`, `type`, dan `scheduled_at`. Tidak ada `resource_id` dan `ends_at`.
- `BookingService::assertNoConflict()` langsung return jika `resource_id` atau `ends_at` kosong. Maka pengecekan tidak pernah dijalankan.
- Tidak ada exclusion constraint `EXCLUDE USING gist` (MTS 8.3). Dua request bersamaan lolos walaupun ada pengecekan aplikasi.
- `cancel()` menulis alasan pembatalan ke kolom `notes`, sehingga catatan asli hilang (`$updates['notes'] = $reason`).
- Tidak ada mesin status. `confirm()` dan `cancel()` menerima status apa pun.

### F-21. Alur grooming melompati status
- `GroomingSessionService::complete()` mengubah status dari apa pun langsung ke `completed`, termasuk dari `pending` tanpa check-in atau QC.
- Tidak ada checklist, foto sebelum dan sesudah, pemeriksaan alergi (MTS 7.10), atau pembayaran. Tidak ada tabel `grooming_checklists`, `grooming_photos`, atau `grooming_service_prices`.

### F-22. Tidak ada state machine yang ditegakkan
- Order, booking, grooming, PO, transfer, kunjungan medis, dan tutup kas tidak memiliki validasi transisi. Contoh: `PurchasingService::approve()` menyetujui PO apa pun, termasuk yang sudah `received` atau `cancelled`.
- Model menggunakan `$fillable` yang memuat kolom `status`, sehingga `status` bisa diubah lewat mass assignment dari mana pun.
- MTS 9 mensyaratkan transisi yang eksplisit.

### F-23. Pembatalan order dan void tidak lengkap
- `PosService::voidOrder()` memblokir void jika ada pembayaran, tetapi tidak ada alur refund. `Order::STATUS_REFUNDED` hanya konstanta, tidak pernah diset oleh kode. Tidak ada `refunds`, `refund_items`, atau `sales_returns` (MTS 7.8).
- Permission `pos.refund` ada, tetapi tidak dipakai di kode.

### F-24. Tutup kas tidak mencatat selisih
- Tabel `cash_shifts` tidak memiliki kolom `expected_amount`, `counted_amount`, atau `variance`. `closeShift()` hanya menyimpan `closing_amount`. MTS 5.5 mensyaratkan selisih kas.
- `CashShift::$fillable` memuat `opened_by` dan `closed_by`, yang tidak ada di skema. Jika nilai ini diisi, insert akan gagal.
- Tidak ada pemeriksaan bahwa kasir hanya punya satu shift terbuka. `openShift()` boleh dipanggil berkali-kali.
- MTS 8 menyebut tabel `cash_sessions`; kode memakai `cash_shifts`. Ini perbedaan penamaan yang harus diputuskan.

### F-25. Laporan pendapatan salah
- `ReportsService::salesSummary()` menghitung `Order::sum('total')` tanpa memfilter status. Order `void`, `draft`, `cancelled`, dan `refunded` ikut dihitung sebagai pendapatan.
- Hasil di-cast ke `(float)`, bukan dibiarkan sebagai string desimal.
- Tidak ada laporan per produk, per kasir, per metode bayar, atau ekspor (MTS 7.27).

### F-26. Keuangan: hapus permanen dan edit = hapus lalu buat ulang
- `Expense` tidak memakai `SoftDeletes`. `FinanceService::deleteExpense()` melakukan hard delete.
- `FinanceForm::save()` untuk edit memanggil `deleteExpense()` lalu `createExpense()`. Riwayat asli hilang dan ID berubah.
- Kolom `status` ada di `$fillable` tetapi tidak ada di migrasi `expenses`.
- `approveExpense()` hanya menulis audit log. Status tidak berubah, sehingga persetujuan tidak punya efek.
- Ambang persetujuan (MTS 7.24) tidak ada.

### F-27. Loyalty dan promosi tidak tersimpan
- `LoyaltyService::earn()` dan `redeem()` menghitung angka dan menulis audit, tetapi tidak membuat tabel `loyalty_accounts` atau `loyalty_transactions`. Saldo poin tidak pernah tersimpan.
- `PromotionService::applyDiscount()` hanya mengurangi `$total`. Tidak ada tabel `promotions` atau `vouchers`, tidak ada batas pemakaian, tidak ada periode, dan tidak ada pemeriksaan minimal transaksi.
- Permission `loyalty.adjust` dipakai untuk "earn", padahal kasir (yang memproses penjualan) tidak punya permission ini. Artinya poin tidak akan pernah diberikan lewat alur normal.

### F-28. Modul R1 yang hanya berupa daftar atau form tanpa aksi
- **Role & Permission:** `RoleList` hanya menampilkan daftar. Tidak ada create, edit, duplicate, assign permission, atau branch scope (MTS 3.2).
- **User Management:** `UserList` hanya daftar. Tidak ada create, deactivate, atau assign role/cabang (MTS 7.2). Pengguna tidak punya cabang atau peran saat dibuat dari `StaffService`.
- **Branch:** `BranchList` hanya daftar. Tidak ada create, edit, atau assign gudang utama (MTS 7.4).
- **Supplier:** tidak ada route maupun halaman.
- **Dashboard:** stub. Tidak ada KPI, grafik, atau stok menipis (MTS 7.1).
- **Audit:** daftar mentah tanpa filter atau ekspor.

### F-29. Pet hanya memiliki satu pemilik dan tanpa riwayat
- `pets.customer_id` adalah satu kolom. Tidak ada `pet_owners` (MTS 5.4 dan 7.6 mensyaratkan multi-pemilik dengan pemilik utama).
- Tidak ada `pet_photos`, `pet_weight_histories`, `pet_vaccinations`, `pet_notes`. Riwayat vaksin dan pengobatan tidak ada di luar `medical_visits`.
- Tidak ada upload foto (`WithFileUploads` tidak dipakai).

### F-30. Produk tanpa varian, tanpa harga per cabang, dan form yang menghapus data
- Tidak ada `product_variants`, `product_prices`, `product_categories`, atau `suppliers` terhubung ke produk.
- `ProductForm::save()` hanya mengirim `name`, `sku`, dan `sell_price`. `cost_price`, `min_price`, `track_batch`, `track_expiry`, dan `barcode` tidak bisa diisi dari UI.
- Import dan export Excel (MTS 5.6) tidak ada.

### F-31. Farmasi tidak terhubung ke stok
- Tabel `medicines` hanya berisi `name`, `category`, dan `status`. Tidak ada harga, batch, kedaluwarsa, atau relasi ke `stock_balances`.
- `PharmacyService` hanya CRUD. Tidak ada `dispense`, tidak ada `prescriptions`, tidak ada `medical_prescription_items`.
- Permission `pharmacy.dispense` dan `prescriptions.create` tidak dipakai.

### F-32. Rekam medis belum lengkap sesuai MTS 5.13
- Tabel `medical_visits` hanya berisi SOAP (`subjective`, `objective`, `assessment`, `plan`). Tidak ada tanda vital, diagnosis terstruktur, resep, tindakan, vaksinasi, laboratorium, persetujuan tindakan, atau kontrol lanjutan.
- `visible_to_owner` default `true` untuk seluruh kunjungan, berlawanan dengan MTS 7.16 yang menetapkan default `false` untuk catatan internal dokter.
- Tidak ada antrean klinik.

### F-33. Stok: indeks unik tidak mencegah duplikasi
- `stock_balances` memiliki `UNIQUE (warehouse_id, product_id, batch_number)`. Di PostgreSQL, `NULL` dianggap berbeda, sehingga produk tanpa batch bisa memiliki banyak baris saldo untuk gudang dan produk yang sama.
- `StockService::receive()` memakai `firstOrCreate` tanpa lock. Dua penerimaan bersamaan bisa membuat baris duplikat.
- Perhitungan memakai `float` (`(float) $b->quantity`, `min($remaining, ...)`, `$remaining -= $take`), yang rawan error pembulatan pada kuantitas desimal.
- Tidak ada perhitungan `unit_cost` rata-rata tertimbang (MTS 7.9).
- Tidak ada transfer (`stock_transfers`), opname (`stock_opnames`), atau penyesuaian dengan alasan (`stock_adjustments`) yang berfungsi.
- Dokumen pergerakan stok memakai cabang `SYS`, bukan cabang gudang.

### F-34. Uang memakai float di banyak tempat
- `app/Support/Money.php` jatuh ke aritmetika `float` jika ekstensi `bcmath` tidak tersedia. MTS 16.2 melarang float untuk uang.
- Ada 13 parameter `float` yang berkaitan dengan uang (`PosService::addItem`, `pay`, `openShift`, `closeShift`, `PurchasingService::createPo`, `StockService::receive` dan `issue`, `CommissionService::calculate`, `PromotionService::applyDiscount`, `LoyaltyService`).
- Tiga form Livewire memakai `public float` untuk nilai uang (`PurchaseOrderForm::$total`, `ProductForm::$sellPrice`, `FinanceForm::$amount`).
- Hanya 4 file yang memakai `Money::`.
- `brick/money` yang disebut MTS 6.1 tidak terpasang.

### F-35. Keamanan sesi dan header belum dikeraskan
- `.env.example`: `APP_DEBUG=true`, `SESSION_ENCRYPT=false`, `LOG_LEVEL=debug`. Jika file ini disalin ke produksi, risikonya langsung terjadi.
- `SESSION_SECURE_COOKIE` tidak diset. Cookie bisa dikirim lewat HTTP.
- Tidak ada `trustProxies` di `bootstrap/app.php`. Di balik Caddy, IP yang dicatat dan rate limit berdasarkan IP akan salah.
- Tidak ada header keamanan: CSP, HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` (MTS 11.3).
- `public/.htaccess` adalah konfigurasi Apache, sementara MTS 2 menetapkan Nginx.

### F-36. Lockout login mudah dilewati
- Kunci rate limit adalah `identifier|ip`. Penyerang bisa mengganti IP untuk setiap akun, atau mengganti identifier dengan variasi huruf besar-kecil.
- Kolom `users.locked_until` ada, tetapi tidak dibaca atau ditulis. MTS D22 menyebut lockout per akun.
- Login dengan telepon tidak menormalisasi format ke `62xxxxxxxxxx` (MTS 7.5). Akun yang disimpan dengan `0812...` tidak bisa login dengan `62812...`.

### F-37. Otorisasi di level Livewire belum eksplisit
- Tidak ada satu pun komponen Livewire (0 dari 67) yang memanggil `authorize`, `Gate::`, atau `->can(` secara langsung. Semua pemeriksaan bergantung pada middleware route dan `Gate::authorize` di service.
- Setiap aksi Livewire mengirim request ke endpoint `/livewire/update`. Perlu diverifikasi apakah middleware dari route halaman asli ikut dijalankan pada request tersebut. Jika tidak, aksi seperti `delete()` pada `CustomerForm` dan `save()` pada `FinanceForm` bisa dipanggil langsung oleh klien yang punya sesi (service tetap memeriksa Gate, tetapi pemeriksaan cabang tidak ada).
- Perlu verifikasi runtime dengan test yang mengirim payload Livewire langsung.

### F-38. Test suite menyatakan lebih dari yang diuji
- `HotelCrudTest::test_checkout()` hanya memanggil `checkOut()` lalu `assertTrue(true)`. `HotelService::checkIn()` mengembalikan array tetap tanpa menyimpan apa pun, tetapi test memeriksa `status === 'checked_in'`.
- `TwoFactorTest` hanya memeriksa bahwa `generateSecret()` tidak kosong. Tidak ada enrollment, verifikasi, atau challenge.
- `AuthLoginFlowTest::test_login_with_email_works()` tidak melakukan login sama sekali. Ia hanya memeriksa halaman login dan `assertGuest()`.
- `AuthLoginFlowTest::test_rate_limiter_key_is_case_insensitive_shape()` hanya mengakses dashboard. Tidak ada pengujian rate limiter.
- Tujuh tes memakai `assertTrue(true)`. Sekitar 42 file tes hanya memeriksa kode status (`assertOk`/`assertStatus`).
- Tes integritas (trigger PostgreSQL, exclusion constraint, lock) tidak ada, karena semua tes berjalan di SQLite. Ini yang membuat F-07 tidak terdeteksi.
- Tidak ada test browser (Playwright/Dusk), tidak ada matriks RBAC lengkap, tidak ada test konkurensi nyata.
- `tests/Feature/Security/BranchIsolationTest` memakai kolom `timezone` pada `Branch::create()`. Kolom itu tidak ada di tabel `branches`, dan `Branch::$fillable` tidak memuatnya, sehingga nilainya diabaikan diam-diam.
- ADR-0001 menyebut 118 test, sedangkan jumlah fungsi test yang ditemukan adalah 155. Perbedaan ini harus dijelaskan atau dikoreksi.

### F-39. Integrasi pihak ketiga tidak berfungsi
- `MidtransAdapter`, `WhatsAppAdapter`, `SmtpAdapter`: ketiganya mengembalikan `false` untuk `isConfigured()` dan `test()`.
- Kontrak `IntegrationAdapter` hanya memiliki `isConfigured()` dan `test()`. MTS 10 mensyaratkan `send()`, `handleWebhook()`, dan `capabilities()`.
- Tidak ada tabel `integration_providers`, `integration_configurations` (kredensial terenkripsi), atau `integration_logs`.
- Tidak ada route webhook untuk Midtrans.
- `IntegrationList.php` (15 baris) dan `modules/integration/list.blade.php` (32 byte) adalah stub.

### F-40. Notifikasi tidak bisa dikirim dan tidak ada penjadwal
- `NotificationService::send()` hanya membuat baris database. Tidak ada pengiriman email, WhatsApp, atau dalam aplikasi yang berfungsi.
- `send()` memakai permission `notifications.manage_templates`, yang salah untuk aksi kirim.
- `markRead()` tidak memeriksa kepemilikan. Siapa pun yang tahu ID bisa menandai notifikasi orang lain sebagai sudah dibaca.
- `routes/console.php` hanya berisi `inspire`. Tidak ada scheduler untuk pengingat booking, peringatan stok menipis, peringatan kedaluwarsa, atau backup (MTS 13.3).
- Tidak ada retry dengan jeda eksponensial dan pencatatan kegagalan pengiriman khusus notifikasi (MTS 7.28). Tabel `failed_jobs` dari Laravel ada, tetapi tidak ada dasbor atau proses peninjauan.

### F-41. Backup belum ada
- Tidak ada `spatie/laravel-backup` di `composer.lock`. Tidak ada skrip `pg_dump`. `docs/runbook/rollback-restore.md` adalah skeleton dengan bagian TODO.
- Tidak ada enkripsi backup (`BACKUP_GPG_RECIPIENT`), retensi, atau drill restore (MTS 13.4).

### F-42. Pengecekan kesehatan dan monitoring
- `/up` tersedia (default Laravel). `routes/health.php` yang disebut MTS 3 tidak ada, sehingga `/health` readiness dengan token internal tidak ada.
- Tidak ada Sentry, tidak ada metrik, tidak ada halaman System Health (MTS 13.2).

### F-43. Konfigurasi Docker dev mengekspos layanan dengan kredensial lemah
- `docker-compose.yml`: PostgreSQL dengan `POSTGRES_PASSWORD: petora` dan port `5432` terbuka ke host. Redis `6379` terbuka tanpa password.
- Layanan `app` hanya menjalankan `php -v`. Tidak ada cara menjalankan aplikasi lewat Compose.
- Ini aman sebagai contoh dev, tetapi tidak boleh menjadi basis `compose.prod.yaml`.

---

## 5. Temuan Sedang dan Rendah (P2 dan P3)

| ID | Sev. | Temuan | Lokasi |
|---|---|---|---|
| F-44 | P2 | Dua implementasi pembuatan order. `OrderService::createDraft()` memakai `count()+1` dan hardcode `PST`, duplikat dengan `PosService::createOrder()`. Risiko tabrakan `order_number`. | `app/Modules/Pos/Services/OrderService.php` |
| F-45 | P2 | Nomor pelanggan dibuat dari `max(id)+1` (`CUS-000001`). Tidak aman untuk konkurensi dan tidak memakai `DocumentNumberService`. | `CustomerService::create()` |
| F-46 | P2 | `PosForm::mount()` membuat order draft baru setiap kali halaman dibuka. Error ditelan oleh `catch (\Throwable)`, sehingga kegagalan tidak terlihat. Fallback `auth()->id() ?? 1`. | `app/Modules/Pos/Livewire/PosForm.php` |
| F-47 | P2 | `CustomerForm::render()` mengubah properti dari database. Side effect di `render()` adalah pola yang salah. | `CustomerForm.php` |
| F-48 | P2 | `StoreCustomerRequest` tidak dipakai. Ada kode mati lain yang perlu dibersihkan. | `app/Modules/Customers/Requests/` |
| F-49 | P2 | 72 dari 149 permission tidak pernah dipakai, termasuk `daycare.*`, `warehouses.manage`, `pos.approve`, dan banyak lainnya. Sumber kebingungan dan celah bila kelak diberikan tanpa ada fitur. | `PermissionSeeder.php` |
| F-50 | P2 | `Admin` memiliki `roles.create/update/delete/manage` dan `users.manage`. Meski belum ada UI, ini melanggar batas delegasi MTS 6.4 begitu fitur dibuat. | `RoleSeeder.php` |
| F-51 | P2 | Peran Dokter punya `customers.view` dan `pets.view` secara global. MTS 6.4 mensyaratkan pasien yang ditugaskan saja. | `RoleSeeder.php` |
| F-52 | P2 | Tidak ada pemeriksaan bahwa `customer_id`, `pet_id`, `product_id`, `supplier_id`, dan `doctor_id` ada. Input tidak valid menghasilkan error SQL (500). | Semua form |
| F-53 | P2 | `DocumentNumberService` memakai `now()` dengan zona aplikasi (`UTC`). Periode `YYYYMM` berganti pukul 07.00 WIB, bukan 00.00 WIB, sehingga dokumen awal bulan bisa memakai periode bulan sebelumnya. MTS mensyaratkan tampilan WIB. | `app/Support/DocumentNumberService.php`, `.env.example` (`APP_TIMEZONE=UTC`) |
| F-54 | P2 | `BranchScope` mengembalikan query tanpa filter ketika tidak ada pengguna login (misalnya di queue atau console). Ini disengaja untuk job sistem, tetapi tidak didokumentasikan dan mudah salah dipakai. | `app/Scopes/BranchScope.php` |
| F-55 | P2 | `StockMovement` dan `StockBalance` tidak memakai `BelongsToBranch`. Cabang hanya bisa dicari lewat relasi gudang, sehingga laporan stok per cabang rawan bocor lintas cabang. | Model terkait |
| F-56 | P2 | `Customer` dan `Pet` bersifat global (tanpa `branch_id`). Ini sesuai desain MTS, tetapi perlu dicatat karena `Dokter` dapat melihat semua pelanggan. | `customers`, `pets` |
| F-57 | P2 | `composer.json` memakai constraint `"*"` untuk `barryvdh/laravel-dompdf`, `maatwebsite/excel`, `pragmarx/google2fa-laravel`, dan `spatie/laravel-permission`. `composer.lock` mengunci versinya, tetapi `composer update` bisa melompat versi mayor. | `composer.json` |
| F-58 | P2 | `composer.json` dan `package.json` tidak memuat `pestphp/pest`, `larastan/larastan`, `brick/money`, `spatie/laravel-backup`, Alpine.js, Chart.js, atau Heroicons. README tetap menyebut `vendor/bin/pest` dan `vendor/bin/phpstan`. | `composer.json`, `package.json`, `README.md` |
| F-59 | P2 | `vite.config.js` memakai `bunny('Instrument Sans')`. MTS 12.4 menetapkan Plus Jakarta Sans. Fonts dilayani dari aplikasi setelah build, tetapi keputusan ini perlu dicatat di ADR. | `vite.config.js` |
| F-60 | P2 | `CLAUDE.md` dan `AGENTS.md` meminta agent menjalankan `composer require laravel/boost --dev`. Ini menambah dependensi di luar MTS, dan AGENT.md (Bagian 3) melarang dependensi di luar daftar. Konflik instruksi. | `CLAUDE.md`, `AGENTS.md` |
| F-61 | P2 | Tidak ada `Dockerfile`, tidak ada `compose.prod.yaml`, dan tidak ada `Caddyfile`. `docker/` tidak ada. | Root repo |
| F-62 | P2 | Tidak ada `scripts/seed-production.sh` atau `scripts/first-deploy.sh`, sesuai MTS 5.5. | `scripts/` |
| F-63 | P2 | Tidak ada endpoint atau pemrosesan upload file. Foto hewan, lampiran lab, dan dokumen karyawan belum ada. | Seluruh modul |
| F-64 | P2 | `NotificationService` memakai model `Notification` dengan tabel custom bernama `notifications`, yang juga nama default Laravel untuk notifikasi database. Risiko tabrakan saat fitur notifikasi Laravel dipakai. | `app/Models/Notification.php` |
| F-65 | P3 | Teks UI dan placeholder bercampur (`placeholder="Pet ID"`, `"Dokter ID"`, `"Tipe"`, `"Jadwal"`). `resources/lang/id/ui.php` hanya 19 baris, sehingga sebagian besar string UI ditulis langsung di Blade, bertentangan dengan MTS 8. | `resources/views/modules/*` |
| F-66 | P3 | `resources/views/welcome.blade.php` (72 KB) adalah halaman default Laravel. Tidak ada route ke sana, tetapi tetap ikut terbawa. | `resources/views/welcome.blade.php` |
| F-67 | P3 | `Branch` tidak memiliki kolom `timezone`, padahal MTS 5.2 mengharapkan jam operasional dan zona per cabang. `Branch::$fillable` juga tidak memuat `operating_hours`. | `database/migrations/2026_11_05_081940_create_branches_table.php` |
| F-68 | P3 | `CashShift` menyimpan `opened_at` dan `closed_at` dengan `timestamp`, tanpa zona. Konversi ke WIB harus dilakukan di tampilan. Perlu helper yang konsisten. | Model dan view |
| F-69 | P3 | Banyak migrasi memakai guard `Schema::hasColumn`/`hasTable` (idempoten), sehingga status skema tidak bisa dipastikan dari nama file migrasi saja. Perlu skema acuan yang diverifikasi. | Migrasi |
| F-70 | P2 | `RuntimeException` dari service (misalnya `Jadwal bentrok dengan booking lain.`, `Stok tidak mencukupi.`) tidak ditangkap di komponen Livewire seperti `BookingForm::save()`. Pengguna melihat halaman error, bukan pesan validasi. | Komponen Livewire |
| F-71 | P3 | `FinanceForm` dan form lain memakai `wire:model` tanpa `wire:loading`, tanpa state loading atau empty, bertentangan dengan MTS 12.5. | Seluruh form |
| F-72 | P3 | `public/robots.txt` dan `favicon.ico` default. Tidak ada `manifest.json` atau ikon PWA (MTS 12.7). | `public/` |

---

## 6. Analisis per Modul

Status: **Berfungsi** (sesuai MTS untuk ruang lingkup R1), **Sebagian** (logika ada, tetapi tidak lengkap atau tidak aman), **Daftar saja** (hanya tampilan baca), **Stub** (kerangka tanpa logika nyata), **Belum ada**.

| No | Modul (MTS) | Rilis | Status | Bukti utama |
|---|---|---|---|---|
| 1 | Dashboard | R1 | Stub | `dashboard.blade.php` berisi nama pengguna dan tombol logout saja |
| 2 | User Management | R1 | Daftar saja | `UserList` hanya `paginate`; tidak ada create/edit/deactivate di UI (F-28) |
| 3 | Role & Permission | R1 | Daftar saja | `RoleList` hanya menampilkan; tidak ada edit (F-28) |
| 4 | Branch Management | R1 | Daftar saja | `BranchList` hanya menampilkan (F-28, F-67) |
| 5 | Customer Management | R1 | Sebagian | CRUD dasar; tidak ada blacklist, pencarian, riwayat, atau normalisasi telepon (F-45, F-52) |
| 6 | Pet Management | R1 | Sebagian | Satu pemilik, tanpa foto, vaksin, atau riwayat (F-29) |
| 7 | Product, Inventory, Warehouse | R1 | Sebagian | Stok masuk/keluar ada, tetapi tidak terhubung ke penjualan atau pembelian; tanpa varian, transfer, opname (F-04, F-30, F-33) |
| 8 | POS & Sales | R1 | Sebagian (tidak aman) | Order dan pembayaran ada; tanpa stok, tanpa refund, tanpa pajak, harga dari klien (F-04–F-06, F-23, F-24); halaman POS hanya `<h1>POS</h1>` |
| 9 | Purchasing & Supplier | R1 | Sebagian | PO dibuat dengan total manual; tanpa item; `receive` tidak menambah stok (F-04); tanpa supplier UI |
| 10 | Grooming | R1 | Sebagian | Sesi dan status sederhana; tanpa checklist, foto, QC, pembayaran (F-21) |
| 11 | Booking | R1 | Sebagian | Booking tercatat; pengecekan bentrok tidak berjalan (F-20) |
| 12 | Pet Hotel & Daycare | R2 | Stub | `HotelService::checkIn()` mengembalikan array tetap; tidak ada tabel kamar |
| 13 | Transport | R2 | Stub | `TransportService` 26 baris, tanpa tabel |
| 14 | Home Grooming | R2 | Stub | `HomeGroomingService` 26 baris, tanpa tabel |
| 15 | Veterinary Management | R1 | Sebagian | SOAP ada; tanpa antrean, resep, tindakan, vaksin, lab (F-32) |
| 16 | Medical Records | R1 | Sebagian (rusak untuk revisi) | Finalisasi ada; revisi gagal di PostgreSQL (F-07); tanpa export, tanpa visibilitas ke pemilik (F-32) |
| 17 | Pharmacy & Medicine | R1 | Sebagian | CRUD obat tanpa stok, harga, atau resep (F-31) |
| 18 | Membership | R2 | Stub | `MembershipService` mengembalikan array; tanpa tabel |
| 19 | Loyalty | R1/R2 | Stub (tidak tersimpan) | Perhitungan tanpa penyimpanan (F-27) |
| 20 | Promotion | R1/R2 | Stub | `applyDiscount` hanya pengurangan; tanpa tabel promosi (F-27) |
| 21 | Staff Management | R1 | Sebagian (tidak aman) | Password default (F-08); tanpa peran atau cabang saat dibuat (F-28) |
| 22 | Attendance | R2 | Stub | `AttendanceService` 29 baris, tanpa tabel |
| 23 | Commission | R2 | Stub | `CommissionService::calculate()` hanya fungsi aritmetika float |
| 24 | Finance | R1 | Sebagian (berisiko) | Hard delete dan edit = hapus + buat (F-26); approval no-op |
| 25 | Accounting | R3 | Stub | `AccountingService` 24 baris, tanpa tabel |
| 26 | Consignment | R3 | Stub | `ConsignmentService` 26 baris, tanpa tabel |
| 27 | Reports & Analytics | R1 | Sebagian | Ringkasan omzet kini hanya memasukkan order paid/completed dan dibatasi ke cabang aktif; ekspor dan laporan terperinci belum tersedia (F-25) |
| 28 | Notification | R1 | Sebagian | Baris database saja; tanpa pengiriman dan penjadwal (F-40) |
| 29 | Customer Portal | R1 | Stub | Tidak ada login pelanggan, tidak ada route (F-18) |
| 30 | System Settings | R1 | Sebagian (tidak aman) | Tanpa authz (F-12); tidak dibaca runtime (F-15) |
| 31 | Integration Settings | R1 | Stub | Adapter mengembalikan `false` (F-39) |
| 32 | Auth & 2FA | R1 | Sebagian (kritis) | Login ada; enrollment 2FA tidak ada (F-02); brute force 2FA (F-09); tanpa reset (F-17) |
| 33 | Audit | R1 | Daftar saja (data hilang) | Kolom hilang (F-19) |
| 34 | Consignment dan akuntansi (R3) | R3 | Stub | Lihat 25–26 |
| 35 | Verifikasi lintas modul (cabang, RBAC, data scope) | R1 | Sebagian | Scope baca ada; scope tulis tidak (F-10, F-11) |

---

## 7. Audit Database

### 7.1 Kelengkapan skema

- Dari **77** tabel yang didefinisikan MTS 8.2, **25** sudah dibuat (32%). Yang hilang termasuk `product_variants`, `product_prices`, `purchase_order_items`, `goods_receipt_items`, `payment_methods`, `refunds`, `loyalty_*`, `promotions`, `vouchers`, `hotel_*`, `integration_*`, `pet_owners`, `pet_photos`, `grooming_checklists`, `grooming_photos`, `medical_prescriptions`, `employees`, `work_schedules`, `cash_accounts`, `cash_movements`, `chart_of_accounts`, `consignment_*`, `file_attachments`, dan `system_backups`.
- Beberapa nama tabel berbeda dari MTS: `cash_shifts` (MTS: `cash_sessions`), `customers.customer_number` (MTS: `customer_code`), `medical_visits` (MTS: `medical_visits` + banyak tabel pendukung), `notifications` (MTS: `notifications` dengan struktur berbeda). Perbedaan ini harus diputuskan dan didokumentasikan.
- Kolom yang disebut MTS tapi tidak ada: `customers.blacklist_reason`, `customers.loyalty_points`, `products.supplier_id`, `products.weight`, `products.unit`/`base_unit` (ada sebagai `unit`), `orders.service_fee`, `orders.paid_at` (ada, ditambah di 120100), `payments.status`, `payments.payment_no`, `cash_shifts.expected_amount`, `cash_shifts.variance`, `bookings.resource_id` (ada, ditambahkan 120300 tetapi tidak dipakai).

### 7.2 Integritas dan constraint

| Aturan MTS | Implementasi | Status |
|---|---|---|
| `stock_movements` append-only (8.3, 6.3) | Trigger `trg_stock_movements_no_update` (PG saja) | Ada, tetapi tidak diuji di PG |
| Order `paid` immutable (8.3) | Trigger `trg_orders_paid_immutable` | Ada. Kolom diperiksa: `subtotal`, `discount`, `tax`, `total`, `branch_id`, `customer_id`. Belum mencakup `paid_at` yang bisa diubah. |
| Pembayaran `paid` immutable (8.3) | Tidak ada trigger untuk `payments` | Belum ada |
| Kunjungan medis `final` immutable (8.3) | Trigger `trg_medical_visits_final_immutable` | Ada, tetapi menghalangi revisi (F-07) |
| Kas `closed` immutable (8.3) | Trigger `trg_cash_shifts_closed_immutable` | Ada |
| Exclusion constraint booking (8.3) | Tidak ada | Belum ada |
| Exclusion constraint kamar hotel (8.3) | Tidak ada tabel kamar | Belum ada |
| `pg_trgm` untuk pencarian pelanggan (8.3) | Tidak ada extension, tidak ada index trigram | Belum ada |
| Unique parsial `products.barcode` (8.3) | `barcode` unique biasa (`nullable()->unique()`), yang di PG juga mengizinkan banyak `NULL` | Berfungsi, meski tidak eksplisit |
| Soft delete data transaksi keuangan (7.12) | `expenses` tanpa soft delete; `Expense` hard delete | Tidak sesuai (F-26) |
| Foreign key cascade pada data medis (10) | `medical_visits.doctor_id` cascade | Tidak sesuai (F-14) |
| Nomor dokumen unik (7.12) | `order_number`, `booking_number`, `po_number` unique | Berfungsi |
| `document_sequences` dengan lock (6.2) | `lockForUpdate` di PG; SQLite tanpa lock | Berfungsi di PG (perlu verifikasi) |

### 7.3 Masalah desain

- **Pemisahan kolom uang:** `payments.amount`, `orders.total`, `purchase_orders.total` berjenis `DECIMAL(15,2)`. Cukup, tetapi nilai dihitung dari float di service (F-34).
- **`stock_balances` tanpa variant:** saldo per `product_id`, bukan per varian. MTS 7.7 mensyaratkan varian. Jika varian ditunda ke R2, keputusan ini harus dicatat di ADR.
- **`order_items` tanpa referensi layanan:** `order_items` hanya punya `product_id`. Layanan grooming, klinik, dan hotel tidak bisa ditagihkan lewat POS, padahal MTS 5.5 mensyaratkan gabungan produk dan layanan dalam satu order.
- **`bookings` dengan dua kolom waktu:** `scheduled_at` (NOT NULL) dan `starts_at` (nullable). Ini menimbulkan ambiguitas. `BookingService::create()` mengisi `starts_at` dari `scheduled_at`, tetapi `ends_at` tidak pernah diisi dari UI.
- **`medical_visits.visible_to_owner` default `true`:** melawan MTS 7.16.
- **`branch_user` dan `user_branch_access`:** dua tabel relasi cabang-pengguna. `User::branches()` memilih salah satu lewat `Schema::hasTable` setiap pemanggilan (lookup skema di setiap query). Migrasi 120500 menyalin data dari `branch_user`, tetapi tabel lama tidak dihapus. Pilih satu sumber kebenaran.
- **`role_branch_scopes.data_scope` default `own_branch`:** nilai ini tidak ada di MTS 6.3 (`all_branches`, `assigned_branches`, `own`, `assigned_records`, `department`). Tidak ada kode yang membacanya.
- **`notifications` dengan kolom `data` berjenis `text`:** tidak sesuai MTS 7.28 (template, channel, status pengiriman).
- **`audit_logs` tanpa partisi atau retensi:** MTS 13.5 menyebut partisi jika volume besar. Belum ada job pembersihan sesuai retensi 5 tahun.

### 7.4 Migrasi

- Migrasi bersifat idempotent pada beberapa tempat (`if (! Schema::hasColumn(...))`), baik untuk kasus instalasi baru maupun migrasi ulang. Ini baik untuk fase awal.
- Namun banyak migrasi "create table" pada tanggal yang sama (2026-11-05), dan migrasi 120000–120500 memperbaiki tabel yang dibuat pada 081940–094806. Hasilnya adalah sejarah migrasi yang sulit dibaca. Sebelum rilis pertama, pertimbangkan untuk memadatkan migrasi (squash) pada database kosong dan menyimpan catatan di ADR.
- Pola expand–contract (MTS 9) belum diikuti secara konsisten, karena belum ada rilis produksi. Ini akan menjadi masalah setelah rilis pertama.

---

## 8. Audit Keamanan

### 8.1 Autentikasi
- Login memakai `Auth::attempt` dengan rate limit per `identifier|ip` (F-36). Sesi diregenerasi setelah login (`session()->regenerate()`), baik.
- Enrollment 2FA tidak ada (F-02). Verifikasi 2FA tidak punya batas (F-09).
- Tidak ada reset password, lupa kata sandi, atau undangan pengguna (F-17).
- Kebijakan password tidak ditegakkan (F-16).
- Idle timeout 120 menit berasal dari `SESSION_LIFETIME` (sesi Laravel berbasis idle). Nilai ini belum dibaca dari `petora.security.idle_timeout_minutes` sehingga tidak bisa diubah dari Super Admin (MTS D22).

### 8.2 Otorisasi
- Kekuatan: Spatie permission dengan `Gate::before` untuk Super Admin, dan `BranchScope` untuk pembacaan. Route dilindungi `can:`. Test `RbacRouteDenyTest` memeriksa 16 route.
- Kelemahan: Pemeriksaan cabang untuk tulis tidak ada (F-10). Pemeriksaan kepemilikan untuk dokter tidak ada (F-11, F-51). Pemeriksaan di Livewire mengandalkan Gate di service. Pada cabang edit, `PurchaseOrderForm`, `BookingForm`, dan `GroomingForm` memanggil `->update()` langsung pada model tanpa service dan tanpa Gate.
- Admin bisa mengubah pengaturan (F-12).
- Pada cabang edit, `PurchaseOrderForm`, `BookingForm`, dan `GroomingForm` memanggil `->update()` langsung pada model, tanpa service dan tanpa Gate.

### 8.3 Data dan privasi
- Kekuatan: Catatan medis (`subjective`, `objective`, `assessment`, `plan`) dienkripsi dengan cast `encrypted`. Ini sesuai MTS D24 untuk field yang dipakai.
- Kelemahan: Field `diagnosis_text` dan `soap` di MTS tidak ada. Snapshot revisi dienkripsi lalu disimpan ke `json` (F-07). Fallback plaintext di `TwoFactorChallenge` (F-09).
- Tidak ada pencatatan akses rekam medis (F-19).
- Tidak ada mekanisme penghapusan data pribadi sesuai UU PDP (MTS 11.5). Hanya disebut sebagai kebijakan.
- Tidak ada kebijakan privasi, persetujuan pemasaran, atau hak akses/koreksi data di portal (karena portal belum ada).

### 8.4 Input dan output
- Blade meng-escape output secara default. Tidak ada `{!! !!}` untuk data pengguna (`grep` tidak menemukan kasus).
- Tidak ada raw SQL dengan interpolasi. Satu-satunya `whereRaw('1 = 0')` aman.
- Upload file belum ada (F-63), sehingga risiko upload belum relevan, tetapi wajib diimplementasikan sesuai MTS 11 saat fitur dibuat.
- Validasi form tidak lengkap: banyak field ID tidak diperiksa keberadaannya (F-52). `scheduledAt` hanya `required` tanpa tipe tanggal (booking).

### 8.5 Konfigurasi dan rahasia
- `APP_KEY` kosong di `.env.example` (aman). `phpunit.xml` memakai `APP_KEY` tetap untuk test (aman untuk test).
- `.gitignore` mengabaikan `.env`, `.env.production`, `/storage/*.key`, dan `auth.json` (baik).
- `docker-compose.yml` memuat password statis `petora` (F-43). Ini hanya untuk dev, tetapi perlu ditandai.
- `SUPER_ADMIN_PASSWORD` tidak didukung karena tidak ada perintah bootstrap (F-03).
- Tidak ada pemindai secret di CI (karena CI tidak ada).

### 8.6 Header dan transport
- Tidak ada CSP, HSTS, X-Content-Type-Options, Referrer-Policy, Permissions-Policy (F-35).
- `SESSION_SECURE_COOKIE` tidak diset, dan `SESSION_SAME_SITE` default `lax` (baik).
- `trustProxies` tidak dikonfigurasi (F-35).

### 8.7 Ketergantungan
- `composer.lock` memuat versi yang cukup baru (Laravel 13.40.0, Livewire 4.4.8, phpunit 12.5.43, dompdf 3.1.6, spatie/laravel-permission 8.3.2).
- Belum ada `composer audit` atau `npm audit` di CI (karena CI tidak ada).
- Tidak ada Renovate atau Dependabot (MTS 5.7).

---

## 9. Audit Pengujian

### 9.1 Gambaran umum
- 62 file test, 155 fungsi test, ±2.573 baris test.
- Test berjalan di SQLite in-memory (`phpunit.xml`). Tidak ada suite PostgreSQL. Tidak ada test trigger, exclusion constraint, atau lock.
- Tidak ada test browser.
- Tidak ada laporan coverage.
- Tidak ada Pest, Larastan, atau Pint CI.

### 9.2 Kualitas test
- **Vacuous:** 7 `assertTrue(true)`, termasuk di `HotelCrudTest`.
- **Nama tidak sesuai isi:** `AuthLoginFlowTest::test_login_with_email_works()` tidak login. `test_rate_limiter_key_is_case_insensitive_shape()` tidak menguji rate limiter.
- **Hanya kode status:** sekitar 42 file test hanya memeriksa `assertOk`/`assertStatus`. Ini membuktikan halaman bisa diakses, tetapi tidak bahwa data benar.
- **Menguji stub seolah berfungsi:** `HotelCrudTest::test_checkin()` memeriksa array tetap. Test ini akan hijau walaupun tidak ada kamar.
- **Tidak menguji celah yang ditemukan:** tidak ada test untuk F-05 (pembayaran ganda), F-07 (revisi PG), F-08 (password default), F-10 (cabang lintas tulis), F-11 (dokter spoof), F-12 (settings tanpa authz).
- **Float compare:** `PosCrudTest` membandingkan `(float) total` dengan `20000`. Untuk uang sebaiknya membandingkan string desimal.

### 9.3 Perlu dijalankan
1. `composer install` dan `php artisan test` untuk mengonfirmasi jumlah lulus dan gagal.
2. Suite PostgreSQL dengan `DB_CONNECTION=pgsql` untuk menguji trigger dan revisi medis (F-07).
3. Test konkurensi untuk stok (dua POS menjual unit terakhir), booking (dua slot sama), dan nomor dokumen.
4. Test payload Livewire langsung untuk memverifikasi F-37.
5. Laporan coverage (`php artisan test --coverage --min=70`).

---

## 10. Audit Infrastruktur, CI/CD, dan Deployment

| Item MTS | Status |
|---|---|
| Dockerfile multi-stage (composer, node build, php-fpm runtime) | Belum ada |
| Nginx + php-fpm di container | Belum ada |
| Caddy (HTTPS otomatis) | Belum ada |
| `compose.prod.yaml`, `compose.staging.yaml` | Belum ada |
| Workflow CI (lint, analisis statis, test, audit, build image) | Belum ada |
| Workflow deploy staging dan produksi | Belum ada |
| Push image ke GHCR | Belum ada |
| Skrip deploy dengan migrasi, health check, rollback | Skeleton (F-13) |
| Health endpoint `/health` (readiness) | Belum ada (F-42) |
| Backup terjadwal, terenkripsi, dengan retensi | Belum ada (F-41) |
| Monitoring dan alerting | Belum ada (F-42) |
| Scheduler untuk job terjadwal | Belum ada (F-40) |
| Queue worker untuk notifikasi dan laporan | Belum ada (queue `database`, worker tidak didefinisikan) |
| Konfigurasi terpisah per lingkungan | Hanya `.env.example` (F-35) |

Catatan: `docker-compose.yml` ada untuk pengembangan, tetapi tidak ada layanan `web` (Nginx), tidak ada `worker`, dan tidak ada `scheduler`. Untuk produksi, ketiganya wajib ada (MTS 5.6).

---

## 11. Audit Dokumentasi dan Tata Kelola

### 11.1 Dua salinan MTS
- `PETORA_MTS_v2.md` (root) adalah sumber kebenaran menurut `README.md` dan `AGENT.md`.
- `docs/MTS.md` adalah "salinan kerja". Isinya berbeda sekitar 1.500 baris, dan kepalanya menyatakan: "Sumber spesifikasi utama proyek adalah `/PETORA_MTS_v2.md`".
- `AGENT.md` (Bagian 0, aturan 4) memerintahkan agent memperbarui `docs/MTS.md` saat ada perubahan material. Padahal aturan 0 menyatakan `PETORA_MTS_v2.md` adalah satu-satunya sumber kebenaran. **Ini kontradiksi di dalam AGENT.md yang saya tulis sebelumnya.** Perbaikan: satu sumber, `PETORA_MTS_v2.md`; `docs/MTS.md` dihapus atau diganti tautan.

### 11.2 README
- Menyebut "31 modul", padahal MTS menetapkan 35.
- Tabel "Tumpukan Teknologi" menyebut Laravel 11, Livewire 3, dan Tailwind 3.4. Kenyataannya Laravel 13.40, Livewire 4.4, Tailwind 4 (lihat ADR-0001). Deviasi tidak disebutkan di README.
- Menyebut `vendor/bin/pest` dan `vendor/bin/phpstan`, tetapi keduanya tidak terpasang.
- Menyebut `docs/screenshots/*.png`, yang tidak ada.
- Bagian "Mengapa PETORA?" dan "Arsitektur" menyatakan fitur sebagai sudah ada ("Audit & keamanan — 2FA, RBAC, enkripsi data medis, audit log lengkap", "Stok akurat"). Ini tidak sesuai kenyataan (F-02, F-04, F-19).
- Ada karakter rusak di bagian "Mengapa PETORA?" (`---\"`).
- Lisensi: README menyebut MIT, tetapi juga menyatakan "Seluruh hak cipta dilindungi undang-undang" dan "Seluruh hak cipta dimiliki oleh PETORA INDONESIA". Keduanya bisa berdampingan, tetapi sebaiknya dirapikan.
- Tidak ada instruksi bootstrap admin (F-03).

### 11.3 ADR-0001
- Mencatat deviasi stack dan menyatakan remediasi bertahap. Ini langkah yang baik. Namun ADR belum punya keputusan akhir: statusnya "Diterima (sementara, perlu konfirmasi pemilik proyek)".
- Menyebut "118 test existing", sedangkan hitungan fungsi test 155 (F-38). Perlu dikoreksi.
- Tidak mencatat keputusan tentang: Tailwind 4 vs 3.4, Vite 8, font Instrument Sans vs Plus Jakarta Sans, `spatie/laravel-permission` v8, dan penamaan tabel kas.

### 11.4 Instruksi agent
- `CLAUDE.md` dan `AGENTS.md` identik. Keduanya meminta instalasi `laravel/boost` dan membuka tautan instalasi PHP. Ini bertentangan dengan AGENT.md (Bagian 3: tidak boleh menambah dependensi di luar daftar tanpa ADR) (F-60).
- Dua instruksi agent yang berbeda dalam satu repo menciptakan risiko agent mengikuti instruksi yang salah. Satukan.

### 11.5 Runbook
- `docs/runbook/deploy-staging.md` dan `rollback-restore.md` berstatus "Skeleton" dengan bagian TODO. Belum operasional.

---

## 12. Dependensi dan Stack

| Komponen | MTS | Terpasang | Catatan |
|---|---|---|---|
| PHP | 8.3 | ^8.3 (runtime 8.4.27 menurut ADR) | Baik |
| Laravel | 11 | ^13.17 (lock 13.40.0) | Deviasi besar; disetujui sementara di ADR |
| Livewire | 3 | ^4.4 (lock 4.4.8) | Deviasi; API Livewire 4 berbeda (perlu dicek sintaks) |
| Tailwind | 3.4 | ^4.0 | Deviasi; konfigurasi CSS berbeda |
| Vite | — | ^8.0 | Tidak di MTS, aman |
| PostgreSQL | 16 | Dev: SQLite; prod: belum dikonfigurasi | Perlu suite PG |
| Redis | 7 | Tidak dipakai (`cache`, `queue`, `session` = `database`) | Belum sesuai MTS D6 |
| Caddy/Nginx | ya | Tidak ada | Belum ada (F-61) |
| spatie/laravel-permission | ya | 8.3.2 | Baik |
| pragmarx/google2fa-laravel | ya | 3.0.1 | Baik; enrollment belum dibuat |
| dompdf | ya | 3.1.6 | Terpasang, tidak dipakai (tidak ada cetak struk) |
| maatwebsite/excel | ya | 4.0.6 | Terpasang, tidak dipakai (tidak ada import/export) |
| brick/money | ya (MTS 6.1) | Tidak ada | Belum sesuai |
| spatie/laravel-backup | ya (MTS 3) | Tidak ada | Belum sesuai |
| Pest | ya | Tidak ada | Belum sesuai |
| Larastan | ya | Tidak ada | Belum sesuai |
| Pint | ya | ^1.27 (dev) | Ada |
| Alpine.js | ya | Tidak ada | Belum sesuai |
| Chart.js | ya | Tidak ada | Belum sesuai |
| Heroicons | ya | Tidak ada | Belum sesuai |

Keputusan stack adalah keputusan terbesar yang tertunda. Jika tim ingin tetap di Laravel 13 / Livewire 4 / Tailwind 4, MTS dan ADR harus diperbarui. Jika tim ingin kembali ke MTS, downgrade bisa berisiko karena banyak kode sudah ditulis untuk API baru. Keputusan ini perlu dibuat sebelum fitur baru ditambahkan.

---

## 13. Kekuatan yang Perlu Dipertahankan

Audit ini juga menemukan hal yang sudah benar:

1. **RBAC berbasis `spatie/laravel-permission`** dengan `Gate::before` untuk Super Admin dan daftar permission yang konsisten dengan route (semua permission di route dan `Gate::authorize` terdaftar di seeder).
2. **`BranchScope` sebagai global scope** dengan bypass `branches.view_all` dan fail-safe `1 = 0` jika pengguna tidak punya cabang.
3. **`DocumentNumberService`** dengan `lockForUpdate` dan format `TIPE/CABANG/YYYYMM/SEQ` sesuai MTS D9.
4. **Trigger PostgreSQL** untuk `stock_movements` (append-only), `orders` lunas (immutable), `cash_shifts` tutup (immutable), dan `medical_visits` final (immutable). Ide yang tepat, meski perlu diperbaiki (F-07).
5. **Enkripsi `encrypted` cast** pada catatan medis.
6. **`RecordFinder`** yang mengembalikan 404 untuk data di luar scope, sesuai MTS 11.2 untuk mencegah enumerasi.
7. **Test isolasi cabang dan RBAC** sudah ada sebagai dasar (`tests/Feature/Security/`), meski perlu diperluas.
8. **`config/petora.php`** sudah memuat nilai default sesuai MTS 4.3.
9. **Penamaan permission** konsisten (`modul.aksi`) dan mengikuti MTS.
10. **Transparansi ADR-0001** tentang deviasi stack.

---

## 14. Rencana Remediasi (Berurutan)

Urutan disusun agar setiap tahap menghasilkan sistem yang lebih aman dan lebih dapat dipakai, serta sesuai dengan fase di MTS 15.2.

### Tahap 0 — Hentikan kerusakan (1–2 minggu, sebelum fitur baru)
Tujuan: menutup celah yang bisa dieksploitasi dan membuka kunci administrator.
- F-02 Implementasi enrollment 2FA lengkap, termasuk kode cadangan dan tes end-to-end.
- F-03 Perintah `petora:bootstrap-admin` dan pembaruan `README`.
- F-08 Hapus password statis; kirim undangan atau password acak.
- F-09 Lockout dan replay protection untuk 2FA; hapus fallback plaintext.
- F-10 & F-11 Hapus `branch_id` dan `doctor_id` dari input klien; validasi akses cabang di service.
- F-12 `settings.manage` dan whitelist kunci.
- F-05 Validasi pembayaran (status, jumlah, idempotensi unik).
- F-19 Perbaiki `AuditLog::$fillable` dan tambahkan trigger append-only pada `audit_logs`.
- F-35 Konfigurasi `.env.production.example`, `SESSION_SECURE_COOKIE`, `trustProxies`, header keamanan.
- F-13 Ubah `scripts/deploy.sh` dan `rollback.sh` agar gagal secara eksplisit sampai diimplementasikan.
- F-14 Ganti `cascadeOnDelete` pada tabel medis dan keuangan.

### Tahap 1 — Fondasi yang dapat dipakai (2–3 minggu)
- F-01 Layout utama dengan Tailwind, Alpine, navigasi, pemilih cabang, dan notifikasi.
- F-28 CRUD lengkap untuk pengguna, peran (termasuk custom), dan cabang, dengan batas delegasi Admin.
- F-17 Reset password, lupa kata sandi, dan undangan pengguna.
- F-16 Kebijakan password dan riwayat password.
- F-15 Sambungkan `SettingsService` ke runtime untuk pajak, poin, stok, dan lockout.
- F-37 Verifikasi dan tutup celah Livewire dengan test payload langsung.
- F-38 Perbaiki test yang vacuous dan tambahkan suite PostgreSQL di CI.

### Tahap 2 — Inti petshop (3–4 minggu)
- F-04 Hubungkan penjualan dan pembelian ke stok (`sale_out`, `purchase_in`) dalam transaksi.
- F-06 Harga dari katalog, diskon dengan `min_price`, pajak dari konfigurasi.
- F-30 Produk: varian (atau keputusan tertunda), harga per cabang, supplier, import/export.
- F-33 Perbaiki `stock_balances` (unique dengan `COALESCE`), `Money` berbasis `brick/money`, dan transfer dan opname.
- F-23 Refund dengan persetujuan, retur, dan void yang lengkap.
- F-24 Tutup kas dengan selisih; satu shift terbuka per kasir.
- F-22 Mesin status untuk order dan PO.
- F-52 Validasi keberadaan ID di semua form.

### Tahap 3 — Layanan dan klinik (3–4 minggu)
- F-20 Booking: `resource_id`, `ends_at`, exclusion constraint PostgreSQL, dan kalender.
- F-21 Grooming: state machine, checklist, foto, QC, pembayaran.
- F-07 Perbaiki revisi rekam medis di PostgreSQL dan tambahkan test PG.
- F-32 Rekam medis lengkap: tanda vital, resep, vaksin, tindakan, visibilitas.
- F-31 Farmasi terhubung ke stok dan resep.
- F-29 Pet: multi-pemilik, foto, riwayat berat, vaksin.
- F-18 Customer login dan portal (booking, riwayat, rekam medis yang diizinkan).
- F-40 Notifikasi: pengiriman email (SMTP), penjadwal, dan retry.

### Tahap 4 — Keuangan, laporan, dan pengetahuan (2–3 minggu)
- F-25 Laporan yang benar (filter status, desimal, per cabang, ekspor).
- F-26 Keuangan: soft delete, approval, tanpa edit destruktif.
- F-27 Loyalty dan promosi dengan tabel dan test.
- F-43 Ubah Compose dev dan buat `compose.prod.yaml` dengan secret dari lingkungan.

### Tahap 5 — Deployment dan operasi (2–3 minggu)
- F-13 Implementasi deploy dan rollback nyata (MTS 5.2–5.6).
- F-61 Dockerfile multi-stage, Nginx, Caddy, `compose.prod.yaml`, `compose.staging.yaml`.
- Workflow CI lengkap (Pint, PHPStan, Pest, audit, build image, pemindai secret).
- F-41 Backup terenkripsi, retensi, dan drill restore.
- F-42 `/health`, Sentry, dan dashboard System Health.
- Smoke test Playwright untuk alur kritis (MTS 14.3).

### Tahap 6 — Rilis 2 dan 3
- Hotel, daycare, transport, home grooming, membership, attendance, komisi, akuntansi, konsinyasi, dan integrasi tambahan.
- Hanya setelah Rilis 1 stabil di staging.

### Tahap 0–5 paralel: tata kelola
- Satukan MTS (F-11.1), perbaiki README (11.2), perbarui ADR-0001 dengan keputusan final stack (Bagian 12), satukan instruksi agent (11.4), dan tulis ADR untuk setiap keputusan penamaan tabel.

---

## 15. Keputusan yang Perlu Dibuat Pemilik Proyek

Berikut keputusan yang tidak bisa diputuskan dari kode saja:

1. **Stack:** tetap di Laravel 13 / Livewire 4 / Tailwind 4 (dan perbarui MTS), atau kembali ke spesifikasi MTS (Laravel 11 / Livewire 3 / Tailwind 3.4)? Rekomendasi auditor: tetap di versi yang sudah terpasang, dan perbarui MTS, karena kode sudah dibangun di atasnya dan downgrade berisiko tinggi.
2. **Varian produk:** diterapkan di Rilis 1 atau ditunda ke Rilis 2? Rekomendasi: ditunda, tetapi `product_variant_id` disiapkan di skema.
3. **Nama tabel kas:** `cash_shifts` (kode) atau `cash_sessions` (MTS)? Rekomendasi: `cash_shifts`, perbarui MTS.
4. **Visibilitas rekam medis:** default `visible_to_owner` untuk seluruh kunjungan atau per item? Rekomendasi: per item (MTS 7.16).
5. **2FA untuk pelanggan:** opsional (MTS D20) atau wajib untuk akses rekam medis? Rekomendasi: opsional, dengan OTP WhatsApp di Rilis 2.
6. **Pajak aktif sejak hari pertama:** MTS mengatakan nonaktif secara default. Konfirmasi dengan akuntan.
7. **Multi-cabang di Rilis 1:** cukup satu cabang aktif, atau perlu verifikasi dengan dua cabang di staging? Rekomendasi: uji dengan dua cabang sebelum produksi (MTS 15.4).
8. **Integrasi pembayaran:** Midtrans di Rilis 1 atau Rilis 2? Rekomendasi: Rilis 2, karena pembayaran kasir bisa tunai dan QRIS manual.

---

## 16. Keterbatasan Audit

- Tidak ada eksekusi kode: PHP, Composer, dan akses jaringan tidak tersedia. Test suite, `composer audit`, dan analisis statis tidak dijalankan.
- Temuan yang bergantung perilaku runtime (F-01 soal Livewire auto-inject, F-37 middleware Livewire, F-07 perilaku PostgreSQL) ditandai untuk verifikasi.
- Tidak ada akses ke database PostgreSQL, sehingga efek trigger dan konkurensi hanya dinilai dari kode.
- Tidak ada akses ke riwayat git (folder `.git` tidak ada di arsip). Analisis perubahan dari waktu ke waktu tidak dilakukan.
- Penilaian keamanan bersifat statis. Penetration testing belum dilakukan.
- Tidak ada wawancara dengan pemilik produk untuk memastikan maksud di balik beberapa keputusan (misalnya alasan `visible_to_owner = true` default).

---

## Lampiran A. Daftar Stub (Service dan Komponen)

**Service stub murni** (mengembalikan data tetap atau hanya memanggil audit):
`AccountingService`, `AttendanceService`, `ConsignmentService`, `CustomerPortalService`, `HomeGroomingService`, `HotelService`, `MembershipService`, `TransportService`.

**Service parsial** (perhitungan tanpa penyimpanan):
`CommissionService` (hanya `calculate`), `PromotionService` (hanya `applyDiscount`), `LoyaltyService` (`earn`, `redeem` tanpa tabel), `IntegrationService` (hanya memanggil adapter).

**Komponen Livewire daftar saja (≤16 baris):** 44 komponen, termasuk `UserList`, `RoleList`, `BranchList`, `AuditLogList`, `CustomerList`, `ProductList`, `PetList`, `StockList`, `PharmacyList`, `PurchaseOrderList`, `BookingList`, `GroomingList`, `VisitList`, `NotificationList`, dan semua `*Index` di modul R2/R3.

**View stub (≤40 byte):** 36 view, termasuk `hotel/*`, `loyalty/*`, `membership/*`, `transport/*`, `consignment/*`, `accounting/*`, `attendance/*`, `commission/*`, `integration/*`, `customer-portal/*`, `home-grooming/*`, `promotion/*`, `staff/index`, `booking/list`, `finance/list`, `grooming/list`, `pharmacy/list`, `reports/list`, `veterinary/list`, `notification/*`, `auth/two-factor`, dan lainnya.

## Lampiran B. Permission yang Tidak Dipakai

Dari 149 permission di `PermissionSeeder`, 72 tidak ditemukan sebagai string literal di `app/` maupun `routes/`. Pencarian ini berbasis string literal, sehingga permission yang dibangun secara dinamis bisa terlewat. Contoh yang terverifikasi tidak dipakai: seluruh `daycare.*`, `warehouses.manage`, `pos.approve`, `pos.discount`, `products.import`, `customers.export`, `customers.blacklist`, `medical_records.export`, `pharmacy.dispense`, `prescriptions.create`, `inventory.opname`, `inventory.transfer`, `finance.reconcile`, `reports.export`, dan `backup.manage`.

Daftar lengkap dapat dihasilkan ulang dengan skrip pencarian string literal pada `PermissionSeeder.php` terhadap `app/` dan `routes/`.

## Lampiran C. Indeks Temuan

| ID | Sev. | Judul ringkas |
|---|---|---|
| F-01 | P0 | UI tidak dapat digunakan (layout tanpa aset dan navigasi) |
| F-02 | P0 | Admin dan Super Admin terkunci karena 2FA tidak bisa diaktifkan |
| F-03 | P0 | Tidak ada cara membuat Super Admin pertama |
| F-04 | P0 | Penjualan dan pembelian tidak menyentuh stok |
| F-05 | P0 | Pembayaran bisa diulang, negatif, dan berlebih |
| F-06 | P0 | Harga dan item dari klien; pajak dan diskon tidak ada |
| F-07 | P0 | Revisi rekam medis gagal di PostgreSQL |
| F-08 | P0 | Password default untuk staf baru |
| F-09 | P0 | Tidak ada batas percobaan 2FA |
| F-10 | P0 | Batas cabang dapat dilewati di form tulis |
| F-11 | P0 | Dokter bisa membuat rekam medis atas nama dokter lain |
| F-12 | P0 | Pengaturan sistem dapat diubah tanpa izin pengaturan |
| F-13 | P0 | Tidak ada CI/CD dan deploy yang nyata |
| F-14 | P0 | Data medis dan keuangan bisa terhapus lewat cascade |
| F-15 | P1 | Pengaturan database tidak dibaca runtime |
| F-16 | P1 | Kebijakan password dan riwayat tidak ditegakkan |
| F-17 | P1 | Reset password dan registrasi tidak ada |
| F-18 | P1 | Portal pelanggan dan akun pelanggan tidak ada |
| F-19 | P1 | Audit log kehilangan kolom dan tidak append-only |
| F-20 | P1 | Pengecekan bentrok booking tidak berjalan |
| F-21 | P1 | Alur grooming melompati status |
| F-22 | P1 | Tidak ada state machine yang ditegakkan |
| F-23 | P1 | Refund dan retur tidak ada |
| F-24 | P1 | Tutup kas tanpa selisih; shift ganda diizinkan |
| F-25 | P1 | Laporan pendapatan salah (termasuk order void) |
| F-26 | P1 | Keuangan: hard delete dan edit destruktif |
| F-27 | P1 | Loyalty dan promosi tidak tersimpan |
| F-28 | P1 | Modul pengguna, peran, cabang, dashboard hanya daftar atau stub |
| F-29 | P1 | Pet hanya satu pemilik; tanpa foto dan riwayat |
| F-30 | P1 | Produk tanpa varian, harga cabang, dan import |
| F-31 | P1 | Farmasi tidak terhubung ke stok dan resep |
| F-32 | P1 | Rekam medis tidak lengkap |
| F-33 | P1 | Stok: duplikasi NULL batch, float, tanpa transfer dan opname |
| F-34 | P1 | Uang memakai float |
| F-35 | P1 | Keamanan sesi dan header belum dikeraskan |
| F-36 | P1 | Lockout login mudah dilewati; telepon tidak dinormalisasi |
| F-37 | P1 | Otorisasi Livewire belum eksplisit (perlu verifikasi) |
| F-38 | P1 | Test menyatakan lebih dari yang diuji |
| F-39 | P1 | Integrasi pihak ketiga tidak berfungsi |
| F-40 | P1 | Notifikasi tidak terkirim; tidak ada penjadwal |
| F-41 | P1 | Backup belum ada |
| F-42 | P1 | `/health` dan monitoring belum ada |
| F-43 | P1 | Compose dev mengekspos layanan dengan kredensial lemah |
| F-44–F-72 | P2–P3 | Lihat Bagian 5 |
