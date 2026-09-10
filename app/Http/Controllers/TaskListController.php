<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use Illuminate\Http\Request;
use App\Models\ListMember;
use App\Models\User;

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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        TaskList::create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

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
    public function destroy(TaskList $taskList)
    {
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        $taskList->delete();

        return redirect()
            ->route('lists.index')
            ->with('success', 'List berhasil dihapus.');
    }

    public function addMember(Request $request, TaskList $taskList)
    {
        // Hanya Owner yang boleh menambahkan collaborator
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        // Owner tidak perlu ditambahkan sebagai collaborator
        if ($user->id === $taskList->owner_id) {
            return back()->with('error', 'User tersebut adalah Owner list.');
        }

        // Tambahkan collaborator
        ListMember::firstOrCreate([
            'task_list_id' => $taskList->id,
            'user_id' => $user->id,
        ]);

        return back()->with(
            'success',
            'Collaborator berhasil ditambahkan.'
        );
    }

    public function removeMember(TaskList $taskList, User $user)
    {
        // Hanya Owner yang boleh menghapus collaborator
        if ($taskList->owner_id !== auth()->id()) {
            abort(403);
        }

        ListMember::where('task_list_id', $taskList->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with(
            'success',
            'Collaborator berhasil dihapus.'
        );
    }
}