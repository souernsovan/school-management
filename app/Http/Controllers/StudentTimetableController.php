<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Timetable;
use Carbon\Carbon;
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

        // Week navigation
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $entries = $classId
            ? Timetable::with(['subject', 'teacher', 'exam.subject', 'schoolClass'])
                ->where('class_id', $classId)
                ->where(function ($q) use ($weekStart, $weekEnd) {
                    $q->whereNull('entry_date')
                      ->orWhereBetween('entry_date', [
                          $weekStart->toDateString(),
                          $weekEnd->toDateString(),
                      ]);
                })
                ->orderBy('start_time')
                ->get()
            : collect();

        $days = self::DAYS;

        $todayWeek     = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $isCurrentWeek = $weekStart->toDateString() === $todayWeek->toDateString();

        $navParams = ['class_id' => $classId];
        $prevWeek  = array_merge($navParams, ['week_start' => $weekStart->copy()->subWeek()->toDateString()]);
        $nextWeek  = array_merge($navParams, ['week_start' => $weekStart->copy()->addWeek()->toDateString()]);
        $prevMonth = array_merge($navParams, ['week_start' => $weekStart->copy()->subWeeks(4)->toDateString()]);
        $nextMonth = array_merge($navParams, ['week_start' => $weekStart->copy()->addWeeks(4)->toDateString()]);
        $thisWeek  = array_merge($navParams, ['week_start' => $todayWeek->toDateString()]);

        return view('student.timetable', compact(
            'classes', 'days', 'entries', 'selectedClass', 'classId', 'myClassId',
            'weekStart', 'weekEnd', 'isCurrentWeek',
            'prevWeek', 'nextWeek', 'prevMonth', 'nextMonth', 'thisWeek'
        ));
    }

    public function announcements()
    {
        $announcements = Announcement::with('author')
            ->active()
            ->forUser(auth()->user())
            ->orderByDesc('pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('student.announcements', compact('announcements'));
    }
}
