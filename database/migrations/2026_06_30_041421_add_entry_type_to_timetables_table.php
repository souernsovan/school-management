<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->string('entry_type', 10)->default('class')->after('id');
        });

        // Back-fill existing rows
        DB::table('timetables')->whereNotNull('exam_id')->update(['entry_type' => 'exam']);
        DB::table('timetables')->whereNull('exam_id')->whereNull('subject_id')->whereNotNull('title')->update(['entry_type' => 'break']);
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn('entry_type');
        });
    }
};
