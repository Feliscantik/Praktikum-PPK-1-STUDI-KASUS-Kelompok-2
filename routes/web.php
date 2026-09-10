<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskListController;

Route::middleware('auth')->group(function () {

    Route::get('/lists', [TaskListController::class, 'index'])
        ->name('lists.index');

    Route::get('/lists/create', [TaskListController::class, 'create'])
        ->name('lists.create');

    Route::post('/lists', [TaskListController::class, 'store'])
        ->name('lists.store');

    Route::get('/lists/{taskList}', [TaskListController::class, 'show'])
        ->name('lists.show');

    Route::put('/lists/{taskList}', [TaskListController::class, 'update'])
        ->name('lists.update');

    Route::delete('/lists/{taskList}', [TaskListController::class, 'destroy'])
        ->name('lists.destroy');

    Route::post(
        '/lists/{taskList}/members',
        [TaskListController::class, 'addMember']
    )->name('lists.members.add');

    Route::delete(
        '/lists/{taskList}/members/{user}',
        [TaskListController::class, 'removeMember']
    )->name('lists.members.remove');
});
