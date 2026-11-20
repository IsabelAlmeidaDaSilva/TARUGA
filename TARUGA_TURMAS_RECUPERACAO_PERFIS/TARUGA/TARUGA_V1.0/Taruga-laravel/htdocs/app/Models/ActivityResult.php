<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityResult extends Model
{
    protected $fillable=['activity_id','student_id','user_id','assessment_id','attempt_number','submission_key','correct','total','errors','score','completed_at'];
    protected $casts=['score'=>'decimal:2','completed_at'=>'datetime'];
    public function activity(){return $this->belongsTo(Activity::class);}
    public function student(){return $this->belongsTo(Student::class);}
    public function user(){return $this->belongsTo(User::class);}
    public function assessment(){return $this->belongsTo(Assessment::class);}
}
