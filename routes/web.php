<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;

// Welcome Route
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Route
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminUserController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/approve', [AdminUserController::class, 'approvePage'])->name('admin.approvePage');
    Route::post('/admin/approve/{id}', [AdminUserController::class, 'approveUser'])->name('admin.approve');
    Route::post('/admin/reject/{id}', [AdminUserController::class, 'rejectUser'])->name('admin.reject');
    Route::get('/admin/profile', [AdminUserController::class, 'editProfile'])->name('admin.profile');
    Route::post('/admin/profile', [AdminUserController::class, 'updateProfile'])->name('admin.updateProfile');
    Route::post('/admin/password', [AdminUserController::class, 'updatePassword'])->name('admin.updatePassword');
    Route::post('/logout', [AdminUserController::class, 'logout'])->name('logout');
});

// Authentication Routes
require __DIR__.'/auth.php';