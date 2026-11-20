<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Institution;
use App\Models\ActivityResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class InstitutionController extends Controller
{
    public function registerInstitution(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:55'],
            'cnpj' => ['required', 'string', 'max:20', 'unique:institutions,cnpj'],
            'email' => ['required', 'email', 'max:55', 'unique:institutions,email'],
            'endereco' => ['required', 'string', 'max:100'],
            'telefone' => ['required', 'string', 'max:20'],
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Institution::create([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'cnpj' => $data['cnpj'],
            'endereco' => $data['endereco'],
            'telefone' => $data['telefone'],
            'senha' => Hash::make($data['senha']),
        ]);

        return redirect()->route('identification')->with('success', 'Instituição cadastrada com sucesso. Faça o login para acessar.');
    }

    public function showLoginForm()
    {
        session(['user_type' => 'institution']);

        return view('login', ['userType' => 'institution']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ]);

        $institution = Institution::where('email', $credentials['email'])->first();

        if (!$institution || !Hash::check($credentials['senha'], $institution->senha)) {
            throw ValidationException::withMessages([
                'email' => 'Email ou senha incorretos. Tente novamente!',
            ]);
        }

        AuthHelper::logoutAll();
        Auth::guard('institution')->login($institution);
        $request->session()->regenerate();
        $request->session()->put('user_type', 'institution');

        return redirect()->route('HomeInstitution');
    }

    public function dashboard()
    {
        $institution=Auth::guard('institution')->user();
        $teachers=$institution->teachers()->withCount(['students','assessments','classes'])->get();
        $teacherIds=$teachers->pluck('id');
        $studentIds=\App\Models\Student::whereIn('fk_professores_id',$teacherIds)->pluck('id');
        $results=ActivityResult::whereIn('student_id',$studentIds)->get();
        $stats=['teachers'=>$teachers->count(),'classes'=>$teachers->sum('classes_count'),'students'=>$studentIds->count(),'assessments'=>\App\Models\Assessment::whereIn('fk_professores_id',$teacherIds)->count(),'completed'=>$results->count(),'average'=>$results->avg('score')];
        return view('institution.dashboard',compact('teachers','stats'));
    }

    public function institutionList()
    {
        $institution = Auth::guard('institution')->user();
        abort_unless($institution, 403);

        $teachers = $institution->teachers()->orderBy('nome')->get();

        return view('usersList', compact('teachers'));
    }
}
