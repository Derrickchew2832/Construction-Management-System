<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect; 

class RoleMiddleware
{
    public function handle($request, Closure $next)
{
    if ($request->user() && $request->user()->role === 'admin') {
        return $next($request);
    }

    return $request->expectsJson()
               ? abort(403, 'Unauthorized.')
               : Redirect::route('verification.notice'); // Redirect to verification notice for non-admin users
}
}