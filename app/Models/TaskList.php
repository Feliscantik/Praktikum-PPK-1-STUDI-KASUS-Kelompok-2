<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
    ];

    /** SRS-002 & SRS-003: Pembersihan otomatis jika cascadeOnDelete database tidak aktif */
    protected static function booted(): void
    {
        static::deleting(function (TaskList $taskList) {
            $taskList->tasks()->delete();
            $taskList->collaborators()->detach();
        });
    }

    /** SRS-002 */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** SRS-003: collaborator lewat tabel pivot list_members. */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_members')
            ->withTimestamps();
    }

    /** SRS-004 */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** Owner atau collaborator boleh mengakses daftar ini. */
    public function isAccessibleBy(User $user): bool
    {
        return $this->owner_id === $user->id
            || $this->collaborators()->whereKey($user->id)->exists();
    }

    /** SRS-007: progres = (tugas selesai / total tugas) * 100. */
    public function progress(): array
    {
        $total = $this->tasks()->count();
        $completed = $this->tasks()->where('is_completed', true)->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
            'percentage' => $percentage,
            'color' => match (true) {
                $percentage >= 80 => 'success',
                $percentage >= 50 => 'info',
                $percentage >= 25 => 'warning',
                default => 'danger',
            },
        ];
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isAccessibleBy(User $user): bool
    {
        return $this->owner_id === $user->id
            || $this->collaborators()->whereKey($user->id)->exists();
    }
    public function progress(): array
    {
        $total = $this->tasks()->count();
        $completed = $this->tasks()->where('is_completed', true)->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
            'percentage' => $percentage,
            'color' => match (true) {
                $percentage >= 80 => 'success',
                $percentage >= 50 => 'info',
                $percentage >= 25 => 'warning',
                default => 'danger',
            },
        ];
    }

    // SRS-008: relasi ke tugas-tugas dalam daftar ini
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
}
