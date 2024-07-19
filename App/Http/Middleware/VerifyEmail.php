<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class VerifyEmail
{
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated and role is admin
        if ($request->user() && $request->user()->role === 'admin' && !$request->user()->hasVerifiedEmail()) {
            // If admin and email not verified, allow access without verification
            return $next($request);
        }

        // For other users, enforce email verification
        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? abort(403, 'Unauthorized.')
                : Redirect::route('verification.notice');
        }

        return $next($request);
    }
}
