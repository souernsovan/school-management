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
        Schema::table('attendances', function (Blueprint $table) {
            // index for queries filtering/grouping by attendance_date
            $table->index('attendance_date');

            // composite index for class + date lookups
            $table->index(['class_id', 'attendance_date'], 'attendances_class_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['attendance_date']);
            $table->dropIndex('attendances_class_date_idx');
        });
    }
};
