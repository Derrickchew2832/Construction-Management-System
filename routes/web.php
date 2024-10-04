<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Contractor\ContractorTaskController;
use App\Http\Controllers\ContractorsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProjectManager\ProjectManagerController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\ProjectManagement\TaskController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Log;

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
Route::middleware(['auth', 'role:project_manager'])->prefix('project_manager')->name('project_manager.')->group(function () {
    Route::get('/dashboard', [ProjectManagerController::class, 'dashboard'])->name('dashboard');

    // Project Management Routes
    Route::get('/projects/quotes', [ProjectManagerController::class, 'manageQuotes'])->name('projects.quotes.index');
    Route::get('/projects', [ProjectManagerController::class, 'indexProjects'])->name('projects.index');
    Route::get('/projects/create', [ProjectManagerController::class, 'createProject'])->name('projects.create');
    Route::post('/projects', [ProjectManagerController::class, 'storeProject'])->name('projects.store');

    // Route for viewing and managing quotes (modified)
    Route::get('/projects/{project}/quotes/{quote}', [ProjectManagerController::class, 'viewQuote'])->name('projects.viewQuote');
    Route::post('/projects/{project}/quotes/{contractor}/approve', [ProjectManagerController::class, 'approveQuote'])->name('projects.approveQuote');
    Route::post('/projects/{project}/quotes/{contractor}/reject', [ProjectManagerController::class, 'rejectQuote'])->name('projects.rejectQuote');
    Route::post('/projects/quotes/suggest', [ProjectManagerController::class, 'suggestPrice'])->name('projects.suggestPrice');
    Route::get('/projects/quotes', [ProjectManagerController::class, 'manageQuotes'])->name('projects.quotes');
    Route::post('/projects/quotes/action', [ProjectManagerController::class, 'handleQuoteAction'])->name('projects.quotes.action');


    Route::get('projects/{projectId}/manage', [ProjectManagerController::class, 'managementBoard'])->name('projects.manage');

    // Other project routes
    Route::get('/projects/{project}', [ProjectManagerController::class, 'showProject'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectManagerController::class, 'editProject'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectManagerController::class, 'updateProject'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectManagerController::class, 'deleteProject'])->name('projects.delete');

    // Profile routes
    Route::get('/profile', [ProjectManagerController::class, 'editProfile'])->name('profile');
    Route::put('/profile', [ProjectManagerController::class, 'updateProfile'])->name('profile.update');

    // Favorite and invitation routes
    Route::post('/projects/{project}/favorite', [ProjectManagerController::class, 'toggleFavorite'])->name('projects.toggleFavorite');
    Route::get('/projects/{project}/invite', [ProjectManagerController::class, 'inviteContractor'])->name('projects.invite');
    Route::post('/projects/{project}/invite', [ProjectManagerController::class, 'storeInvite'])->name('projects.storeInvite');
});



Route::middleware(['auth', 'role:contractor'])->prefix('contractor')->name('contractor.')->group(function () {

    // Contractor Dashboard
    Route::get('/dashboard', [ContractorsController::class, 'dashboard'])->name('dashboard');
    Route::get('/projects', [ContractorsController::class, 'indexProjects'])->name('projects.index');
    Route::post('/projects/{project}/submit-quote', [ContractorsController::class, 'submitQuote'])->name('projects.submitQuote');
    
    Route::post('/projects/{project}/accept-quote', [ContractorsController::class, 'respondToSuggestion'])->name('acceptQuote');
    Route::post('/projects/{project}/reject-quote', [ContractorsController::class, 'respondToSuggestion'])->name('rejectQuote');
    Route::post('/projects/{project}/suggest-quote', [ContractorsController::class, 'respondToSuggestion'])->name('suggestQuote');

    Route::post('/projects/{projectId}/favorite', [ContractorsController::class, 'toggleFavorite'])->name('projects.favorite');
    Route::get('contractor/projects/{project}/manage', [ContractorsController::class, 'manageProject'])->name('contractor.projects.manage');
    Route::get('/projects/quotes', [ContractorsController::class, 'showQuotes'])->name('projects.quotes');

    // Routes for task quotes
    Route::get('contractor/tasks', [ContractorTaskController::class, 'indexTasks'])->name('contractor.tasks.index');
    Route::post('tasks/{taskId}/submit-quote', [ContractorTaskController::class, 'submitTaskQuote'])->name('tasks.submitQuote');
    Route::post('tasks/{taskId}/accept-quote', [ContractorTaskController::class, 'acceptTaskQuote'])->name('tasks.acceptQuote');
    Route::post('tasks/{taskId}/reject-quote', [ContractorTaskController::class, 'rejectTaskQuote'])->name('tasks.rejectQuote');
    Route::post('tasks/{taskId}/suggest-quote', [ContractorTaskController::class, 'suggestTaskQuote'])->name('tasks.suggestQuote');
    // Contractor Profile
    Route::get('/profile', [ContractorsController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [ContractorsController::class, 'updateProfile'])->name('profile.update');

    // Contractor Change Password
    Route::get('/change-password', [ContractorsController::class, 'changePassword'])->name('change_password');
    Route::post('/change-password', [ContractorsController::class, 'updatePassword'])->name('update_password');


    // Contractor Logout
    Route::post('/logout', [ContractorsController::class, 'logout'])->name('logout');
});


// Supplier Routes
Route::middleware(['auth', 'role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', [SupplierController::class, 'dashboard'])->name('dashboard'); 
    Route::get('/quotes/dashboard', [SupplierController::class, 'quotes'])->name('quotes.index'); 
    Route::get('/delivery', [SupplierController::class, 'delivery'])->name('delivery');
    Route::get('/profile', [SupplierController::class, 'editProfile'])->name('profile');
    Route::put('/profile', [SupplierController::class, 'updateProfile'])->name('profile.update');
});


// Client Routes
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('dashboard');
    Route::get('/projects/dashboard', [ClientController::class, 'projects'])->name('projects.dashboard');
    Route::get('/invitations', [ClientController::class, 'invitations'])->name('invitations');
    Route::get('/profile', [ClientController::class, 'editProfile'])->name('profile');
    Route::put('/profile', [ClientController::class, 'updateProfile'])->name('profile.update');
});



Route::middleware(['auth', 'role:project_manager,contractor,client'])
    ->prefix('projects/{projectId}')
    ->name('tasks.')
    ->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('store');
        Route::get('/tasks/{taskId}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/tasks/{taskId}', [TaskController::class, 'update'])->name('update');
        Route::delete('/tasks/{taskId}', [TaskController::class, 'destroy'])->name('destroy');
        Route::get('/invite', [TaskController::class, 'invite'])->name('invite');
        Route::get('/statistics', [TaskController::class, 'statistics'])->name('statistics');
        // Route to show the task quote
        Route::get('/quote', [TaskController::class, 'showQuote'])->name('quote');
        Route::post('/tasks/{taskId}/quote/respond', [TaskController::class, 'respondToTaskQuote'])->name('quote.respond');
        Route::get('/tasks/{taskId}/details', [TaskController::class, 'viewTaskDetails'])->name('details');
        Route::post('/tasks/{taskId}/update-status', [TaskController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/tasks/{task}/update-category', [TaskController::class, 'updateCategory'])->name('updateCategory');

    });
