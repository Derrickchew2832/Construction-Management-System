<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Get the user's role
        $userRoleId = Auth::user()->role_id;

        // Retrieve the role IDs for the allowed roles
        $allowedRoleIds = DB::table('roles')
            ->whereIn('name', $roles)
            ->pluck('id')
            ->toArray();

        // Check if the user's role matches any of the allowed roles
        if (in_array($userRoleId, $allowedRoleIds)) {
            return $next($request);
        }

        // Redirect if the user does not have the appropriate role
        return redirect('/')->with('error', 'Unauthorized access');
    }
}
