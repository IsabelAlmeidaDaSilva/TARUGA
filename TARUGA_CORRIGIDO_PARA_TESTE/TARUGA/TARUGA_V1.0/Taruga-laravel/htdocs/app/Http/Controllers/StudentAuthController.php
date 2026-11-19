<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('register', ['userType' => 'user']);
    }

    /** Public self-registration creates an independent User account. */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['nome'],
            'email' => $data['email'],
            'password' => Hash::make($data['senha']),
        ]);

        AuthHelper::logoutAll();
        $request->session()->forget('user_type');

        return redirect()->route('identification')->with(
            'success',
            'Cadastro realizado com sucesso! Faça o login para acessar.'
        );
    }

    public function showLoginForm()
    {
        session(['user_type' => 'student']);

        return view('identification');
    }

    /** Students are created by their responsible teacher, not publicly. */
    public function registerStudent(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        abort_unless($teacher, 403, 'Apenas professores autenticados podem cadastrar alunos.');

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:55'],
            'ra' => ['required', 'string', 'max:25', 'unique:students,ra'],
            'email' => ['required', 'email', 'max:55', 'unique:students,email'],
            'senha' => ['required', 'string', 'min:8'],
            'situacao' => ['required', 'in:Ouvinte,Não-ouvinte'],
        ]);

        $class = $teacher->classes()->first();

        if (!$class) {
            $class = $teacher->classes()->create([
                'nome' => 'Turma geral',
                'serie' => 'Não informada',
            ]);
        }

        $teacher->students()->create([
            'nome' => $data['nome'],
            'ra' => $data['ra'],
            'email' => $data['email'],
            'senha' => Hash::make($data['senha']),
            'situacao' => $data['situacao'],
            'foto' => '',
            'fk_turmas_id' => $class->id,
        ]);

        return redirect()->back()->with('success', 'Aluno cadastrado com sucesso.');
    }

    public function update(Request $request, int $id)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher, 403);

        $student = $teacher->students()->whereKey($id)->firstOrFail();

        $data = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'max:55'],
            'ra' => ['sometimes', 'required', 'string', 'max:25', 'unique:students,ra,'.$student->id],
            'email' => ['sometimes', 'required', 'email', 'max:55', 'unique:students,email,'.$student->id],
            'situacao' => ['sometimes', 'required', 'in:Ouvinte,Não-ouvinte'],
        ]);

        $student->update($data);

        return redirect()->route('teacher.list')->with('success', 'Aluno atualizado com sucesso.');
    }

    public function delete(int $id)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher, 403);

        $student = $teacher->students()->whereKey($id)->firstOrFail();

        if ($student->progresses()->exists()) {
            return redirect()->back()->with('error', 'Este aluno possui progresso registrado. Para preservar o histórico pedagógico, não é possível excluí-lo.');
        }

        $student->delete();

        return redirect()->route('teacher.list')->with('success', 'Aluno excluído com sucesso.');
    }

    public function search(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher, 403);

        $search = trim((string) $request->input('search', ''));
        $students = $teacher->students()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('nome', 'like', "%{$search}%")
                        ->orWhere('ra', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('nome')
            ->get();

        return view('usersList', compact('students', 'search'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        $student = Student::where('email', $credentials['email'])->first();

        if (!$student || !Hash::check($credentials['senha'], $student->senha)) {
            throw ValidationException::withMessages([
                'email' => 'Email ou senha incorretos. Tente novamente!',
            ]);
        }

        AuthHelper::logoutAll();
        Auth::guard('student')->login($student);
        $request->session()->regenerate();
        $request->session()->put('user_type', 'student');

        return redirect()->route('HomeStudent');
    }

    public function logout(Request $request)
    {
        return app(AuthController::class)->logout($request);
    }

    public function updatePhoto(Request $request)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);

        $data = $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($student->foto && Storage::disk('public')->exists($student->foto)) {
            Storage::disk('public')->delete($student->foto);
        }

        $student->foto = $request->file('foto')->store('students/photos', 'public');
        $student->save();

        return back()->with('success', 'Foto atualizada com sucesso.');
    }
}
