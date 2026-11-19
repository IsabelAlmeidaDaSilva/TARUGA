<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // IDs such as RA, RP, CNPJ and phone numbers are identifiers, not
        // quantities. Text columns preserve leading zeroes and punctuation.
        if (Schema::hasTable('institutions')) {
            Schema::table('institutions', function (Blueprint $table) {
                $table->string('cnpj', 20)->change();
                $table->string('telefone', 20)->change();
                $table->unique('cnpj', 'institutions_cnpj_unique');
                $table->unique('email', 'institutions_email_unique');
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->string('rp', 25)->change();
                $table->boolean('precisa_atualizar_senha')->default(false)->change();
                $table->unique('rp', 'teachers_rp_unique');
                $table->unique('email', 'teachers_email_unique');
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('ra', 25)->change();
                $table->string('foto', 255)->default('')->change();
                $table->unique('ra', 'students_ra_unique');
                $table->unique('email', 'students_email_unique');
            });
        }
    }

    public function down(): void
    {
        // Identifier columns intentionally remain strings on rollback so
        // existing values cannot be truncated or lose leading zeroes.
        if (Schema::hasTable('institutions')) {
            Schema::table('institutions', function (Blueprint $table) {
                $table->dropUnique('institutions_cnpj_unique');
                $table->dropUnique('institutions_email_unique');
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropUnique('teachers_rp_unique');
                $table->dropUnique('teachers_email_unique');
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropUnique('students_ra_unique');
                $table->dropUnique('students_email_unique');
            });
        }
    }
};
