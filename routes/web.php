<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\ListController;

Route::middleware(['auth'])->group(function () {
    
    // SRS-006: Toggle status selesai
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])
        ->name('tasks.toggle');
    
    // SRS-006: Filter tugas (bisa via query string)
    Route::get('/lists/{list}/tasks', [TaskController::class, 'index'])
        ->name('tasks.index');
    
    // SRS-007: Progress tracking (bisa di ListController@show)
    Route::get('/lists/{list}', [ListController::class, 'show'])
        ->name('lists.show');
});