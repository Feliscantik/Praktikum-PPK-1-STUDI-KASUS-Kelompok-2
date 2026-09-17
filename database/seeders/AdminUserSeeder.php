<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /** SRS-001: akun admin awal. */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@jara.test'],
            [
                'name' => 'Admin JARA',
                'password' => 'password123', // di-hash otomatis oleh cast 'hashed'
                'role' => 'admin',
            ]
        );
    }
}
