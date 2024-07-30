<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        // Role-based redirection logic
        $user = Auth::user();
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $projectManagerRoleId = DB::table('roles')->where('name', 'projectmanager')->value('id');
        $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');

        if ($user->role_id == $adminRoleId) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->role_id == $projectManagerRoleId) {
            return redirect()->intended(route('projectmanager.dashboard'));
        } elseif ($user->role_id == $contractorRoleId) {
            return redirect()->intended(route('contractor.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}
