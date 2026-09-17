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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_members')
            ->withTimestamps();
    }

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
}
