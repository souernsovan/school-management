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
            $table->string('exam_type', 100)->nullable()->after('entry_type');
        });

        // Back-fill from linked exam records
        DB::table('timetables')
            ->where('entry_type', 'exam')
            ->whereNotNull('exam_id')
            ->get()
            ->each(function ($row) {
                $exam = DB::table('exams')->find($row->exam_id);
                if ($exam) {
                    DB::table('timetables')->where('id', $row->id)->update([
                        'exam_type'  => $exam->type,
                        'subject_id' => $exam->subject_id,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn('exam_type');
        });
    }
};
