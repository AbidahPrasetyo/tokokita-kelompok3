# TokoKita — Aplikasi Toko Daring

Mini Proyek Aplikasi Web Berbasis Basis Data Relasional (**Kasus 3: TokoKita**) untuk mata kuliah **Basis Data Lanjut** (Semester Ganjil 2026/2027).

Aplikasi ini dibangun menggunakan **Laravel** dan **MySQL/MariaDB** dengan basis data `db_tokodaring`. TokoKita memiliki dua sisi utama:
- **Etalase (Publik):** Katalog produk, filter kategori & rentang harga, keranjang belanja berbasis sesi, serta proses *checkout* dalam satu transaksi basis data.
- **Panel Admin:** Dashboard analitik penjualan (kartu ringkasan & grafik), manajemen produk, pesanan (master-detail & perubahan status), serta pelanggan.

---

## Anggota Kelompok 3

| No | Nama Lengkap | NIM | Peran / Modul |
| :-: | :--- | :--- | :--- |
| 1 | *Abidah Azka Zaen Prasetyo* | *25091397018* | Setup Proyek, Database SQL, & Sisi Etalase Publik (`3a`, Keranjang, Checkout) |
| 2 | *Farahdiba' Indirasanti Saputra* | *25091397008* | Panel Admin: Produk (`3e`), Pesanan (`3b`, `3j`), & Pelanggan (`3d`, `3f`, `3i`) |
| 3 | *Ashilah Zahirah Haq* | *25091397033* | Dashboard Analitik (`3c`, `3g`, `3h`), Pengujian |

---

## Teknologi yang Digunakan

- **Backend:** PHP 8.2+ & Laravel
- **Basis Data:** MySQL / MariaDB (`db_tokodaring`, InnoDB)
- **Frontend:** Blade Template, HTML, CSS (Bootstrap / Tailwind CSS), JavaScript, dan Chart.js
- **Akses Data:** SQL eksplisit (*raw query*) menggunakan `Illuminate\Support\Facades\DB` dengan *prepared statement* (*parameter binding* `?`) tanpa *subquery*
- **Version Control:** Git & GitHub

---

## Prasyarat Sistem

Pastikan perangkat sudah terpasang:
- PHP `>= 8.2`
- Composer
- MySQL / MariaDB (misalnya melalui **Laragon** atau **XAMPP**)
- Git

---

## Cara Instalasi & Menjalankan dari Nol

Ikuti langkah berikut secara berurutan agar aplikasi berjalan dengan benar:

### 1. Clone Repositori
```bash
git clone https://github.com/USERNAME_KAMU/tokokita-kelompok3.git
cd tokokita-kelompok3
```
*(Ganti URL di atas dengan tautan repositori GitHub proyek ini.)*

### 2. Import Basis Data `db_tokodaring`
Buka MySQL client (HeidiSQL, phpMyAdmin, atau terminal MySQL), lalu jalankan berkas skrip SQL berikut secara berurutan:
1. `database/sql/01_schema_dan_data.sql` — membuat basis data `db_tokodaring`, 4 tabel inti (`pelanggan`, `produk`, `pesanan`, `detail_pesanan`), beserta data awal dari dokumen latihan.
2. `database/sql/02_tambahan.sql` *(jika tersedia)* — membuat objek pendukung tambahan (seperti tabel `users` atau `VIEW`) tanpa mengubah struktur tabel inti.

> **Penting:** Jangan menjalankan `php artisan migrate` agar struktur basis data `db_tokodaring` tetap bersih sesuai skema dokumen latihan.

### 3. Install Dependensi Laravel
```bash
composer install
```

### 4. Konfigurasi File `.env`
Salin berkas `.env.example` menjadi `.env`:
- **Git Bash / macOS / Linux:**
  ```bash
  cp .env.example .env
  ```
- **Windows Command Prompt / PowerShell:**
  ```powershell
  copy .env.example .env
  ```

Buka file `.env`, lalu sesuaikan kredensial MySQL lokal Anda:
```env
APP_NAME="TokoKita"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_tokodaring
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file

# Mode demo menggunakan tanggal acuan 2026-11-13
APP_DEMO_MODE=true
TANGGAL_ACUAN=2026-11-13
```
*(Jika MySQL lokal Anda menggunakan password untuk user `root`, isikan pada `DB_PASSWORD`.)*

### 5. Generate Application Key & Bersihkan Cache
```bash
php artisan key:generate
php artisan config:clear
```

### 6. Jalankan Aplikasi
```bash
php artisan serve
```
Buka peramban web (*browser*) dan akses:
- **Etalase Publik:** `http://127.0.0.1:8000`
- **Panel Admin:** `http://127.0.0.1:8000/admin`
- **Endpoint Tes Koneksi Awal:** `http://127.0.0.1:8000/tes-etalase`

---

## Mode Demo vs Mode Produksi (`TANGGAL_ACUAN`)

