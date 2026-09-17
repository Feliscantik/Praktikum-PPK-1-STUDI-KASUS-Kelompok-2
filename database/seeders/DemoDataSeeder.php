<?php

namespace Database\Seeders;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /** Data contoh untuk demo SRS-002 s/d SRS-007. */
    public function run(): void
    {
        $budi = User::firstOrCreate(
            ['email' => 'budi@jara.test'],
            ['name' => 'Budi', 'password' => 'password123', 'role' => 'user']
        );

        $sari = User::firstOrCreate(
            ['email' => 'sari@jara.test'],
            ['name' => 'Sari', 'password' => 'password123', 'role' => 'user']
        );

        if ($budi->ownedLists()->exists()) {
            return;
        }

        $list = TaskList::create([
            'owner_id' => $budi->id,
            'name' => 'Praktikum PPK 1',
            'description' => 'Daftar tugas studi kasus kelompok 2.',
        ]);

        $list->collaborators()->attach($sari->id); // SRS-003

        $list->tasks()->createMany([
            [
                'user_id' => $budi->id,
                'title' => 'Menyusun dokumen SRS',
                'description' => 'Lengkapi acceptance criteria tiap SRS.',
                'priority' => 'high',
                'due_date' => now()->subDay(),      // contoh tugas terlambat (SRS-005)
                'is_completed' => false,
            ],
            [
                'user_id' => $sari->id,
                'title' => 'Membuat skema database',
                'priority' => 'medium',
                'due_date' => now()->addHours(5),   // contoh tenggat dekat (SRS-005)
                'is_completed' => true,
                'completed_at' => now(),
            ],
            [
                'user_id' => $budi->id,
                'title' => 'Menulis feature test',
                'priority' => 'low',
                'due_date' => now()->addWeek(),
                'is_completed' => false,
            ],
        ]);
    }
}
