<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_builds_a_complete_demo_dataset_and_is_repeatable(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Category::count());
        $this->assertSame(20, Book::count());
        $this->assertSame(15, Member::count());
        $this->assertSame(10, Loan::count());
        $this->assertSame(5, User::count());
        $this->assertGreaterThanOrEqual(10, LoanItem::count());
        $this->assertSame('id_ID', config('app.faker_locale'));

        $admin = User::where('email', 'admin@pens.ac.id')->firstOrFail();
        $this->actingAs($admin)
            ->get(route('books.index'))
            ->assertOk()
            ->assertSee('<nav class="pagination"', false)
            ->assertDontSee('<svg', false);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Category::count());
        $this->assertSame(20, Book::count());
        $this->assertSame(15, Member::count());
        $this->assertSame(10, Loan::count());
        $this->assertSame(5, User::count());
    }
}
