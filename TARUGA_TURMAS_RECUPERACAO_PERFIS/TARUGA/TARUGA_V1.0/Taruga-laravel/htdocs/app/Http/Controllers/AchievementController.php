<?php
namespace App\Http\Controllers;
use App\Models\ActivityResult;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function stickersFor(Student|User $learner): array
    {
        $query=ActivityResult::query();
        if($learner instanceof Student) $query->where('student_id',$learner->id);
        else $query->where('user_id',$learner->id);
        $count=(clone $query)->count();
        $perfect=(clone $query)->where('score','>=',10)->exists();
        $distinct=(clone $query)->distinct()->count('activity_id');
        return [
            ['icon'=>'⭐','name'=>'Primeira vitória','description'=>'Conclua uma atividade','unlocked'=>$count>=1],
            ['icon'=>'📚','name'=>'Pequeno leitor','description'=>'Conclua cinco atividades','unlocked'=>$count>=5],
            ['icon'=>'🏆','name'=>'Nota dez','description'=>'Alcance uma nota 10','unlocked'=>$perfect],
            ['icon'=>'🐢','name'=>'Explorador','description'=>'Conclua duas atividades diferentes','unlocked'=>$distinct>=2],
            ['icon'=>'🌈','name'=>'Persistência','description'=>'Conclua dez atividades','unlocked'=>$count>=10],
        ];
    }
    public function index()
    {
        $learner=Auth::guard('student')->user() ?: Auth::guard('web')->user();
        abort_unless($learner,403);
        $stickers=$this->stickersFor($learner);
        $count=ActivityResult::when($learner instanceof Student, fn($q)=>$q->where('student_id',$learner->id),fn($q)=>$q->where('user_id',$learner->id))->count();
        return view('students.achievements',compact('stickers','count'));
    }
}
