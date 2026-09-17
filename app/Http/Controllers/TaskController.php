<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * SRS-006: Menampilkan daftar tugas dengan fitur filter (Status, Priority, Due Date).
     */
    public function index(Request $request, TaskList $taskList): View
    {
        $this->authorizeAccess($taskList);

        $query = $taskList->tasks()->with('user');

        // Filter berdasarkan STATUS (SRS-006)
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_completed', false);
            }
        }

        // Filter berdasarkan PRIORITY (SRS-006)
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter berdasarkan DUE DATE (SRS-006)
        if ($request->filled('due')) {
            if ($request->due === 'overdue') {
                $query->where('due_date', '<', now())
                    ->where('is_completed', false);
            } elseif ($request->due === 'today') {
                $query->whereDate('due_date', today());
            } elseif ($request->due === 'week') {
                $query->whereBetween('due_date', [now(), now()->addWeek()]);
            }
        }

        // Urutkan: belum selesai dulu, lalu berdasarkan tanggal jatuh tempo
        $tasks = $query->orderBy('is_completed', 'asc')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('tasks.index', [
            'list' => $taskList,
            'tasks' => $tasks,
        ]);
    }

    /**
     * SRS-004: Menampilkan form tambah tugas.
     */
    public function create(TaskList $taskList): View
    {
        $this->authorizeAccess($taskList);

        return view('tasks.create', compact('taskList'));
    }

    /**
     * SRS-004: Menyimpan tugas baru ke dalam daftar.
     */
    public function store(Request $request, TaskList $taskList): RedirectResponse
    {
        $this->authorizeAccess($taskList);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,Low,Medium,High'],
            'due_date' => ['nullable', 'date'],
        ]);

        $taskList->tasks()->create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => strtolower($validated['priority']),
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return redirect()
            ->route('lists.show', $taskList)
            ->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * SRS-005: Menampilkan form edit tugas.
     */
    public function edit(Task $task)
    {
        $this->authorizeAccess($task->taskList);

        if (request()->wantsJson()) {
            return response()->json($task);
        }

        return view('tasks.edit', compact('task'));
    }

    /**
     * SRS-005: Memperbarui data tugas.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeAccess($task->taskList);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,Low,Medium,High'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => strtolower($validated['priority']),
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return redirect()
            ->route('lists.show', $task->task_list_id)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    /**
     * SRS-005: Menghapus tugas.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeAccess($task->taskList);

        $taskListId = $task->task_list_id;
        $task->delete();

        return redirect()
            ->route('lists.show', $taskListId)
            ->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * SRS-006 & SRS-007: Toggle status selesai/belum selesai tugas.
     */
    public function toggle(Request $request, Task $task)
    {
        $this->authorizeAccess($task->taskList);

        $task->is_completed = ! $task->is_completed;
        $task->completed_at = $task->is_completed ? now() : null;
        $task->save();

        $progress = $this->calculateProgress($task->taskList);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $task->is_completed,
                'progress' => $progress,
                'message' => $task->is_completed
                    ? 'Tugas ditandai selesai!'
                    : 'Tugas dikembalikan ke belum selesai',
            ]);
        }

        return back()->with('success', 'Status tugas berhasil diperbarui!');
    }

    /**
     * SRS-007: Menghitung persentase progres daftar tugas.
     */
    private function calculateProgress(TaskList $taskList): array
    {
        $total = $taskList->tasks()->count();
        $completed = $taskList->tasks()->where('is_completed', true)->count();

        $percentage = $total > 0
            ? round(($completed / $total) * 100, 1)
            : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
            'percentage' => $percentage,
        ];
    }

    /**
     * SRS-009: Memeriksa apakah pengguna (Owner, Collaborator, atau Admin) memiliki hak akses.
     */
    private function authorizeAccess(TaskList $taskList): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // Admin memiliki akses penuh global
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return;
        }

        $userId = $user->id;
        $isOwner = $taskList->user_id === $userId || $taskList->owner_id === $userId;
        $isCollaborator = $taskList->collaborators()->where('user_id', $userId)->exists();

        if (! $isOwner && ! $isCollaborator) {
            abort(403, 'Anda tidak memiliki hak akses ke daftar tugas ini.');
        }
    }
}