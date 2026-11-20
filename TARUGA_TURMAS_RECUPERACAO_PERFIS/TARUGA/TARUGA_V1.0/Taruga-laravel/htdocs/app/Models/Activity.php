<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'nome',
        'numeroAtividade',
        'nomeMateria',
        'fk_modulos_id',
    ];

    protected $casts = [
        'numeroAtividade' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'fk_modulos_id');
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class, 'fk_atividades_id');
    }

    public function userProgresses()
    {
        return $this->hasMany(UserProgress::class, 'fk_atividades_id');
    }
}
