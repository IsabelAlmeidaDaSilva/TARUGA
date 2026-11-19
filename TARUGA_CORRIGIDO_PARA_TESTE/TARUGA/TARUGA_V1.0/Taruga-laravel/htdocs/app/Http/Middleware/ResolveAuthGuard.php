<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveAuthGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = match ($request->session()->get('user_type')) {
            'student' => 'student',
            'teacher' => 'teacher',
            'institution' => 'institution',
            'user' => 'web',
            default => 'web',
        };

        Auth::shouldUse($guard);

        return $next($request);
    }
}
