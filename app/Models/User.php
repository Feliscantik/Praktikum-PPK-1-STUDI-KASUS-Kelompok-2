<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** SRS-001: kolom yang boleh diisi Admin saat membuat akun. */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** SRS-001 */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** SRS-002: daftar yang dimiliki user ini. */
    public function ownedLists(): HasMany
    {
        return $this->hasMany(TaskList::class, 'owner_id');
    }

    /** SRS-003: daftar milik orang lain yang dibagikan ke user ini. */
    public function sharedLists(): BelongsToMany
    {
        return $this->belongsToMany(TaskList::class, 'list_members')
            ->withTimestamps();
    }

    /** SRS-004 */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}