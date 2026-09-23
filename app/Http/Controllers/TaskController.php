<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /** SRS-004 & SRS-006: daftar semua tugas user (dengan filter). */
    public function index(Request $request): View
    {
        $user = $request->user();

        $tasks = Task::query()
            ->whereHas('taskList', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('collaborators', fn ($q2) => $q2->whereKey($user->id));
            })
            ->with('taskList')
            ->when($request->filled('status'), fn ($q) => $q->where('is_completed', $request->status === 'completed'))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->latest()
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    /** SRS-004 */
    public function create(TaskList $taskList): View
    {
        $this->authorize('view', $taskList);

        return view('tasks.create', compact('taskList'));
    }

    /** SRS-004 */
    public function store(TaskRequest $request, TaskList $taskList): RedirectResponse
    {
        $this->authorize('view', $taskList);

        $taskList->tasks()->create([
            'user_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'priority' => $request->validated('priority'),
            'due_date' => $request->validated('due_date'),
        ]);

        return redirect()
            ->route('lists.show', $taskList)
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    /** SRS-004 */
    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', compact('task'));
    }

    /** SRS-004 */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'priority' => $request->validated('priority'),
            'due_date' => $request->validated('due_date'),
        ]);

        return redirect()
            ->route('lists.show', $task->task_list_id)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /** SRS-004 */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $listId = $task->task_list_id;
        $task->delete();

        return redirect()
            ->route('lists.show', $listId)
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /** SRS-006: toggle status selesai/belum. */
    public function toggle(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update([
            'is_completed' => ! $task->is_completed,
            'completed_at' => $task->is_completed ? null : now(),
        ]);

        return back()->with('success', 'Status tugas diperbarui.');
    }
}