<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function getUserType(): ?string
    {
        foreach (['institution', 'teacher', 'student', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard === 'web' ? 'user' : $guard;
            }
        }

        return null;
    }

    public static function logoutAll(): void
    {
        foreach (['web', 'student', 'teacher', 'institution'] as $guard) {
            Auth::guard($guard)->logout();
        }
    }
}
