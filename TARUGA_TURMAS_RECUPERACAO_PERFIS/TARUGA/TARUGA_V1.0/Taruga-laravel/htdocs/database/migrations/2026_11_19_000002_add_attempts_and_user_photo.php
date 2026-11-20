<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assessment_students', function (Blueprint $table) {
            $table->unsignedInteger('attempt_number')->default(1);
            $table->unsignedBigInteger('recovery_activity_id')->nullable(); // FK validated in controller; compatible with SQLite ALTER TABLE
        });
        Schema::table('activity_results', function (Blueprint $table) {
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('submission_key')->nullable()->unique(); // null in legacy rows: preserves historical duplicates
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('foto'));
        Schema::table('activity_results', function (Blueprint $table) {
            $table->dropUnique(['submission_key']);
            $table->dropColumn(['attempt_number', 'submission_key']);
        });
        Schema::table('assessment_students', function (Blueprint $table) {
            $table->dropColumn('recovery_activity_id');
            $table->dropColumn('attempt_number');
        });
    }
};
