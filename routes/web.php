<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProjectManager\ProjectManagerController;
use App\Http\Controllers\Contractor\ContractorController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Redirect root URL to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');


// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminUserController::class, 'index'])->name('dashboard');
    Route::get('/approve', [AdminUserController::class, 'approvePage'])->name('approvePage');
    Route::post('/approve/{id}', [AdminUserController::class, 'approveUser'])->name('approve');
    Route::post('/reject/{id}', [AdminUserController::class, 'rejectUser'])->name('reject');
    Route::get('/profile', [AdminUserController::class, 'editProfile'])->name('profile');
    Route::post('/profile', [AdminUserController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/password', [AdminUserController::class, 'updatePassword'])->name('updatePassword');
});

// Project Manager Routes
Route::middleware(['auth', 'role:project manager'])->prefix('projectmanager')->name('projectmanager.')->group(function () {
    Route::get('/dashboard', [ProjectManagerController::class, 'dashboard'])->name('dashboard');
    Route::resource('/projects', ProjectManagerController::class);
    Route::get('/projects/{project}/invite', [ProjectManagerController::class, 'invite'])->name('projects.invite');
    Route::post('/projects/{project}/invite', [ProjectManagerController::class, 'sendInvitation'])->name('projects.sendInvitation');
    Route::post('/projects/{project}/quote', [ProjectManagerController::class, 'quote'])->name('projects.quote');
    Route::post('/projects/{project}/appoint', [ProjectManagerController::class, 'appointMainContractor'])->name('projects.appointMainContractor');
    Route::get('/profile/edit', [ProjectManagerController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/update', [ProjectManagerController::class, 'updateProfile'])->name('profile.update');
});

// Contractor Routes
Route::middleware(['auth', 'role:contractor'])->prefix('contractor')->name('contractor.')->group(function () {
    Route::get('/dashboard', [ContractorController::class, 'dashboard'])->name('dashboard');
    Route::get('/quotes', [ContractorController::class, 'quotes'])->name('quotes.index');
    Route::get('/quotes/{quote}', [ContractorController::class, 'showQuote'])->name('quotes.show');
    Route::put('/quotes/{quote}', [ContractorController::class, 'updateQuote'])->name('quotes.update');
    Route::get('/profile/edit', [ContractorController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/update', [ContractorController::class, 'updateProfile'])->name('profile.update');
});

