<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /** SRS-004: owner dan collaborator daftar boleh mengubah tugas. */
    public function update(User $user, Task $task): bool
    {
        return $task->list->isAccessibleBy($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->list->isAccessibleBy($user);
    }

    /** SRS-006 */
    public function toggle(User $user, Task $task): bool
    {
        return $task->list->isAccessibleBy($user);
    }
}
