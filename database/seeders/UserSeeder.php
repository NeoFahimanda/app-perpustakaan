<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Bambang Sutrisno',
                'email' => 'admin@pens.ac.id',
                'role' => 'admin',
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'petugas1@pens.ac.id',
                'role' => 'petugas',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'petugas2@pens.ac.id',
                'role' => 'petugas',
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'petugas',
            ],
            [
                'name' => 'Petugas Perpustakaan',
                'email' => 'petugas@example.com',
                'role' => 'petugas',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                ],
            );
        }
    }
}
