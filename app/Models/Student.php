<?php

namespace App\Models;

use App\Traits\GradeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, GradeHelper;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'dob',
        'gender',
        'phone',
        'address',
        'class_id',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * Attendance rate as a percentage (0–100).
     * "Present" and "Late" both count as attended.
     */
    public function attendanceRate(): float
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0.0;
        $attended = $this->attendances()->whereIn('status', ['Present', 'Late'])->count();
        return round(($attended / $total) * 100, 1);
    }

    /**
     * Academic performance summary across all exams.
     */
    public function performanceSummary(): array
    {
        $results  = $this->examResults()->with('exam')->get();
        $obtained = $results->sum('marks_obtained');
        $possible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
        $pct      = $possible > 0 ? round(($obtained / $possible) * 100, 1) : null;

        return [
            'count'    => $results->count(),
            'obtained' => $obtained,
            'possible' => $possible,
            'pct'      => $pct,
            'grade'    => self::gradeFromPct($pct),
        ];
    }
}
