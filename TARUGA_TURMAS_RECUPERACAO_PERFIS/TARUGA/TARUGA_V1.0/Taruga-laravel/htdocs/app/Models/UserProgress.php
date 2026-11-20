<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $table = 'user_progress';

    protected $fillable = ['status', 'fk_usuarios_id', 'fk_atividades_id'];

    protected $casts = ['status' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class, 'fk_usuarios_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'fk_atividades_id');
    }
}
