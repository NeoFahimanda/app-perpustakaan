# Skenario Demo Aplikasi Perpustakaan

## Persiapan

1. Pastikan migrasi dan data demo sudah tersedia (`php artisan migrate --seed`).
2. Jalankan server web pada port `8000` dan server API internal pada port
   `8011` di dua terminal terpisah.
3. Siapkan Postman dengan base URL `http://127.0.0.1:8000/api` dan header
   `Accept: application/json`.

## Alur demo

1. **Login sebagai admin**
   - Buka `/login`.
   - Login dengan `admin@pens.ac.id` / `password`.
   - Tunjukkan dashboard, identitas pengguna, dan menu Kategori.
2. **Dashboard**
   - Tunjukkan jumlah buku, anggota, dan peminjaman aktif.
   - Terangkan bahwa angka diambil dari `GET /api/stats`.
3. **Tambah kategori**
   - Buka Kategori, buat kategori baru, lalu tunjukkan kategori di daftar.
4. **Tambah buku**
   - Buat buku dengan kategori yang baru dibuat dan tunjukkan hasil di daftar.
5. **Tambah anggota**
   - Buat anggota, lalu tunjukkan nama dan data anggota di daftar.
6. **Buat peminjaman**
   - Pilih anggota dan satu atau lebih buku.
   - Tanggal dan petugas diisi/dicatat, petugas otomatis mengikuti akun yang
     sedang login.
7. **Daftar peminjaman aktif**
   - Tunjukkan anggota, petugas, buku, status peminjaman, dan badge status.
8. **Pengembalian**
   - Klik **Kembalikan** pada transaksi aktif.
   - Tunjukkan status berubah menjadi `Dikembalikan` dan tombol hilang.
9. **Laporan**
   - Buka menu Laporan.
   - Tunjukkan transaksi yang disajikan dari `GET /api/loans` dan navigasi
     halaman laporan.
10. **Demo REST API di Postman**
    - `GET /api/books?judul=...` untuk daftar/filter buku.
    - `GET /api/members` untuk daftar anggota.
    - `GET /api/loans` untuk daftar peminjaman.
    - `GET /api/stats` untuk statistik.
    - Jika waktu cukup, buat buku/transaksi API pada database demo dan tunjukkan
      status `201`, lalu tunjukkan `422` untuk input yang tidak valid.
11. **Hak akses petugas (opsional)**
    - Login sebagai `petugas1@pens.ac.id` / `password`.
    - Tunjukkan Kategori tersembunyi dan akses langsung ke `/categories`
      ditolak.

## Catatan operasional

Dashboard dan Laporan memakai HTTP client untuk memanggil server kedua pada
port `8011`. Jika server kedua tidak aktif, kedua halaman menampilkan pesan
API tidak tersedia dan fallback, bukan error 500.
