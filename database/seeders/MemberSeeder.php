<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $remaining = max(0, 15 - Member::count());

        if ($remaining > 0) {
            Member::factory()->count($remaining)->create();
        }
    }
}
