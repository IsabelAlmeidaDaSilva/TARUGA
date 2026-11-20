<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progress';

    protected $fillable = [
        'status',
        'fk_estudantes_id',
        'fk_atividades_id',
    ];

    protected $casts = ['status' => 'integer'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'fk_estudantes_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'fk_atividades_id');
    }
}
