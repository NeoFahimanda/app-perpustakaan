<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_demo_admin_and_petugas_accounts(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@pens.ac.id',
            'name' => 'Bambang Sutrisno',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'petugas1@pens.ac.id',
            'name' => 'Siti Rahmawati',
            'role' => 'petugas',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'petugas2@pens.ac.id',
            'name' => 'Ahmad Fauzi',
            'role' => 'petugas',
        ]);
    }

    public function test_guests_are_redirected_and_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'petugas@example.test',
            'password' => 'password',
            'role' => 'petugas',
        ]);

        $this->get(route('books.index'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Ingat saya');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ])->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->withSession(['url.intended' => null])->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertAuthenticatedAs($user);
        $this->get(route('books.index'))
            ->assertOk()
            ->assertSee($user->name.' (Petugas)')
            ->assertSee('Profil');

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();
        $this->get(route('books.index'))->assertRedirect(route('login'));
    }

    public function test_only_admins_can_access_categories(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($petugas)
            ->get(route('categories.index'))
            ->assertForbidden();

        $this->get(route('books.index'))
            ->assertOk()
            ->assertDontSee(route('categories.index'));

        $this->actingAs($admin)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee(route('categories.index'));
    }

    public function test_loan_uses_the_authenticated_user_instead_of_submitted_user_id(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);
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

        $this->actingAs($user)
            ->get(route('loans.create'))
            ->assertOk()
            ->assertSee('Petugas pencatat: '.$user->name)
            ->assertDontSee('name="user_id"');

        $this->post(route('loans.store'), [
            'member_id' => $member->id,
            'user_id' => User::factory()->create()->id,
            'tanggal_pinjam' => today()->toDateString(),
            'tanggal_kembali' => today()->addWeek()->toDateString(),
            'book_ids' => [$book->id],
        ])->assertRedirect(route('loans.index'));

        $this->assertDatabaseHas('loans', [
            'member_id' => $member->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_change_password_from_profile_after_verifying_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
            'role' => 'petugas',
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee('Ganti Password');

        $this->post(route('profile.password.update'), [
            'current_password' => 'incorrect',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');

        $this->post(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('profile.show'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
