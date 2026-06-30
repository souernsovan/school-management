<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Http\Request;

class StudentTimetableController extends Controller
{
    const DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();

        // Auto-detect the student's class from their linked record
        $myStudent = Student::where('email', auth()->user()?->email ?? '')->first();
        $myClassId = $myStudent?->class_id;

        $classId = $request->filled('class_id')
            ? (int) $request->class_id
            : ($myClassId ?? optional($classes->first())->id);

        $selectedClass = $classId ? SchoolClass::find($classId) : null;

        $entries = $classId
            ? Timetable::with(['subject', 'teacher', 'exam.subject'])
                ->where('class_id', $classId)
                ->orderBy('start_time')
                ->get()
            : collect();

        $days = self::DAYS;

        return view('student.timetable', compact(
            'classes', 'days', 'entries', 'selectedClass', 'classId', 'myClassId'
        ));
    }
}
