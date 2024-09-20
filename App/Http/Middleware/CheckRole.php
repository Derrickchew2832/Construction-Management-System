<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    public function handle($request, Closure $next, $roles)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Get the user's role ID or role name (depending on how your roles are set up)
        $userRoleId = Auth::user()->role_id;

        // Split roles into an array of role names
        $allowedRoles = explode(',', $roles);  // This ensures multiple roles are checked
        Log::info('Allowed roles: ' . json_encode($allowedRoles));

        // Retrieve the role IDs for the allowed roles
        $allowedRoleIds = DB::table('roles')
            ->whereIn('name', $allowedRoles)
            ->pluck('id')
            ->toArray();

        // Log for debugging
        Log::info('User Role ID: ' . $userRoleId);
        Log::info('Allowed Role IDs: ' . json_encode($allowedRoleIds));

        // Check if the user's role matches any of the allowed roles
        if (in_array($userRoleId, $allowedRoleIds)) {
            return $next($request);
        }

        // If the user doesn't have the required role, redirect with an error
        return redirect('/')->with('error', 'Unauthorized access');
    }
}
