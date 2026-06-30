<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE exams ADD COLUMN total_marks DECIMAL(8,2) NOT NULL DEFAULT 100 AFTER exam_date');
        DB::statement('ALTER TABLE exams ADD COLUMN description TEXT NULL AFTER total_marks');
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['total_marks', 'description']);
        });
    }
};
