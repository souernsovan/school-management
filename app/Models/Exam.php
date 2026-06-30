<?php

namespace App\Models;

use App\Models\ExamType;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'type',
        'exam_date',
        'total_marks',
        'description',
    ];

    protected $casts = [
        'exam_date'   => 'date',
        'total_marks' => 'decimal:2',
    ];

    public static function types(): array
    {
        try {
            return ExamType::orderBy('sort_order')->orderBy('name')->pluck('name')->toArray();
        } catch (\Throwable) {
            return ['Class Test', 'Monthly Test', 'Mid-Year Exam', 'Final Exam', 'Quiz'];
        }
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'exam_results');
    }
}