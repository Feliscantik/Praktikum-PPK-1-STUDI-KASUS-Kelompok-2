<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * SRS-006: Menampilkan tugas dengan filter
     * Filter berdasarkan: status, priority, due_date
     */
    public function index(Request $request, TaskList $list)
    {
        // Pastikan user punya akses ke list ini
        $this->authorizeAccess($list);
        
        $query = $list->tasks()->with('user');
        
        // Filter berdasarkan STATUS (SRS-006)
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_completed', false);
            }
            // 'all' = tidak perlu filter
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
        
        // Urutkan: belum selesai dulu, lalu by due date
        $tasks = $query->orderBy('is_completed', 'asc')
                       ->orderBy('due_date', 'asc')
                       ->get();
        
        return view('tasks.index', compact('list', 'tasks'));
    }
    
    /**
     * SRS-006: Toggle status selesai/belum selesai
     */
    public function toggle(Task $task)
    {
        $this->authorizeAccess($task->list);
        
        // Toggle status
        $task->is_completed = !$task->is_completed;
        $task->completed_at = $task->is_completed ? now() : null;
        $task->save();
        
        // Hitung progress baru (untuk SRS-007)
        $list = $task->list;
        $progress = $this->calculateProgress($list);
        
        // Jika request via AJAX, return JSON
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
     * Rumus: (Jumlah Selesai / Total Tugas) * 100
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