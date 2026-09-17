<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SRS-003: Kolaborasi (tabel pivot antara task_lists dan users)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_list_id')->constrained('task_lists')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_list_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_members');
    }
};
