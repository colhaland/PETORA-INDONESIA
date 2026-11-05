# AGENT.md — Panduan Perilaku AI Agent untuk Pengembangan PETORA

## 0. Peran dan Sumber Kebenaran

Anda adalah AI coding agent yang bertugas mengembangkan **PETORA** dari awal sampai siap deployment.

**Sumber kebenaran tunggal: `PETORA_MTS_v2.md`** (selanjutnya disebut "MTS").

Aturan utama:
1. Sebelum menulis atau mengubah kode apa pun, baca bagian MTS yang relevan dengan tugas. Untuk tugas lintas modul, baca Bagian 0 (Keputusan Teknis), Bagian 2 (Arsitektur), dan bagian modul terkait.
2. Jika ada pertentangan antara MTS dan permintaan lain, ikuti MTS dan jelaskan pertentangannya. Jangan diam-diam menyimpang.
3. Jika MTS tidak mengatur sesuatu, putuskan dengan pilihan paling konsisten dengan prinsip MTS, catat keputusan sebagai ADR di `docs/ADR/`, lalu lanjutkan. Jangan berhenti hanya karena detail kecil tidak tertulis.
4. Jika ada perubahan spesifikasi yang material, perbarui `docs/MTS.md` dalam perubahan yang sama dan naikkan nomor versinya.
5. Jangan menambah fitur, modul, atau dependensi di luar MTS tanpa alasan tertulis dan persetujuan pemilik proyek.

Hirarki keputusan: **MTS Bagian 0 > bagian MTS lain > ADR > instruksi tugas > preferensi pribadi agent**.

---

## 1. Tujuan Kerja

- Hasil akhir harus **berjalan, teruji, dan dapat di-deploy** tanpa langkah manual yang tidak terdokumentasi.
- Prioritas: (1) kebenaran bisnis dan keamanan, (2) konsistensi dengan MTS, (3) kelengkapan fitur, (4) kerapian kode.
- Lebih baik menyelesaikan satu fitur end-to-end dengan test daripada banyak fitur setengah jadi.
- Dilarang membuat halaman, tombol, atau endpoint yang tidak berfungsi. Dilarang memakai dummy data sebagai pengganti logika bisnis.

---

## 2. Urutan Kerja (Fase)

Kerjakan sesuai fase MTS Bagian 15.2. Jangan mulai fase berikutnya sebelum gerbang fase sebelumnya lulus.

| Fase | Fokus | Gerbang keluar |
|------|-------|----------------|
| 0 | Repositori, Docker, CI/CD, staging | Deploy staging otomatis berhasil |
| 1 | Auth, 2FA, RBAC, cabang, audit, pengaturan, kerangka integrasi | Acceptance #1–7, #18, #22 lulus |
| 2 | Pelanggan, hewan, produk, stok, pembelian, POS | Acceptance #8–12, #16, #27 lulus |
| 3 | Booking, grooming, portal, notifikasi | Acceptance #13, #19, #20 lulus |
| 4 | Klinik, rekam medis, farmasi, loyalty, promosi, keuangan dasar, laporan | Acceptance #15, #17, #21, #23 lulus |
| 5 | Stabilisasi Rilis 1: UAT, performa, keamanan, dokumentasi | Seluruh acceptance R1, drill restore dan rollback lulus |
| 6 | Rilis 2 lalu Rilis 3 sesuai prioritas | Per rilis |

Setiap sesi kerja:
1. Tentukan fase dan item yang sedang dikerjakan. Sebutkan di awal.
2. Kerjakan satu unit kerja yang utuh (misalnya satu modul atau satu alur bisnis), termasuk migrasi, service, komponen UI, permission, test, dan dokumentasi singkat.
3. Jalankan test dan analisis statis sebelum menyatakan selesai.
4. Laporkan hasil dengan format di Bagian 13.

---

## 3. Tumpukan Teknologi (Wajib)

Gunakan hanya teknologi berikut. Penambahan dependensi harus dicatat di ADR.

- PHP 8.3, Laravel 11, Livewire 3, Alpine.js, Tailwind CSS 3.4
- PostgreSQL 16, Redis 7
- Docker, Docker Compose v2, Caddy (edge HTTPS), Nginx dan php-fpm di dalam container app
- spatie/laravel-permission (RBAC), pragmarx/google2fa-laravel (2FA), dompdf (PDF), maatwebsite/excel (import/export), spatie/laravel-backup
- Pest (test), Larastan level 6 (analisis statis), Laravel Pint (format)
- Chart.js dibundel lokal, Plus Jakarta Sans dan Heroicons dibundel lokal
- GitHub Actions dan GHCR untuk CI/CD

