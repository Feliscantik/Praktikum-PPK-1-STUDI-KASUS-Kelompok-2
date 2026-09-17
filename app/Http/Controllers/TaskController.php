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
     * SRS-002 & SRS-003: daftar milik sendiri + daftar yang dibagikan.
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
    {
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
