<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskListController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| Tamu (SRS-001)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Pengguna terautentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // SRS-002 — PENTING: /lists/create didaftarkan SEBELUM /lists/{taskList}
    Route::get('/lists', [TaskListController::class, 'index'])->name('lists.index');
    Route::get('/lists/create', [TaskListController::class, 'create'])->name('lists.create');
    Route::post('/lists', [TaskListController::class, 'store'])->name('lists.store');
    Route::get('/lists/{taskList}', [TaskListController::class, 'show'])->name('lists.show');
    Route::put('/lists/{taskList}', [TaskListController::class, 'update'])->name('lists.update');
    Route::delete('/lists/{taskList}', [TaskListController::class, 'destroy'])->name('lists.destroy');

    // SRS-003
    Route::post('/lists/{taskList}/members', [TaskListController::class, 'addMember'])
        ->name('lists.members.add');
    Route::delete('/lists/{taskList}/members/{user}', [TaskListController::class, 'removeMember'])
        ->name('lists.members.remove');

    // SRS-004, SRS-005, SRS-006 — tugas selalu berada di dalam sebuah daftar
    Route::get('/lists/{taskList}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/lists/{taskList}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
});

/*
|--------------------------------------------------------------------------
| Admin (SRS-001)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
