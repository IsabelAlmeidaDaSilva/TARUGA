<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $table = 'teachers';

    protected $fillable = [
        'nome', 'rp', 'senha', 'email', 'fk_instituicao_id', 'precisa_atualizar_senha',
    ];

    protected $hidden = ['senha'];

    protected $casts = ['precisa_atualizar_senha' => 'boolean'];

    public function getAuthPasswordName()
    {
        return 'senha';
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'fk_instituicao_id');
    }

    /** Alias mantido para compatibilidade com código antigo. */
    public function institutions()
    {
        return $this->institution();
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'fk_professores_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'fk_professores_id');
    }
}
