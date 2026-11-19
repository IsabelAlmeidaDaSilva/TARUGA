<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $table = 'institutions';

    protected $fillable = [
        'nome', 'email', 'cnpj', 'endereco', 'telefone', 'senha',
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

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'fk_instituicao_id');
    }
}
