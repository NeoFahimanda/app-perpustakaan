# Perpustakaan REST API

Base URL saat menjalankan `php artisan serve`: `http://127.0.0.1:8000/api`.
Endpoint API pada materi ini bersifat publik dan tidak memakai session login.
Kirim header `Accept: application/json`; untuk body JSON gunakan
`Content-Type: application/json`.

Daftar dan collection memakai pagination 10 item per halaman dengan struktur
`data`, `links`, dan `meta`. Contoh response memakai data ilustratif; bentuk
JSON dan field-nya mengikuti response yang diverifikasi oleh tes fitur API.
Timestamp aktual mengikuti waktu data dibuat.

## Endpoints

| Method | URL | Body / query | Response sukses |
|---|---|---|---|
| GET | `/api/books` | Opsional `judul`, `category_id`, `page` | `200`, paginated `BookResource` |
| GET | `/api/books/{id}` | - | `200`, satu `BookResource` |
| POST | `/api/books` | JSON buku | `201`, `BookResource` |
| PUT | `/api/books/{id}` | JSON buku lengkap | `200`, `BookResource` |
| DELETE | `/api/books/{id}` | - | `200`, pesan penghapusan |
| GET | `/api/members` | Opsional `page` | `200`, paginated `MemberResource` |
| GET | `/api/members/{id}` | - | `200`, satu `MemberResource` |
| GET | `/api/loans` | Opsional `page` | `200`, paginated `LoanResource` |
| POST | `/api/loans` | JSON transaksi dan `book_ids` | `201`, `LoanResource` |
| PUT | `/api/loans/{id}/kembalikan` | - | `200`, `LoanResource` setelah pengembalian |
| GET | `/api/stats` | - | `200`, ringkasan statistik |

Semua endpoint resource mengembalikan data dalam properti `data`. Endpoint
penghapusan buku dan statistik mengembalikan JSON langsung.

## Contoh request dan response

### Books

`GET /api/books?judul=laskar&category_id=1`

