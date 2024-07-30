<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckRole
{
    public function handle($request, Closure $next, $role)
    {
        $roleId = DB::table('roles')->where('name', $role)->value('id');

        if (Auth::check() && Auth::user()->role_id == $roleId) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }
}
