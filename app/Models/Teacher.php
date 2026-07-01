<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;

class Teacher extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'dob',
        'qualification',
        'specialization',
        'hire_date',
        'email',
        'phone',
        'address',
        'status',
    ];

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /** Distinct subjects this teacher is assigned to via timetables. */
    public function subjects()
    {
        return Subject::whereIn('id',
            $this->timetables()->whereNotNull('subject_id')->pluck('subject_id')->unique()
        )->get();
    }

    /** Distinct classes this teacher teaches. */
    public function classes()
    {
        return SchoolClass::whereIn('id',
            $this->timetables()->whereNotNull('class_id')->pluck('class_id')->unique()
        )->get();
    }

    /** All students in classes taught by this teacher. */
    public function students()
    {
        $classIds = $this->timetables()->whereNotNull('class_id')->pluck('class_id')->unique();
        return Student::whereIn('class_id', $classIds)->get();
    }
}