```json
{
  "data": [
    {
      "id": 1,
      "judul": "Laskar Pelangi",
      "penulis": "Andrea Hirata",
      "penerbit": "Bentang Pustaka",
      "tahun_terbit": 2005,
      "isbn": "9780000000001",
      "stok": 3,
      "sampul": null,
      "category": {
        "id": 1,
        "nama_kategori": "Fiksi"
      },
      "created_at": "2026-11-19T08:00:00.000000Z",
      "updated_at": "2026-11-19T08:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/books?page=1",
    "last": "http://127.0.0.1:8000/api/books?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "page": null,
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/books?page=1",
        "label": "1",
        "page": 1,
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "page": null,
        "active": false
      }
    ],
    "path": "http://127.0.0.1:8000/api/books",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

`GET /api/books/1` mengembalikan satu buku dengan format resource yang sama:

```json
{
  "data": {
    "id": 1,
    "judul": "Laskar Pelangi",
    "penulis": "Andrea Hirata",
    "penerbit": "Bentang Pustaka",
    "tahun_terbit": 2005,
    "isbn": "9780000000001",
    "stok": 3,
    "sampul": null,
    "category": {
      "id": 1,
      "nama_kategori": "Fiksi"
    },
    "created_at": "2026-11-19T08:00:00.000000Z",
    "updated_at": "2026-11-19T08:00:00.000000Z"
  }
}
```

Body `POST /api/books` dan `PUT /api/books/{id}`:

```json
{
  "judul": "Laskar Pelangi",
  "penulis": "Andrea Hirata",
  "penerbit": "Bentang Pustaka",
  "tahun_terbit": 2005,
  "isbn": "9780000000001",
  "stok": 3,
  "category_id": 1
}
```

POST sukses memberi status `201` dan resource buku. PUT sukses memberi status
`200` dan resource buku yang telah diperbarui. Body yang tidak valid memberi
status `422`, misalnya:

```json
{
  "message": "The judul field is required.",
  "errors": {
    "judul": ["The judul field is required."],
    "penulis": ["The penulis field is required."]
  }
}
```

`DELETE /api/books/1`:

```json
{
  "message": "Buku berhasil dihapus."
}
```

Sukses `200`; ID buku yang tidak ditemukan memberi `404`.

### Members

`GET /api/members` mengembalikan daftar paginated `MemberResource`, dan
`GET /api/members/1` mengembalikan satu resource:

```json
{
  "data": {
    "id": 1,
    "nama": "Mei Kinoshita",
    "nim": "2345234523",
    "email": "mei@example.test",
    "nomor_telepon": "08123456789",
    "alamat": "Surabaya",
    "status": "aktif",
    "created_at": "2026-11-19T08:00:00.000000Z",
    "updated_at": "2026-11-19T08:00:00.000000Z"
  }
}
```

`GET /api/members` mengembalikan format pagination yang sama (`data`, `links`,
`meta`), dengan setiap item menggunakan struktur MemberResource di atas.
Kedua endpoint sukses memberi status `200`; ID yang tidak ada memberi `404`.

### Loans

`GET /api/loans` mengembalikan daftar paginated dengan relasi anggota, petugas,
dan buku:

```json
{
  "data": [
    {
      "id": 1,
      "member": {
        "id": 1,
        "nama": "Mei Kinoshita"
      },
      "petugas": {
        "id": 1,
        "name": "Petugas Satu"
      },
      "tanggal_pinjam": "2026-11-19",
      "tanggal_kembali": "2026-11-26",
      "tanggal_dikembalikan": null,
      "status": "dipinjam",
      "books": [
        {
          "id": 1,
          "judul": "Laskar Pelangi"
        }
      ]
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/loans?page=1",
    "last": "http://127.0.0.1:8000/api/loans?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "page": null,
        "active": false
      },
      {
        "url": "http://127.0.0.1:8000/api/loans?page=1",
        "label": "1",
        "page": 1,
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "page": null,
        "active": false
      }
    ],
    "path": "http://127.0.0.1:8000/api/loans",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

Body `POST /api/loans`:

```json
{
  "member_id": 1,
  "user_id": 1,
  "tanggal_pinjam": "2026-11-19",
  "tanggal_kembali": "2026-11-26",
  "book_ids": [1]
}
```

Sukses memberi status `201` beserta transaksi dan relasi, misalnya:

```json
{
  "data": {
    "id": 1,
    "member": {
      "id": 1,
      "nama": "Mei Kinoshita"
    },
    "petugas": {
      "id": 1,
      "name": "Petugas Satu"
    },
    "tanggal_pinjam": "2026-11-19",
    "tanggal_kembali": "2026-11-26",
    "tanggal_dikembalikan": null,
    "status": "dipinjam",
    "books": [
      {
        "id": 1,
        "judul": "Laskar Pelangi"
      }
    ]
  }
}
```

Request tidak valid memberi `422` dengan properti `errors` per field.

`PUT /api/loans/1/kembalikan` tidak memerlukan body. Response sukses (status
`200`) memiliki resource yang sama dengan `status: "dikembalikan"` dan
`tanggal_dikembalikan` berisi tanggal hari ini, misalnya:

```json
{
  "data": {
    "id": 1,
    "member": {
      "id": 1,
      "nama": "Mei Kinoshita"
    },
    "petugas": {
      "id": 1,
      "name": "Petugas Satu"
    },
    "tanggal_pinjam": "2026-11-19",
    "tanggal_kembali": "2026-11-26",
    "tanggal_dikembalikan": "2026-11-19",
    "status": "dikembalikan",
    "books": [
      {
        "id": 1,
        "judul": "Laskar Pelangi"
      }
    ]
  }
}
```

### Stats

`GET /api/stats` mengembalikan status `200` dan jumlah aktual:

```json
{
  "total_buku": 1,
  "total_anggota": 1,
  "peminjaman_aktif": 1
}
```

## Status code umum

| Status | Arti |
|---|---|
| `200` | GET, PUT, DELETE, atau pengembalian berhasil |
| `201` | Buku atau transaksi peminjaman berhasil dibuat |
| `404` | Resource dengan ID tersebut tidak ditemukan |
| `422` | Input gagal validasi; response berisi `message` dan `errors` |
