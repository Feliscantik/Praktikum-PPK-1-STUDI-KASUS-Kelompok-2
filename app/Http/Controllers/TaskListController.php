<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\ListMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskListController extends Controller
{
    // Menampilkan semua list milik user
    public function index()
    {
        $lists = TaskList::where('owner_id', auth()->id())
            ->with('members.user')
            ->get();

        return view('lists.index', compact('lists'));
    }

    // Form membuat list
    public function create()
    {
        return view('lists.create');
    }

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
            ->route('lists.index')
            ->with('success', 'List berhasil dibuat.');
    }

    // Menampilkan detail list
    public function show(TaskList $taskList)
    {
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        $taskList->load('owner', 'members.user');

        return view('lists.show', compact('taskList'));
    }

    // Edit list
    public function update(Request $request, TaskList $taskList)
    {
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $taskList->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'List berhasil diperbarui.');
    }

    // Hapus list
    // SRS-008: penghapusan daftar (hapus tugas -> hapus keanggotaan -> hapus daftar)
    // dalam 1 transaksi. Kalau salah satu langkah gagal, semua di-rollback:
    // tidak boleh ada tugas tanpa daftar, atau daftar "setengah terhapus".
    public function destroy(TaskList $taskList)
    {
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

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
            ->with('success', 'List berhasil dihapus.');
    }

    public function addMember(Request $request, TaskList $taskList)
    {
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        if ($user->id === $taskList->owner_id) {
            return back()->with('error', 'User tersebut adalah Owner list.');
        }

        ListMember::firstOrCreate([
            'task_list_id' => $taskList->id,
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Collaborator berhasil ditambahkan.');
    }

    public function removeMember(TaskList $taskList, User $user)
    {
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        ListMember::where('task_list_id', $taskList->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Collaborator berhasil dihapus.');
    }
}
