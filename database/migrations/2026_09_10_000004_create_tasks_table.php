<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SRS-004, SRS-005, SRS-006, SRS-007
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_list_id')->constrained('task_lists')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // pembuat tugas
            $table->string('title');                                   // SRS-004: wajib
            $table->text('description')->nullable();                   // SRS-004: opsional
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium'); // SRS-005
            $table->dateTime('due_date')->nullable();                  // SRS-005
            $table->boolean('is_completed')->default(false);           // SRS-006
            $table->timestamp('completed_at')->nullable();             // SRS-006
            $table->timestamps();

            $table->index(['task_list_id', 'is_completed']);
            $table->index(['task_list_id', 'priority']);
            $table->index(['task_list_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
