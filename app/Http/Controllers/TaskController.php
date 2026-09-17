<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * SRS-006: Menampilkan tugas dengan filter
     */
    public function index(Request $request, TaskList $list)
    {
        $this->authorizeAccess($list);
        
        $query = $list->tasks()->with('user');

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_completed', false);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

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

        $tasks = $query->orderBy('is_completed', 'asc')
                       ->orderBy('due_date', 'asc')
                       ->get();
        
        return view('tasks.index', compact('list', 'tasks'));
    }
    
    /**
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
    public function toggle(Task $task)
    {
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
     * SRS-007: Hitung progress list
     */
    private function calculateProgress(TaskList $list): array
    {
        $total = $list->tasks()->count();
        $completed = $list->tasks()->where('is_completed', true)->count();
        
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
     * Cek apakah user punya akses ke list
     */
    private function authorizeAccess(TaskList $list): void
    {
        $userId = auth()->id();
        $hasAccess = $list->user_id === $userId 
            || $list->collaborators()->where('user_id', $userId)->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak punya akses ke daftar ini.');
        }
    }
}