<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * SRS-003 & SRS-007: ringkasan daftar milik sendiri dan daftar yang
     * dibagikan ke pengguna, lengkap dengan progres masing-masing.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $ownedLists = $user->ownedLists()->withCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($q) => $q->where('is_completed', true),
        ])->latest()->get();

        $sharedLists = $user->sharedLists()->with('owner')->withCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($q) => $q->where('is_completed', true),
        ])->latest('task_lists.created_at')->get();

        return view('dashboard', compact('ownedLists', 'sharedLists'));
    }
}
