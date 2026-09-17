<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_list_id',
        'title',
        'description',
        'priority',
        'due_date',
        'is_completed',
    ];

    // SRS-008: relasi tugas ke daftar tempatnya berada
    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }
}
