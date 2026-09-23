<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskListRequest;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * SRS-002: pembuat daftar otomatis menjadi List Owner.
     * SRS-008: pembuatan daftar dibungkus transaksi database.
     */
    public function store(TaskListRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                TaskList::create([
                    'owner_id' => $request->user()->id,
                    'name' => $request->validated('name'),
                    'description' => $request->validated('description'),
                ]);
            });
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat daftar. Silakan coba lagi.');
        }

        return redirect()
            ->route('lists.index')
            ->with('success', 'Daftar berhasil dibuat.');
    }

    /** SRS-002 & SRS-007 */
    public function show(TaskList $taskList): View
    {
        $this->authorize('view', $taskList);

        $taskList->load(['tasks' => function ($q) {
            $q->latest();
        }, 'collaborators', 'owner']);

        $progress = $taskList->progress();

        return view('lists.show', compact('taskList', 'progress'));
    }

    /** SRS-002 */
    public function update(TaskListRequest $request, TaskList $taskList): RedirectResponse
    {
        $this->authorize('update', $taskList);

        $taskList->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        return redirect()
            ->route('lists.show', $taskList)
            ->with('success', 'Daftar berhasil diperbarui.');
    }

    /**
     * SRS-002 & SRS-008: penghapusan daftar dibungkus transaksi.
     * Cascade delete tugas + detach collaborator ditangani oleh model event.
     */
    public function destroy(TaskList $taskList): RedirectResponse
    {
        $this->authorize('delete', $taskList);

        try {
            DB::transaction(function () use ($taskList) {
                $taskList->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus daftar. Silakan coba lagi.');
        }

        return redirect()
            ->route('lists.index')
            ->with('success', 'Daftar berhasil dihapus.');
    }

    /** SRS-003: menambahkan collaborator ke daftar. */
    public function addMember(Request $request, TaskList $taskList): RedirectResponse
    {
        $this->authorize('update', $taskList);

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->id === $taskList->owner_id) {
            return back()->with('error', 'Owner sudah memiliki akses ke daftar ini.');
        }

        $taskList->collaborators()->syncWithoutDetaching([$user->id]);

        return back()->with('success', "{$user->name} berhasil ditambahkan sebagai collaborator.");
    }

    /** SRS-003: menghapus collaborator dari daftar. */
    public function removeMember(TaskList $taskList, User $user): RedirectResponse
    {
        $this->authorize('update', $taskList);

        $taskList->collaborators()->detach($user->id);

        return back()->with('success', "{$user->name} berhasil dihapus dari collaborator.");
    }
}