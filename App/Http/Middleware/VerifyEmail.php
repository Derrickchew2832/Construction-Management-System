
<?php



use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class VerifyEmail
{
    public function handle($request, Closure $next)
    {

        // For other users, enforce email verification
        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? abort(403, 'Unauthorized.')
                : Redirect::route('verification.notice');
        }

        return $next($request);
    }
}

