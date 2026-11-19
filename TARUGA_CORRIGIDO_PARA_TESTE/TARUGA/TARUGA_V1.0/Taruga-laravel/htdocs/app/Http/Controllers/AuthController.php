<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const USER_TYPES = ['user', 'student', 'teacher', 'institution'];

    public function showLoginForm(string $userType)
    {
        abort_unless(in_array($userType, self::USER_TYPES, true), 404);

        // The public "student" option is retained for old links, but new
        // independent accounts use the dedicated "user" profile.
        session(['user_type' => $userType]);

        return view('login', compact('userType'));
    }

    public function showRegisterForm(string $userType)
    {
        abort_unless(in_array($userType, ['user', 'institution'], true), 404);

        session(['user_type' => $userType]);

        return view('register', compact('userType'));
    }

    public function login(Request $request)
    {
        $userType = $request->session()->get('user_type');

        return match ($userType) {
            'user' => $this->loginCommonUser($request),
            'student' => app(StudentAuthController::class)->login($request),
            'teacher' => app(TeacherController::class)->login($request),
            'institution' => app(InstitutionController::class)->login($request),
            default => redirect()->route('identification')->withErrors([
                'email' => 'Selecione um tipo de usuário antes de entrar.',
            ]),
        };
    }

    private function loginCommonUser(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['senha'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email ou senha incorretos. Tente novamente!',
            ]);
        }

        AuthHelper::logoutAll();
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put('user_type', 'user');

        return redirect()->route('HomeStudent');
    }

    public function logout(Request $request)
    {
        AuthHelper::logoutAll();
        $request->session()->forget('user_type');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('identification');
    }
}
