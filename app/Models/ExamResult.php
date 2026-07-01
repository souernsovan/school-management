<?php

namespace App\Models;

use App\Traits\GradeHelper;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use GradeHelper;
    protected $fillable = [
        'exam_id',
        'student_id',
        'marks_obtained',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function getGradeAttribute(): string
    {
        $total = $this->exam?->total_marks ?? 100;
        if ($total <= 0) return 'N/A';
        $pct = ($this->marks_obtained / $total) * 100;
        return self::gradeFromPct($pct);
    }

    public function getGradeColorAttribute(): string
    {
        return self::gradeColor($this->grade);
    }
}