Aplikasi menyediakan konfigurasi tanggal acuan di `.env` dan `config/tokokita.php`:
- **Mode Demo (`APP_DEMO_MODE=true`):** Menggunakan nilai `TANGGAL_ACUAN=2026-11-13` untuk konsistensi pengujian data awal.
- **Mode Produksi (`APP_DEMO_MODE=false`):** Menggunakan tanggal sistem terkini (`CURDATE()`).

---

## Pemetaan Fitur terhadap Soal Latihan SQL (`3a`–`3j`)

Seluruh query laporan ditulis sebagai **SQL eksplisit tanpa subquery**, dilengkapi komentar kode soal (misalnya `-- Soal 3h`), dan diuji menggunakan data awal:

| Kode Soal | Halaman / Fitur | Konsep SQL |
| :-: | :--- | :--- |
| **3a** | Etalase: Filter katalog berdasarkan kategori dan rentang harga | `BETWEEN`, `AND` |
| **3b** | Admin Pesanan: Daftar pesanan beserta total nilai transaksi | `SUM(jumlah * harga_satuan)` |
| **3c** | Dashboard Admin: 5 produk terlaris berdasarkan unit terjual | `SUM`, `ORDER BY`, `LIMIT` |
| **3d** | Dashboard & Pelanggan: Pelanggan prioritas (belanja 2026 > Rp10.000.000) | `JOIN` 3 tabel, `HAVING` |
| **3e** | Dashboard & Produk: Penanda produk yang belum pernah dipesan | `LEFT JOIN` + `IS NULL` |
| **3f** | Admin Pelanggan: Jumlah pelanggan per kota | `COUNT`, `GROUP BY` |
| **3g** | Dashboard Admin: Pendapatan per kategori (khusus status `selesai`) | Seleksi status + agregasi |
| **3h** | Dashboard Admin: Grafik pendapatan bulanan tahun 2026 (tanpa `batal`) | `MONTH()`, `<>` |
| **3i** | Dashboard & Pelanggan: Pelanggan yang belum pernah memesan (target promo) | `LEFT JOIN` + `IS NULL` |
| **3j** | Admin Detail Pesanan: Jumlah jenis produk pada pesanan (> 3 jenis) | `COUNT`, `HAVING` |

---

## Aturan Bisnis & Standar Keamanan

1. **Harga Historis:** Seluruh perhitungan nilai pesanan dan pendapatan menggunakan `detail_pesanan.harga_satuan`, bukan `produk.harga`. Saat *checkout*, `harga_satuan` diisi otomatis dari harga produk terkini.
2. **Transaksi Checkout Atomik:** Proses `INSERT pesanan`, `INSERT detail_pesanan`, dan `UPDATE produk` (pengurangan stok) dibungkus dalam satu transaksi (`DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`).
3. **Siklus Status Pesanan:** Perubahan status mengikuti alur `diproses` -> `dikirim` -> `selesai`, atau `diproses` -> `batal`. Pesanan berstatus `selesai` atau `batal` tidak dapat diubah lagi.
4. **Integritas Referensial:** Produk yang sudah memiliki riwayat pesanan tidak dapat dihapus dan ditangani dengan pesan galat yang ramah.
5. **Keamanan Query & Kredensial:** Seluruh input pengguna menggunakan *prepared statement* (*parameter binding*), dan file `.env` dikecualikan dari repositori melalui `.gitignore`.
6. **Format Indonesia:** Tanggal diformat `13 Nov 2026` / `13/11/2026` dan mata uang diformat `Rp7.650.000` menggunakan helper global `app/Helpers/FormatHelper.php`.

---

## Struktur Folder Utama

```text
tokokita/
├── app/
│   ├── Helpers/
│   │   └── FormatHelper.php       <- Helper format Rupiah & tanggal Indonesia
│   ├── Http/Controllers/          <- Controller Etalase & Admin
│   └── Models/ (atau Repositories/) <- Seluruh query raw SQL (berkomentar kode soal)
├── config/
│   └── tokokita.php               <- Konfigurasi mode demo & TANGGAL_ACUAN
├── database/
│   └── sql/
│       ├── 01_schema_dan_data.sql <- Skrip skema & data awal db_tokodaring
│       └── 02_tambahan.sql        <- Skrip tambahan (opsional)
├── resources/views/               <- Template Blade (layouts, etalase, admin)
├── routes/web.php                 <- Definisi seluruh rute aplikasi
├── .env.example                   <- Template variabel environment tanpa kredensial
└── README.md                      <- Panduan instalasi dan dokumentasi proyek
```

---

## Panduan Kolaborasi Tim (Git)

- Setiap anggota wajib bekerja di *branch* masing-masing (misalnya `fitur-etalase`, `fitur-admin`, `fitur-dashboard`) dan menggabungkannya ke `main` melalui **Pull Request**.
- Setiap anggota wajib memiliki minimal **1 commit per minggu** menggunakan akun GitHub masing-masing.
- Selalu jalankan `git pull origin main` sebelum mulai mengerjakan fitur baru agar kode tetap sinkron.