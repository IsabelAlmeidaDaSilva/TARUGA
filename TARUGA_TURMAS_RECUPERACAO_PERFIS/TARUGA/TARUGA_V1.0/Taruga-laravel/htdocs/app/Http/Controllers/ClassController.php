<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ClassController extends Controller {
 public function index(){ $teacher=Auth::guard('teacher')->user(); $classes=$teacher->classes()->with(['students'])->orderBy('nome')->get(); return view('teacher.classes',compact('classes')); }
 public function store(Request $request){ $teacher=Auth::guard('teacher')->user(); $data=$request->validate(['nome'=>['required','string','max:25'],'serie'=>['required','string','max:25']]); $teacher->classes()->create($data); return redirect()->route('teacher.classes')->with('success','Turma criada com sucesso.'); }
 public function show(int $id){ $teacher=Auth::guard('teacher')->user(); $class=$teacher->classes()->with('students')->findOrFail($id); return view('teacher.class-detail',compact('class')); }
 public function move(Request $request,int $id){ $teacher=Auth::guard('teacher')->user(); $class=$teacher->classes()->findOrFail($id); $data=$request->validate(['student_id'=>['required','integer']]); $student=$teacher->students()->findOrFail($data['student_id']); $student->update(['fk_turmas_id'=>$class->id]); return back()->with('success','Aluno vinculado à turma.'); }
}
