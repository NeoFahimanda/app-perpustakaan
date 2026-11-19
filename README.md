# Aplikasi Perpustakaan

Aplikasi manajemen perpustakaan berbasis Laravel 12 dengan autentikasi admin
dan petugas, CRUD buku/anggota/peminjaman, REST API, dashboard, dan laporan.

## Persyaratan

- PHP 8.2 atau lebih baru beserta ekstensi Laravel yang diperlukan
- Composer
- Database SQLite (default) atau MySQL

## Instalasi

```bash
git clone --branch dev https://github.com/NeoFahimanda/app-perpustakaan.git
cd app-perpustakaan
composer install
```

Di PowerShell, salin konfigurasi dan buat application key:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

`.env.example` menggunakan SQLite (`DB_CONNECTION=sqlite`) dan telah
mengonfigurasi locale Faker Indonesia. Jika file database belum tersedia,
buat file `database/database.sqlite` sebelum migrasi.

Pastikan variabel berikut ada di `.env`:

```dotenv
APP_URL=http://127.0.0.1:8000
APP_FAKER_LOCALE=id_ID
INTERNAL_API_URL=http://127.0.0.1:8011
```

Untuk memakai MySQL, sesuaikan `DB_CONNECTION`, host, nama database, username,
dan password di `.env`, lalu buat database tersebut sebelum migrasi.

Jalankan migrasi dan isi data demo:

```bash
php artisan migrate --seed
```

Untuk membuat ulang database demo dari nol, gunakan
`php artisan migrate:fresh --seed`. **Perintah ini menghapus semua tabel dan
data yang ada**, jadi jangan jalankan pada database penting.

### Akun demo

Semua akun demo memiliki password `password`:

| Role | Email |
|---|---|
| Admin | `admin@pens.ac.id` |
| Petugas | `petugas1@pens.ac.id` |
| Petugas | `petugas2@pens.ac.id` |

## Menjalankan aplikasi

Dashboard dan laporan mengambil data melalui HTTP dari API internal. Jalankan
dua server pada **terminal terpisah** dari direktori repository:

Terminal pertama — aplikasi web:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal kedua — menerima request API internal:

```bash
php artisan serve --host=127.0.0.1 --port=8011
```

Buka `http://127.0.0.1:8000` dan login dengan akun demo. Jika port API diubah,
perbarui `INTERNAL_API_URL` di `.env` agar menunjuk ke port server kedua.
Tanpa server kedua, dashboard dan laporan tetap terbuka dengan pesan bahwa API
tidak tersedia dan menampilkan data fallback.

## REST API

API bersifat publik sesuai cakupan praktikum dan memakai prefix `/api`:

- `GET /api/books` — daftar buku, filter `judul` dan `category_id`
- `GET /api/books/{id}` — detail buku
- `POST /api/books` — tambah buku
- `PUT /api/books/{id}` — perbarui buku
- `DELETE /api/books/{id}` — hapus buku
- `GET /api/members` dan `GET /api/members/{id}` — baca anggota
- `GET /api/loans` — daftar peminjaman
- `POST /api/loans` — buat transaksi
- `PUT /api/loans/{id}/kembalikan` — catat pengembalian
- `GET /api/stats` — statistik perpustakaan

Lihat [API.md](./API.md) untuk body request dan contoh response.

## Pengujian

```bash
php artisan test
```
