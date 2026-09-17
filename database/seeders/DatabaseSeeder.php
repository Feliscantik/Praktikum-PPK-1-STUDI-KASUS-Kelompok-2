<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Sebelumnya AdminUserSeeder tidak pernah dipanggil, sehingga
        // `php artisan migrate --seed` tidak menghasilkan akun apa pun.
        $this->call([
            AdminUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
