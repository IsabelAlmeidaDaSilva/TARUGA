<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use App\Models\Activity;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_professores_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('titulo', 120);
            $table->text('descricao')->nullable();
            $table->dateTime('prazo')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });
        Schema::create('assessment_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->unsignedInteger('ordem')->default(1);
            $table->unique(['assessment_id', 'activity_id']);
        });
        Schema::create('assessment_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['assessment_id', 'student_id']);
        });
        Schema::create('activity_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->cascadeOnDelete();
            $table->unsignedInteger('correct')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->decimal('score', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'completed_at']);
            $table->index(['user_id', 'completed_at']);
            $table->index(['assessment_id', 'student_id']);
        });

        // Register the two games already present in the repository as selectable library activities.
        $libras = Module::firstOrCreate(['tema' => 'libras', 'numeroModulo' => 1]);
        $port = Module::firstOrCreate(['tema' => 'portugues', 'numeroModulo' => 1]);
        Activity::firstOrCreate(['nomeMateria' => 'libras', 'numeroAtividade' => 1, 'fk_modulos_id' => $libras->id], ['nome' => 'Alfabeto em Libras']);
        Activity::firstOrCreate(['nomeMateria' => 'portugues', 'numeroAtividade' => 1, 'fk_modulos_id' => $port->id], ['nome' => 'Alfabeto em Português']);
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_results');
        Schema::dropIfExists('assessment_students');
        Schema::dropIfExists('assessment_activities');
        Schema::dropIfExists('assessments');
    }
};
