<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskListRequest;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\ListMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
    // Menyimpan list baru
    // SRS-008: pembuatan daftar (insert daftar + assign owner) dalam 1 transaksi.
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // insert daftar sekaligus assign owner (owner_id) dalam 1 query,
                // masih di dalam transaksi yang sama
                TaskList::create([
                    'owner_id' => auth()->id(),
                    'name' => $request->name,
                    'description' => $request->description,
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            // rollback otomatis oleh DB::transaction, state DB tidak berubah
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat daftar. Tidak ada perubahan yang disimpan, silakan coba lagi.');
        }

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

    /** SRS-002 & SRS-003: menghapus daftar beserta tugas dan collaborator-nya */
    public function destroy(TaskList $taskList): RedirectResponse
    // Hapus list
    // SRS-008: penghapusan daftar (hapus tugas -> hapus keanggotaan -> hapus daftar)
    // dalam 1 transaksi. Kalau salah satu langkah gagal, semua di-rollback:
    // tidak boleh ada tugas tanpa daftar, atau daftar "setengah terhapus".
    public function destroy(TaskList $taskList)
    {
        $this->authorize('delete', $taskList);

        DB::transaction(function () use ($taskList) {
            $taskList->delete();
        });
        try {
            DB::transaction(function () use ($taskList) {
                // 1. hapus tugas-tugas dalam daftar ini
                Task::where('task_list_id', $taskList->id)->delete();

                // 2. hapus keanggotaan/collaborator daftar ini
                ListMember::where('task_list_id', $taskList->id)->delete();

                // 3. baru hapus daftarnya sendiri
                $taskList->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            // rollback otomatis, tidak ada tugas/keanggotaan/daftar yang berubah
            return back()->with(
                'error',
                'Gagal menghapus daftar. Tidak ada perubahan yang disimpan, silakan coba lagi.'
            );
        }

        return redirect()
            ->route('lists.index')
            ->with('success', 'Daftar beserta seluruh tugas dan keanggotaan collaborator berhasil dihapus.');
    }

    /** SRS-003: menambah collaborator berdasarkan email. */
    public function addMember(Request $request, TaskList $taskList): RedirectResponse
    {
        $this->authorize('manageMembers', $taskList);
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

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
        ListMember::firstOrCreate([
            'task_list_id' => $taskList->id,
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Collaborator berhasil ditambahkan.');
    }

    /** SRS-003 */
    public function removeMember(TaskList $taskList, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $taskList);
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        $taskList->collaborators()->detach($user->id);

        return back()->with('success', 'Akses collaborator berhasil dicabut.');
        return back()->with('success', 'Collaborator berhasil dihapus.');
    }
}