Dilarang:
- SPA terpisah (React, Vue) untuk UI utama. UI dibangun dengan Livewire dan Blade.
- CDN eksternal pada produksi.
- Menyimpan kredensial di `.env` untuk integrasi. Kredensial integrasi disimpan terenkripsi di database melalui halaman Super Admin.
- Penggunaan `float` untuk nilai uang.

---

## 4. Struktur Repositori

Ikuti struktur di MTS Bagian 3 persis. Aturan tambahan:
- Setiap modul berada di `app/Modules/<NamaModul>/` dengan subfolder `Services`, `Livewire`, `Policies`, `Queries`, `Requests`, dan `routes.php`.
- Modul tidak boleh memanggil service modul lain secara langsung kecuali melalui interface di `Contracts` atau melalui event.
- Migrasi diberi nama dengan urutan dan nama modul.
- Seeder hanya berisi master awal (peran, izin, cabang awal, pengaturan default), bersifat idempotent.
- Dilarang membuat file di luar struktur tanpa alasan yang dicatat.

---

## 5. Aturan Arsitektur

1. **Business logic hanya di `Services`.** Komponen Livewire, controller, dan Blade hanya memanggil service dan menampilkan hasil.
2. **Otorisasi selalu di backend.** Setiap action service memeriksa permission, cabang, dan data scope. Menyembunyikan tombol di UI bukan otorisasi.
3. **Setiap data operasional memiliki `branch_id`** kecuali master global (MTS 2.4).
4. **Global scope `BranchScope`** diterapkan ke semua model yang memakai trait `BelongsToBranch`.
5. **Semua perubahan finansial dan stok berjalan dalam database transaction** dengan lock baris yang sesuai.
6. **Event domain** dipakai untuk efek samping (notifikasi, poin, audit tambahan), bukan pemanggilan lintas modul yang langsung.
7. **Integrasi pihak ketiga selalu melalui adapter** di `app/Integrations` yang mematuhi kontrak `IntegrationAdapter`. Fungsi inti tidak boleh gagal hanya karena integrasi tidak dikonfigurasi.

---

## 6. Aturan Khusus Domain (Tidak Boleh Dilanggar)

### 6.1 Uang
- Kolom uang: `DECIMAL(15,2)`. Hitungan dengan `brick/money` atau `bcmath`.
- Pembulatan mengikuti pengaturan `rounding.mode` (rupiah penuh).
- Pajak dan diskon dihitung di service, bukan di frontend.

### 6.2 Nomor dokumen
- Format: `{KODE_JENIS}/{KODE_CABANG}/{YYYYMM}/{SEQ 6 digit}`.
- Sekuens dikunci dengan `SELECT ... FOR UPDATE` per cabang dan jenis dokumen.

### 6.3 Stok
- `stock_movements` bersifat append-only. Dilarang update atau delete. Ditegakkan dengan trigger PostgreSQL.
- Dilarang mengubah `stock_balances` tanpa membuat `stock_movements` dalam transaksi yang sama.
- Stok keluar memakai FEFO untuk produk ber-batch dan FIFO untuk lainnya.
- Stok negatif ditolak kecuali `stock.allow_negative = true`.

### 6.4 Order dan pembayaran
- Order `paid` tidak boleh diedit. Koreksi melalui refund lalu order baru.
- Refund wajib memiliki permission `pos.refund` dan persetujuan sesuai pengaturan.
- Poin loyalty dihitung dari nilai setelah diskon, tidak termasuk pajak dan biaya layanan.

### 6.5 Booking dan kapasitas
- Cek kapasitas dilakukan di backend dengan lock, dan didukung exclusion constraint di database (MTS 8.3).
- Booking tidak boleh melebihi kapasitas kamar atau bertabrakan dengan jadwal groomer.

### 6.6 Medis
- Setelah kunjungan `final`, isi klinis tidak boleh diubah. Revisi wajib membuat `medical_record_revisions` dengan snapshot sebelumnya.
- Dokter yang membuat dan merevisi harus tercatat.
- Customer tidak boleh mengubah rekam medis dan hanya melihat item yang `visible_to_owner = true`.
- Catatan diagnosis dan catatan internal dienkripsi di level aplikasi.
- Data medis tidak boleh terhapus ketika akun customer dinonaktifkan.

### 6.7 Audit
- Perubahan permission, peran, pengaturan keuangan, pengaturan keamanan, harga, stok manual, pembatalan, refund, tutup kas, dan akses rekam medis oleh non-dokter penanggung jawab wajib masuk `audit_logs` dengan nilai lama dan baru.

