<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController; 
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


Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');