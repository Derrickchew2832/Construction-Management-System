<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProjectManager\ProjectManagerController;
use App\Http\Controllers\Contractor\ContractorController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Redirect root URL to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Registration Routes
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

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
