<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\User;

class TaskListPolicy
{
    /**
     * Intercept semua gate: Admin memiliki akses penuh secara global (SRS-009).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /** SRS-002 & SRS-003: owner DAN collaborator boleh melihat detail daftar. */
    public function view(User $user, TaskList $taskList): bool
    {
        return $taskList->isAccessibleBy($user);
    }

    /** SRS-002: hanya owner yang boleh mengubah data daftar. */
    public function update(User $user, TaskList $taskList): bool
    {
        return $taskList->owner_id === $user->id;
    }

    /** SRS-002: hanya owner yang boleh menghapus daftar. */
    public function delete(User $user, TaskList $taskList): bool
    {
        return $taskList->owner_id === $user->id;
    }

    /** SRS-003: hanya owner yang boleh mengelola collaborator. */
    public function manageMembers(User $user, TaskList $taskList): bool
    {
        return $taskList->owner_id === $user->id;
    }

    /** SRS-004: owner dan collaborator boleh membuat tugas di daftar ini. */
    public function createTask(User $user, TaskList $taskList): bool
    {
        return $taskList->isAccessibleBy($user);
    }
}