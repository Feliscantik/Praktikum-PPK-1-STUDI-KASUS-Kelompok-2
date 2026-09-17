<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskListRequest;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskListController extends Controller
{
    /**
     * SRS-006: Menampilkan daftar tugas dengan fitur filter (Status, Priority, Due Date).
     */
    public function index(Request $request, TaskList $taskList): View
    {
        $this->authorizeAccess($taskList);

        $query = $taskList->tasks()->with('user');

        // Filter berdasarkan STATUS (SRS-006)
     * SRS-002 & SRS-003: daftar milik sendiri + daftar yang dibagikan.
     * SRS-006: Menampilkan tugas dengan filter
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $ownedLists = $user->ownedLists()
            ->with('collaborators')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('is_completed', true),
            ])
            ->latest()
            ->get();

        $sharedLists = $user->sharedLists()
            ->with('owner')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('is_completed', true),
            ])
            ->latest('task_lists.created_at')
            ->get();

        return view('lists.index', compact('ownedLists', 'sharedLists'));
    }

    /** SRS-002 */
    public function create(): View
    {
        return view('lists.create');
    }

    /** SRS-002: pembuat daftar otomatis menjadi List Owner. */
    public function store(TaskListRequest $request): RedirectResponse
        $this->authorizeAccess($list);
        
        $query = $list->tasks()->with('user');

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
     * SRS-004 & SRS-010: Menyimpan tugas baru dengan validasi input
     */
    public function store(Request $request, TaskList $list)
    {
        $this->authorizeAccess($list);

        // Validasi input (SRS-010) untuk mencegah data kosong/tidak valid
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ]);

        // Simpan data aman menggunakan relasi
        $list->tasks()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'due_date'    => $validated['due_date'] ?? null,
            'user_id'     => auth()->id(),
        ]);

        return back()->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * SRS-004 & SRS-010: Memperbarui tugas dengan validasi input
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeAccess($task->list);

        // Validasi input (SRS-010)
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ]);

        $task->update($validated);

        return back()->with('success', 'Tugas berhasil diperbarui!');
    }

    /**
     * Menghapus tugas
     */
    public function destroy(Task $task)
    {
        $this->authorizeAccess($task->list);
        
        $task->delete();

        return back()->with('success', 'Tugas berhasil dihapus!');
    }
    
    /**
     * SRS-006: Toggle status selesai/belum selesai
     */
    public function create(TaskList $taskList): View
    {
        $this->authorizeAccess($taskList);

        return view('tasks.create', compact('taskList'));
        $this->authorizeAccess($task->list);

        $task->is_completed = !$task->is_completed;
        $task->completed_at = $task->is_completed ? now() : null;
        $task->save();

        $list = $task->list;
        $progress = $this->calculateProgress($list);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $task->is_completed,
                'progress' => $progress,
                'message' => $task->is_completed 
                    ? 'Tugas ditandai selesai!' 
                    : 'Tugas dikembalikan ke belum selesai',
            ]);
        }
        
        return back()->with('success', 'Status tugas diperbarui!');
    }

    /**
     * SRS-004: Menyimpan tugas baru ke dalam daftar.
     * SRS-007: Hitung progress list
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
        $list = TaskList::create([
            'owner_id' => $request->user()->id,
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        return redirect()
            ->route('lists.show', $list)
            ->with('success', 'Daftar berhasil dibuat.');
    }

    /**
     * SRS-002, SRS-006, SRS-007: detail daftar + filter tugas + progres.
     */
    public function show(Request $request, TaskList $taskList): View
    {
        $this->authorize('view', $taskList);

        $taskList->load('owner', 'collaborators');

        $query = $taskList->tasks()->with('user');

        // SRS-006: filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_completed', $request->status === 'completed');
        }

        // SRS-006: filter prioritas
        if ($request->filled('priority') && in_array($request->priority, Task::PRIORITIES, true)) {
            $query->where('priority', $request->priority);
        }

        // SRS-006: filter tenggat waktu
        match ($request->input('due')) {
            'overdue' => $query->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->where('is_completed', false),
            'today' => $query->whereDate('due_date', today()),
            'week' => $query->whereBetween('due_date', [now(), now()->addWeek()]),
            default => null,
        };

        $tasks = $query
            ->orderBy('is_completed')
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->get();

        // SRS-007: progres selalu dihitung dari seluruh tugas, bukan hasil filter.
        $progress = $taskList->progress();

        return view('lists.show', compact('taskList', 'tasks', 'progress'));
    }

    /** SRS-002 */
    public function update(TaskListRequest $request, TaskList $taskList): RedirectResponse
    {
        $this->authorize('update', $taskList);

        $taskList->update($request->validated());

        return back()->with('success', 'Daftar berhasil diperbarui.');
    }

    /** SRS-002: menghapus daftar beserta seluruh isinya (cascade). */
    public function destroy(TaskList $taskList): RedirectResponse
    {
        $this->authorize('delete', $taskList);

        $taskList->delete();

        return redirect()
            ->route('lists.index')
            ->with('success', 'Daftar beserta seluruh tugasnya berhasil dihapus.');
    }

    /** SRS-003: menambah collaborator berdasarkan email. */
    public function addMember(Request $request, TaskList $taskList): RedirectResponse
    {
        $this->authorize('manageMembers', $taskList);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', 'Pengguna dengan email tersebut tidak ditemukan.');
        }

        if ($user->id === $taskList->owner_id) {
            return back()->with('error', 'Pengguna tersebut adalah Owner daftar ini.');
        }

        if ($taskList->collaborators()->whereKey($user->id)->exists()) {
            return back()->with('error', 'Pengguna tersebut sudah menjadi collaborator.');
        }

        $taskList->collaborators()->attach($user->id);

        return back()->with('success', "{$user->name} berhasil ditambahkan sebagai collaborator.");
    }

    /** SRS-003 */
    public function removeMember(TaskList $taskList, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $taskList);

        $taskList->collaborators()->detach($user->id);

        return back()->with('success', 'Akses collaborator berhasil dicabut.');
    }
}
    }
}