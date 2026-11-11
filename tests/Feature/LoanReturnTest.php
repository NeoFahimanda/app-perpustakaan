<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_loan_can_be_returned_and_shows_returned_badge(): void
    {
        $member = Member::create([
            'nama' => 'Anggota Uji',
            'nim' => '12345678',
            'email' => 'anggota@example.test',
            'nomor_telepon' => '08123456789',
            'alamat' => 'Alamat Uji',
            'status' => 'aktif',
        ]);
        $category = Category::create(['nama_kategori' => 'Fiksi', 'deskripsi' => 'Kategori uji']);
        $book = Book::create([
            'judul' => 'Buku Uji',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'isbn' => '9780000000001',
            'stok' => 1,
            'category_id' => $category->id,
        ]);
        $loan = Loan::create([
            'member_id' => $member->id,
            'user_id' => User::factory()->create()->id,
            'tanggal_pinjam' => today()->toDateString(),
            'tanggal_kembali' => today()->addWeek()->toDateString(),
        ]);
        $loan->loanItems()->create(['book_id' => $book->id]);

        $this->get(route('loans.index'))
            ->assertOk()
            ->assertSee('class="badge badge-dipinjam"', false)
            ->assertSee(route('loans.kembalikan', $loan->id));

        $this->get(route('loans.show', $loan->id))
            ->assertOk()
            ->assertSee('class="badge badge-dipinjam"', false);

        $this->put(route('loans.kembalikan', $loan->id))
            ->assertRedirect(route('loans.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'dikembalikan',
            'tanggal_dikembalikan' => now()->toDateString(),
        ]);

        $this->get(route('loans.index'))
            ->assertOk()
            ->assertSee('class="badge badge-dikembalikan"', false)
            ->assertDontSee(route('loans.kembalikan', $loan->id));

        $this->get(route('loans.show', $loan->id))
            ->assertOk()
            ->assertSee('class="badge badge-dikembalikan"', false);
    }
}
