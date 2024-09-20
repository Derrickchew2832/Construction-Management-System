<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$role): Response
    {
        // $roleId = DB::table('roles')->where('name', $role)->value('id');
        // if (Auth::check() && Auth::user()->role_id == $roleId) {
        //     return $next($request);
        // }
        $roleIds = DB::table('roles')->whereIn('name', $role)->pluck('id')->toArray();

        if (Auth::check() && in_array(Auth::user()->role_id, $roleIds)) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }
}
