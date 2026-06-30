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
        Schema::create('attendances', function (Blueprint $table) {

            $table->id();

            // Student
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            // Teacher who took attendance
            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            // Class
            $table->foreignId('class_id')
                ->constrained('school_classes')
                ->cascadeOnDelete();

            // Subject (optional)
            $table->foreignId('subject_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Attendance Date
            $table->date('attendance_date');

            // Attendance Status
            $table->enum('status', [
                'Present',
                'Absent',
                'Late',
                'Permission'
            ])->default('Present');

            // Optional Remark
            $table->text('remark')->nullable();

            $table->timestamps();

            // Prevent duplicate attendance for the same student on the same date and subject
            $table->unique([
                'student_id',
                'attendance_date',
                'subject_id'
            ], 'attendance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
