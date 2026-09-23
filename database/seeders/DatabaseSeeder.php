<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin default
        User::firstOrCreate(
            ['email' => 'admin@jara.test'],
            [
                'name' => 'Admin JARA',
                'password' => 'password123',
                'role' => 'admin',
            ]
        );

        // Data demo (Budi, Sari, list, tasks)
        $this->call(DemoDataSeeder::class);
    }
}