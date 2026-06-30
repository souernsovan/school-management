<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Exam;

class Timetable extends Model
{
    protected $fillable = [
        'class_id',
        'entry_type',
        'exam_type',
        'title',
        'subject_id',
        'teacher_id',
        'exam_id',
        'day',
        'start_time',
        'end_time',
        'room',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
