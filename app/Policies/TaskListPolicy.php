<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\User;

class TaskListPolicy
{
    /** SRS-003: owner DAN collaborator boleh melihat. */
    public function view(User $user, TaskList $list): bool
    {
        return $list->isAccessibleBy($user);
    }

    /** SRS-002: hanya owner yang boleh mengubah daftar. */
    public function update(User $user, TaskList $list): bool
    {
        return $list->owner_id === $user->id;
    }

    /** SRS-002: hanya owner yang boleh menghapus daftar. */
    public function delete(User $user, TaskList $list): bool
    {
        return $list->owner_id === $user->id;
    }

    /** SRS-003: hanya owner yang boleh mengelola collaborator. */
    public function manageMembers(User $user, TaskList $list): bool
    {
        return $list->owner_id === $user->id;
    }

    /** SRS-004: owner dan collaborator boleh membuat tugas. */
    public function createTask(User $user, TaskList $list): bool
    {
        return $list->isAccessibleBy($user);
    }
}