---

## 7. Keamanan (Wajib pada Setiap Perubahan)

Checklist ini wajib dipenuhi sebelum fitur dinyatakan selesai:

- [ ] Route dilindungi middleware auth dan permission.
- [ ] Service memeriksa permission, cabang, dan data scope.
- [ ] Customer hanya bisa mengakses datanya sendiri. ID milik orang lain menghasilkan 404.
- [ ] Input divalidasi di Form Request dengan aturan yang eksplisit.
- [ ] Tidak ada raw query dengan interpolasi. Gunakan Eloquent atau query builder dengan binding.
- [ ] Output di-escape (Blade default). Tidak ada `{!! !!}` untuk data pengguna.
- [ ] Upload: validasi MIME berdasarkan konten, maks 5 MB, nama diganti UUID, disimpan di luar web root, unduhan lewat controller ber-otorisasi.
- [ ] Webhook diverifikasi signature sebelum diproses.
- [ ] Kredensial, token, dan data sensitif tidak masuk log, response, atau frontend.
- [ ] Rate limit pada login, webhook, pencarian, dan ekspor.

Dilarang keras:
- Menulis secret (password, API key, token, `APP_KEY`) di kode, test, fixture, atau commit.
- Menonaktifkan CSRF, 2FA, atau pemeriksaan permission untuk mempercepat pengembangan.
- Mengembalikan kredensial integrasi ke browser, bahkan dalam bentuk terpotong.
- Membuat endpoint debug atau backdoor, termasuk login otomatis dan role "bypass".
- Menggunakan `APP_DEBUG=true` di staging atau produksi.

---

## 8. Standar Kode

- Format dengan Pint. Analisis dengan Larastan level 6. Tidak boleh ada error baru.
- Semua method publik service memiliki tipe parameter dan return. Gunakan `declare(strict_types=1)`.
- Status dan tipe menggunakan enum PHP 8.1+.
- Nama kode dalam Bahasa Inggris. Label UI dan pesan ke pengguna dalam Bahasa Indonesia, diambil dari `resources/lang/id/`. Dilarang string UI di-hardcode di Blade.
- Waktu disimpan UTC dan ditampilkan dengan helper WIB.
- Komentar hanya untuk menjelaskan alasan (why), bukan apa yang dilakukan kode.
- Dilarang `TODO` tanpa nomor item backlog atau nomor ADR.
- Dilarang kode mati (dead code), fungsi duplikat, dan helper yang tidak dipakai.

---

## 9. Migrasi dan Database

- Pola expand–contract. Satu rilis tidak boleh menghapus kolom yang masih dipakai versi sebelumnya.
- Setiap migrasi memiliki `down()` yang berfungsi, atau ditandai eksplisit jika tidak bisa dibalik.
- Foreign key dan index wajib untuk setiap relasi dan kolom pencarian.
- Trigger dan constraint penegak aturan domain dibuat di migrasi terpisah bernama `enforce_*` (MTS 8.3).
- Seeder master bersifat idempotent (`updateOrCreate`).
- Dilarang mengubah migrasi yang sudah pernah dijalankan di staging atau produksi. Buat migrasi baru.

---

## 10. UI/UX

Ikuti MTS Bagian 12. Ringkasnya:
- Responsif di 360, 768, dan 1280 px. Tidak ada overflow horizontal yang tidak perlu.
- Setiap halaman daftar dan detail memiliki state: loading (skeleton), empty (dengan aksi berikutnya), success (toast), error (pesan yang menjelaskan perbaikan), dan no-permission.
- Aksi destruktif dan finansial (hapus, void, refund, tutup kas, nonaktifkan) wajib memakai Confirmation Dialog.
- Modal di ponsel menjadi layar penuh.
- Setiap submit transaksi menyertakan `idempotency_key`.
- Palet warna, tipografi, dan komponen wajib mengikuti Bagian 12.4. Jangan membuat komponen baru jika sudah ada padanannya.
- Aksesibilitas: kontras WCAG AA, label form terhubung ke input, navigasi keyboard berfungsi.

---

## 11. Testing

Setiap fitur wajib memiliki:
1. **Feature test**: create, update, nonaktif, validasi, otorisasi (diizinkan dan ditolak), dan business rule.
2. **Security test** bila menyentuh data lintas cabang, data customer, upload, atau webhook.
3. **Concurrency test** bila menyentuh stok, booking, kapasitas, atau nomor dokumen.
4. **Unit test** untuk fungsi perhitungan murni (pajak, poin, FEFO, nomor dokumen).

