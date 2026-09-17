<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SRS-008: menghubungkan tugas ke daftar (dibutuhkan supaya penghapusan
// daftar bisa ikut menghapus tugas-tugas di dalamnya).
// Nullable dulu karena tabel tasks sudah ada isinya sebelum kolom ini ada.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_list_id')
                ->nullable()
                ->after('id')
                ->constrained('task_lists')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_list_id');
        });
    }
};
