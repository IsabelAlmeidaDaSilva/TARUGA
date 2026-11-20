<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TeacherController extends Controller
{
    public function registerTeacher(Request $request)
    {
        $institution = Auth::guard('institution')->user();
        abort_unless($institution, 403);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:55'],
            'rp' => ['required', 'string', 'max:25', 'unique:teachers,rp'],
            'email' => ['required', 'email', 'max:55', 'unique:teachers,email'],
            'senha' => ['required', 'string', 'min:8'],
        ]);

        $institution->teachers()->create([
            'nome' => $data['nome'],
            'rp' => $data['rp'],
            'email' => $data['email'],
            'senha' => Hash::make($data['senha']),
            // The original interface asks the institution to choose a password.
            // Do not block the teacher at login because no password-change page exists.
            'precisa_atualizar_senha' => false,
        ]);

        return redirect()->back()->with('success', 'Professor cadastrado com sucesso.');
    }

    public function update(Request $request, int $id)
    {
        $institution = Auth::guard('institution')->user();
        abort_unless($institution, 403);

        $teacher = $institution->teachers()->whereKey($id)->firstOrFail();
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:55'],
            'rp' => ['required', 'string', 'max:25', 'unique:teachers,rp,'.$teacher->id],
            'email' => ['required', 'email', 'max:55', 'unique:teachers,email,'.$teacher->id],
        ]);

        $teacher->update($data);

        return redirect()->route('institution.list')->with('success', 'Professor atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        $institution = Auth::guard('institution')->user();
        abort_unless($institution, 403);

        $teacher = $institution->teachers()->whereKey($id)->firstOrFail();

        if ($teacher->students()->exists() || $teacher->classes()->exists()) {
            return redirect()->back()->with('error', 'Este professor possui alunos ou turmas vinculados. Transfira esses registros antes de excluí-lo.');
        }

        $teacher->delete();

        return redirect()->route('institution.list')->with('success', 'Professor excluído com sucesso.');
    }

    public function search(Request $request)
    {
        $institution = Auth::guard('institution')->user();
        abort_unless($institution, 403);

        $search = trim((string) $request->input('search', ''));
        $teachers = $institution->teachers()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('nome', 'like', "%{$search}%")
                        ->orWhere('rp', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('nome')
            ->get();

        return view('usersList', compact('teachers', 'search'));
    }

    public function teacherList()
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher, 403);

        $students = $teacher->students()->with('class')->orderBy('nome')->get();

        $classes = $teacher->classes()->orderBy('nome')->get();
        return view('usersList', compact('students','classes'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        $teacher = Teacher::where('email', $credentials['email'])->first();

        if (!$teacher || !Hash::check($credentials['senha'], $teacher->senha)) {
            throw ValidationException::withMessages([
                'email' => 'Email ou senha incorretos. Tente novamente!',
            ]);
        }

        AuthHelper::logoutAll();
        Auth::guard('teacher')->login($teacher);
        $request->session()->regenerate();
        $request->session()->put('user_type', 'teacher');

        return redirect()->route('HomeTeacher');
    }

    public function updatePassword(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher, 403);

        $data = $request->validate([
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $teacher->senha = Hash::make($data['nova_senha']);
        $teacher->precisa_atualizar_senha = false;
        $teacher->save();

        return $request->expectsJson()
            ? response()->json(['message' => 'Senha atualizada com sucesso.'])
            : redirect()->route('HomeTeacher')->with('success', 'Senha atualizada com sucesso.');
    }
}