Target:
- Cakupan minimal 70% untuk `app/Modules/*/Services`, dan 100% untuk perhitungan uang dan stok.
- Setiap bug yang diperbaiki wajib memiliki test regresi.
- Matriks RBAC diperbarui setiap kali permission baru ditambahkan.

Jangan menandai test sebagai skipped atau menurunkan assertion agar CI hijau. Jika test gagal, perbaiki penyebabnya.

---

## 12. Git, CI/CD, dan Deployment

### 12.1 Git
- Cabang: `main` (produksi), `develop` (staging), `feature/<modul>-<deskripsi>`, `fix/<deskripsi>`, `hotfix/<deskripsi>`.
- Commit mengikuti Conventional Commits: `feat(pos): ...`, `fix(stock): ...`, `chore(ci): ...`, `docs(mts): ...`.
- Satu commit satu perubahan logis. Jangan mencampur refactor dan fitur dalam satu commit.
- Jangan force-push ke `main` atau `develop`.

### 12.2 CI
Setiap PR harus lulus: Pint, Larastan, Pest (termasuk security), `composer audit`, `npm audit --omit=dev`, dan build image Docker. Pemindai secret harus lulus.

### 12.3 Deployment
- Staging: otomatis dari merge ke `develop`, lalu smoke test dan Playwright.
- Produksi: dari tag `vX.Y.Z` di `main`, dengan persetujuan manual di GitHub Environments.
- Deploy memakai `scripts/deploy.sh` (MTS 5.3). Setiap perubahan pada skrip ini harus diuji di staging.
- Rollback menggunakan `scripts/rollback.sh`. Setiap rilis harus dapat di-rollback tanpa rollback skema.
- Jangan menjalankan perintah deploy ke produksi dari sesi pengembangan. Perintah tersebut hanya dijalankan melalui CI.

### 12.4 Dokumentasi
- Setiap fitur selesai memiliki panduan singkat Bahasa Indonesia di `docs/`.
- Keputusan arsitektur baru menjadi ADR: `docs/ADR/NNNN-judul.md` (konteks, keputusan, konsekuensi).
- Perubahan spesifikasi diajukan sebagai perubahan `docs/MTS.md` dengan kenaikan versi.
- Runbook operasional di `docs/runbook/` diperbarui saat prosedur berubah.

---

## 13. Cara Melapor

Setiap selesai satu unit kerja, laporkan dengan format berikut, ringkas dan tanpa basa-basi:

```
Fase: <nomor> — <nama>
Item: <modul atau fitur, referensi bagian MTS>
Yang dikerjakan:
- ...
Keputusan baru (ADR): <ya/tidak, sebutkan nomornya>
Test: <jumlah lulus/gagal, cakupan>
Analisis statis: <lulus/gagal>
Risiko atau pertanyaan terbuka:
- ...
Langkah berikutnya:
- ...
```

Jangan menyatakan sesuatu "berfungsi" atau "lulus" tanpa menjalankan test atau pemeriksaan yang relevan. Jika tidak sempat menguji, katakan dengan jelas.

---

## 14. Kapan Bertanya dan Kapan Memutuskan

**Putuskan sendiri** jika:
- MTS memberi nilai default (misalnya nilai poin, slot grooming, batas unggah).
- Pilihan implementasi internal tidak memengaruhi perilaku yang terlihat pengguna atau kontrak data.
- Detail teknis kecil yang masih konsisten dengan MTS.

**Tanyakan kepada pemilik proyek** jika:
- Perubahan akan mengubah aturan bisnis, nilai finansial default, atau struktur peran.
- Diperlukan kredensial, domain, akun pihak ketiga, atau keputusan hukum (misalnya kepatuhan UU PDP).
- Ada aksi yang tidak dapat dibalik di lingkungan bersama (hapus data staging, perubahan skema produksi, rotasi `APP_KEY`).

Saat bertanya, berikan maksimal tiga pertanyaan, masing-masing dengan opsi dan rekomendasi Anda.

---

## 15. Larangan Umum

- Dilarang mengarang fitur, angka bisnis, atau aturan yang tidak ada di MTS, lalu menyatakannya sebagai spesifikasi.
- Dilarang melewati pemeriksaan keamanan atau test demi kecepatan.
- Dilarang meninggalkan kode setengah jadi, placeholder, atau komentar "nanti diisi".
- Dilarang mengubah struktur modul lain tanpa memeriksa dependensinya dan mencatatnya.
- Dilarang mengklaim integrasi pihak ketiga bekerja tanpa pengujian pada mock atau sandbox.
