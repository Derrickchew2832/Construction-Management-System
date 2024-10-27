<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();


        $request->session()->regenerate();

        // Role-based redirection
        $user = Auth::user();
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $projectManagerRoleId = DB::table('roles')->where('name', 'project_manager')->value('id');
        $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');
        $supplierRoleId = DB::table('roles')->where('name', 'supplier')->value('id');
        $clientRoleId = DB::table('roles')->where('name', 'client')->value('id');
        
        if ($user->role_id == $adminRoleId) {
            return redirect()->intended(route('admin.approvePage', absolute: false));
        } elseif ($user->role_id == $projectManagerRoleId) {
            return redirect()->intended(route('project_manager.projects.index', absolute: false));
        } elseif ($user->role_id == $contractorRoleId) {
            return redirect()->intended(route('contractor.projects.index', absolute: false));
        } elseif ($user->role_id == $supplierRoleId) {
            return redirect()->intended(route('supplier.quotes.dashboard', absolute: false));
        } elseif ($user->role_id == $clientRoleId) {
            return redirect()->intended(route('client.projects.dashboard', absolute: false));
        }   

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
