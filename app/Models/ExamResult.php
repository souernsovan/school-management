<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
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

        return match (true) {
            $pct >= 95 => 'A+',
            $pct >= 90 => 'A',
            $pct >= 85 => 'B+',
            $pct >= 80 => 'B',
            $pct >= 70 => 'C',
            $pct >= 60 => 'D',
            $pct >= 50 => 'E',
            default    => 'F',
        };
    }

    public function getGradeColorAttribute(): string
    {
        return match ($this->grade) {
            'A+', 'A' => 'emerald',
            'B+', 'B' => 'blue',
            'C'       => 'teal',
            'D'       => 'yellow',
            'E'       => 'orange',
            'F'       => 'red',
            default   => 'slate',
        };
    }
}
