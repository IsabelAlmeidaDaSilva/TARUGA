<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Assessment extends Model
{
    protected $fillable=['fk_professores_id','titulo','descricao','prazo','ativa'];
    protected $casts=['prazo'=>'datetime','ativa'=>'boolean'];
    public function teacher(){return $this->belongsTo(Teacher::class,'fk_professores_id');}
    public function activities(){return $this->belongsToMany(Activity::class,'assessment_activities','assessment_id','activity_id')->withPivot('ordem')->orderBy('assessment_activities.ordem');}
    public function students(){return $this->belongsToMany(Student::class,'assessment_students','assessment_id','student_id')->withPivot(['attempt_number', 'recovery_activity_id'])->withTimestamps();}
    public function results(){return $this->hasMany(ActivityResult::class,'assessment_id');}
}
