<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_usuarios_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fk_atividades_id')->constrained('activities')->cascadeOnDelete();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();
            $table->unique(['fk_usuarios_id', 'fk_atividades_id'], 'user_progress_user_activity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};
