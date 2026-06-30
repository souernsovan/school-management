<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->string('title')->nullable()->after('class_id');
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['teacher_id']);
        });

        DB::statement('ALTER TABLE timetables MODIFY subject_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE timetables MODIFY teacher_id BIGINT UNSIGNED NULL');

        Schema::table('timetables', function (Blueprint $table) {
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['teacher_id']);
        });

        DB::statement('ALTER TABLE timetables MODIFY subject_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE timetables MODIFY teacher_id BIGINT UNSIGNED NOT NULL');

        Schema::table('timetables', function (Blueprint $table) {
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
        });
    }
};
