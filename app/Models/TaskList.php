<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'description',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ListMember::class);
    }

    // SRS-008: relasi ke tugas-tugas dalam daftar ini
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}