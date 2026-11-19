<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_endpoints_support_pagination_filters_crud_validation_and_not_found(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category, [
            'judul' => 'Laskar Pelangi',
            'isbn' => '9780000000001',
        ]);
        $otherBook = $this->createBook($category, [
            'judul' => 'Buku Lain',
            'isbn' => '9780000000002',
        ]);

        $this->getJson('/api/books?judul=laskar&category_id='.$category->id)
            ->assertOk()
            ->assertJsonPath('data.0.id', $book->id)
            ->assertJsonPath('data.0.category.nama_kategori', 'Fiksi')
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->getJson('/api/books?judul=tidak-ada')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/books/'.$book->id)
            ->assertOk()
            ->assertJsonPath('data.judul', 'Laskar Pelangi');

        $this->getJson('/api/books/99999')->assertNotFound();

        $newBook = [
            'judul' => 'Buku API',
            'penulis' => 'Penulis API',
            'penerbit' => 'Penerbit API',
            'tahun_terbit' => 2024,
            'isbn' => '9780000000003',
            'stok' => 3,
            'category_id' => $category->id,
        ];

        $this->postJson('/api/books', $newBook)
            ->assertCreated()
            ->assertJsonPath('data.judul', 'Buku API')
            ->assertJsonPath('data.category.id', $category->id);

        $this->postJson('/api/books', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['judul', 'penulis', 'category_id']);

        $this->putJson('/api/books/'.$otherBook->id, [
            'judul' => 'Buku Diperbarui',
            'penulis' => 'Penulis API',
            'penerbit' => 'Penerbit API',
            'tahun_terbit' => 2024,
            'isbn' => null,
            'stok' => 4,
            'category_id' => $category->id,
        ])->assertOk()
            ->assertJsonPath('data.judul', 'Buku Diperbarui');

        $this->deleteJson('/api/books/'.$otherBook->id)
            ->assertOk()
            ->assertExactJson(['message' => 'Buku berhasil dihapus.']);

        $this->deleteJson('/api/books/99999')->assertNotFound();
    }

    public function test_member_endpoints_return_paginated_list_and_single_resource(): void
    {
        $members = collect();
        for ($i = 1; $i <= 11; $i++) {
            $members->push(Member::create([
                'nama' => 'Anggota '.$i,
                'nim' => '202600'.$i,
                'email' => 'anggota'.$i.'@example.test',
                'nomor_telepon' => '08123456789',
                'alamat' => 'Alamat '.$i,
                'status' => 'aktif',
            ]));
        }

        $this->getJson('/api/members')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('meta.total', 11);

        $this->getJson('/api/members/'.$members->first()->id)
            ->assertOk()
            ->assertJsonPath('data.nama', 'Anggota 1');

        $this->getJson('/api/members/99999')->assertNotFound();
    }

    public function test_loan_endpoints_create_return_and_list_nested_resources(): void
    {
        $member = Member::create([
            'nama' => 'Anggota API',
            'nim' => '20260001',
            'email' => 'api-anggota@example.test',
            'nomor_telepon' => '08123456789',
            'alamat' => 'Alamat API',
            'status' => 'aktif',
        ]);
        $user = User::factory()->create(['name' => 'Petugas API']);
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $this->postJson('/api/loans', [
            'member_id' => $member->id,
            'user_id' => $user->id,
            'tanggal_pinjam' => today()->toDateString(),
            'tanggal_kembali' => today()->addWeek()->toDateString(),
            'book_ids' => [$book->id],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'dipinjam')
            ->assertJsonPath('data.member.nama', 'Anggota API')
            ->assertJsonPath('data.petugas.name', 'Petugas API')
            ->assertJsonPath('data.books.0.judul', 'Buku Uji');

        $loan = Loan::firstOrFail();

        $this->getJson('/api/loans')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('data.0.member.id', $member->id)
            ->assertJsonPath('data.0.petugas.id', $user->id)
            ->assertJsonPath('data.0.books.0.id', $book->id);

        $this->putJson('/api/loans/'.$loan->id.'/kembalikan')
            ->assertOk()
            ->assertJsonPath('data.status', 'dikembalikan')
            ->assertJsonPath('data.tanggal_dikembalikan', today()->toDateString());

        $this->postJson('/api/loans', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['member_id', 'user_id', 'book_ids']);

        $this->putJson('/api/loans/99999/kembalikan')->assertNotFound();
    }

    public function test_stats_endpoint_reports_current_database_counts(): void
    {
        $member = Member::create([
            'nama' => 'Anggota Statistik',
            'nim' => '20260002',
            'email' => 'stats-anggota@example.test',
            'nomor_telepon' => '08123456789',
            'alamat' => 'Alamat Statistik',
            'status' => 'aktif',
        ]);
        $user = User::factory()->create();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $loan = Loan::create([
            'member_id' => $member->id,
            'user_id' => $user->id,
            'tanggal_pinjam' => today()->toDateString(),
            'tanggal_kembali' => today()->addWeek()->toDateString(),
        ]);
        $loan->loanItems()->create(['book_id' => $book->id]);

        $this->getJson('/api/stats')
            ->assertOk()
            ->assertExactJson([
                'total_buku' => 1,
                'total_anggota' => 1,
                'peminjaman_aktif' => 1,
            ]);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'nama_kategori' => 'Fiksi',
            'deskripsi' => 'Kategori untuk pengujian API',
        ]);
    }

    private function createBook(Category $category, array $overrides = []): Book
    {
        return Book::create(array_merge([
            'judul' => 'Buku Uji',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'isbn' => '9780000000000',
            'stok' => 2,
            'category_id' => $category->id,
        ], $overrides));
    }
}
