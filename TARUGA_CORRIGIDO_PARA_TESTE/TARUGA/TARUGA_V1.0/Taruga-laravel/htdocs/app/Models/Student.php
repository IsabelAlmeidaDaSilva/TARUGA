<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $table = 'students';

    protected $fillable = [
        'nome', 'ra', 'senha', 'email', 'situacao', 'foto',
        'fk_professores_id', 'fk_turmas_id',
    ];

    protected $hidden = ['senha'];

    public function getAuthPasswordName()
    {
        return 'senha';
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'fk_professores_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'fk_turmas_id');
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class, 'fk_estudantes_id');
    }
}
