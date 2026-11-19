<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $remaining = max(0, 20 - Book::count());

        if ($remaining > 0) {
            Book::factory()->count($remaining)->create();
        }
    }
}
