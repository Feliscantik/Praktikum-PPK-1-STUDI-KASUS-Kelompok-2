<?php

namespace App\Http\Controllers;

use App\Models\TaskList;

class ListController extends Controller
{
    /**
     * SRS-007: Tampilkan detail list dengan progress
     */
    public function show(TaskList $list)
    {
        // Cek akses
        $userId = auth()->id();
        $hasAccess = $list->user_id === $userId 
            || $list->collaborators()->where('user_id', $userId)->exists();
        
        if (!$hasAccess) {
            abort(403);
        }
        
        // SRS-007: Hitung progress
        $progress = $this->calculateProgress($list);
        
        // Ambil tasks (bisa dengan filter dari SRS-006)
        $tasks = $list->tasks()
            ->orderBy('is_completed', 'asc')
            ->orderBy('due_date', 'asc')
            ->get();
        
        return view('lists.show', compact('list', 'tasks', 'progress'));
    }
    
    /**
     * SRS-007: Hitung progress percentage
     */
    public function calculateProgress(TaskList $list): array
    {
        $total = $list->tasks()->count();
        $completed = $list->tasks()->where('is_completed', true)->count();
        $pending = $total - $completed;
        
        $percentage = $total > 0 
            ? round(($completed / $total) * 100, 1) 
            : 0;
        
        // Tentukan warna progress bar
        $color = match(true) {
            $percentage >= 80 => 'green',
            $percentage >= 50 => 'yellow',
            $percentage >= 25 => 'orange',
            default => 'red',
        };
        
        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'percentage' => $percentage,
            'color' => $color,
        ];
    }
}