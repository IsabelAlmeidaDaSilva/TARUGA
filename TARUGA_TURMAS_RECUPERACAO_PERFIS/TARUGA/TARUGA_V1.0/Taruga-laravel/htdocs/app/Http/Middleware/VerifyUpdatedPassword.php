<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUpdatedPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        // This legacy middleware is not attached globally. When used on a
        // teacher-only route, it at least enforces the correct guard. The
        // current UI does not provide a mandatory password-change screen.
        abort_unless(Auth::guard('teacher')->check(), 403);

        return $next($request);
    }
}
