<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Timetable;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function index(Request $request)
    {
        $classes = SchoolClass::all();
        $days    = self::DAYS;

        // Default to first class if none selected
        $classId = $request->filled('class_id')
            ? $request->class_id
            : optional($classes->first())->id;

        $selectedClass = $classId ? SchoolClass::find($classId) : null;

        // Week navigation — default to current week (Monday)
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $entries = $classId
            ? Timetable::with(['subject', 'teacher', 'exam.subject', 'schoolClass'])
                ->where('class_id', $classId)
                ->where(function ($q) use ($weekStart, $weekEnd) {
                    // Show entries that have no specific date (recurring) OR fall in this week
                    $q->whereNull('entry_date')
                      ->orWhereBetween('entry_date', [
                          $weekStart->toDateString(),
                          $weekEnd->toDateString(),
                      ]);
                })
                ->orderBy('start_time')
                ->get()
            : collect();

        $todayWeek     = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $isCurrentWeek = $weekStart->toDateString() === $todayWeek->toDateString();

        $navParams = ['class_id' => $classId];
        $prevWeek  = array_merge($navParams, ['week_start' => $weekStart->copy()->subWeek()->toDateString()]);
        $nextWeek  = array_merge($navParams, ['week_start' => $weekStart->copy()->addWeek()->toDateString()]);
        $prevMonth = array_merge($navParams, ['week_start' => $weekStart->copy()->subWeeks(4)->toDateString()]);
        $nextMonth = array_merge($navParams, ['week_start' => $weekStart->copy()->addWeeks(4)->toDateString()]);
        $prevYear  = array_merge($navParams, ['week_start' => $weekStart->copy()->subWeeks(52)->toDateString()]);
        $nextYear  = array_merge($navParams, ['week_start' => $weekStart->copy()->addWeeks(52)->toDateString()]);
        $thisWeek  = array_merge($navParams, ['week_start' => $todayWeek->toDateString()]);

        return view('admin.timetables.index', compact(
            'classes', 'days', 'entries', 'selectedClass', 'classId',
            'weekStart', 'weekEnd', 'isCurrentWeek',
            'prevWeek', 'nextWeek', 'prevMonth', 'nextMonth', 'prevYear', 'nextYear', 'thisWeek'
        ));
    }

    public function create(Request $request)
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        $exams    = Exam::with('subject')->orderBy('exam_date')->get();
        $days     = self::DAYS;

        // Pre-fill the week if coming from the timetable index
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        // Build list of selectable weeks: past 4 weeks + next 8 weeks
        $weeks = [];
        for ($i = -4; $i <= 8; $i++) {
            $ws = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($i);
            $weeks[] = [
                'value' => $ws->toDateString(),
                'label' => $ws->format('M j') . ' – ' . $ws->copy()->addDays(6)->format('M j, Y')
                    . ($i === 0 ? ' (this week)' : ($i === 1 ? ' (next week)' : ($i === -1 ? ' (last week)' : ''))),
            ];
        }

        return view('admin.timetables.create', compact('classes', 'subjects', 'teachers', 'exams', 'days', 'weekStart', 'weeks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'entry_type'  => 'required|in:class,break,exam',
            'title'       => 'required_if:entry_type,break|nullable|string|max:100',
            'subject_id'  => 'required_if:entry_type,class|nullable|exists:subjects,id',
            'teacher_id'  => 'nullable|exists:teachers,id',
            'exam_type'   => 'required_if:entry_type,exam|nullable|string|max:100',
            'total_marks' => 'required_if:entry_type,exam|nullable|numeric|min:1|max:1000',
            'exam_id'     => 'nullable|exists:exams,id',
            'day'         => 'required|in:' . implode(',', self::DAYS),
            'start_time'  => 'required',
            'end_time'    => 'required|after:start_time',
        ]);

        $data = $request->only('class_id', 'day', 'start_time', 'end_time');
        $data['entry_type'] = $request->entry_type;

        // entry_date: null = recurring every week, date = specific week only
        $data['entry_date'] = $request->recurring === '1'
            ? null
            : ($request->filled('entry_date') ? Carbon::parse($request->entry_date)->startOfWeek(Carbon::MONDAY)->toDateString() : null);

        if ($request->entry_type === 'break') {
            $data['title']      = $request->title;
            $data['subject_id'] = null;
            $data['teacher_id'] = null;
            $data['exam_id']    = null;
        } elseif ($request->entry_type === 'exam') {
            $data['exam_type']  = $request->exam_type;
            $data['subject_id'] = $request->subject_id ?: null;
            $data['title']      = null;
            $data['teacher_id'] = null;

            // Use the selected entry_date week for the exam, or fall back to nearest matching day
            if ($data['entry_date']) {
                $examDate = Carbon::parse($data['entry_date'])->next($request->day);
            } else {
                $examDate = Carbon::parse('this ' . $request->day);
                if ($examDate->isPast()) {
                    $examDate = Carbon::parse('next ' . $request->day);
                }
            }

            // Auto-create exam record
            $exam = Exam::create([
                'class_id'    => $request->class_id,
                'subject_id'  => $request->subject_id ?: null,
                'type'        => $request->exam_type,
                'exam_date'   => $examDate->toDateString(),
                'total_marks' => $request->total_marks,
            ]);
            $data['exam_id'] = $exam->id;
        } else {
            $data['title']      = null;
            $data['subject_id'] = $request->subject_id;
            $data['teacher_id'] = $request->teacher_id;
            $data['exam_id']    = null;
        }

        Timetable::create($data);

        return redirect()->route('timetables.index', ['class_id' => $data['class_id']])->with('success', 'Timetable entry created successfully.');
    }

    public function edit(Timetable $timetable)
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        $exams    = Exam::with('subject')->orderBy('exam_date')->get();
        $days     = self::DAYS;

        $weeks = [];
        for ($i = -4; $i <= 8; $i++) {
            $ws = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($i);
            $weeks[] = [
                'value' => $ws->toDateString(),
                'label' => $ws->format('M j') . ' – ' . $ws->copy()->addDays(6)->format('M j, Y')
                    . ($i === 0 ? ' (this week)' : ($i === 1 ? ' (next week)' : ($i === -1 ? ' (last week)' : ''))),
            ];
        }

        return view('admin.timetables.edit', compact('timetable', 'classes', 'subjects', 'teachers', 'exams', 'days', 'weeks'));
    }

    public function update(Request $request, Timetable $timetable)
    {
        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'entry_type'  => 'required|in:class,break,exam',
            'title'       => 'required_if:entry_type,break|nullable|string|max:100',
            'subject_id'  => 'required_if:entry_type,class|nullable|exists:subjects,id',
            'teacher_id'  => 'nullable|exists:teachers,id',
            'exam_type'   => 'required_if:entry_type,exam|nullable|string|max:100',
            'total_marks' => 'required_if:entry_type,exam|nullable|numeric|min:1|max:1000',
            'exam_id'     => 'nullable|exists:exams,id',
            'day'         => 'required|in:' . implode(',', self::DAYS),
            'start_time'  => 'required',
            'end_time'    => 'required|after:start_time',
        ]);

        $data = $request->only('class_id', 'day', 'start_time', 'end_time');
        $data['entry_type'] = $request->entry_type;

        $data['entry_date'] = $request->recurring === '1'
            ? null
            : ($request->filled('entry_date') ? Carbon::parse($request->entry_date)->startOfWeek(Carbon::MONDAY)->toDateString() : null);

        if ($request->entry_type === 'break') {
            $data['title']      = $request->title;
            $data['subject_id'] = null;
            $data['teacher_id'] = null;
            $data['exam_id']    = null;
        } elseif ($request->entry_type === 'exam') {
            $data['exam_type']  = $request->exam_type;
            $data['subject_id'] = $request->subject_id ?: null;
            $data['title']      = null;
            $data['teacher_id'] = null;

            if ($data['entry_date']) {
                $examDate = Carbon::parse($data['entry_date'])->next($request->day);
            } else {
                $examDate = Carbon::parse('this ' . $request->day);
                if ($examDate->isPast()) {
                    $examDate = Carbon::parse('next ' . $request->day);
                }
            }

            // Update or create the linked exam record
            if ($timetable->exam_id) {
                Exam::where('id', $timetable->exam_id)->update([
                    'class_id'    => $request->class_id,
                    'subject_id'  => $request->subject_id ?: null,
                    'type'        => $request->exam_type,
                    'exam_date'   => $examDate->toDateString(),
                    'total_marks' => $request->total_marks,
                ]);
                $data['exam_id'] = $timetable->exam_id;
            } else {
                $exam = Exam::create([
                    'class_id'    => $request->class_id,
                    'subject_id'  => $request->subject_id ?: null,
                    'type'        => $request->exam_type,
                    'exam_date'   => $examDate->toDateString(),
                    'total_marks' => $request->total_marks,
                ]);
                $data['exam_id'] = $exam->id;
            }
        } else {
            $data['title']      = null;
            $data['subject_id'] = $request->subject_id;
            $data['teacher_id'] = $request->teacher_id;
            $data['exam_id']    = null;
        }

        $timetable->update($data);

        return redirect()->route('timetables.index', ['class_id' => $data['class_id']])->with('success', 'Timetable entry updated successfully.');
    }

    public function destroy(Timetable $timetable)
    {
        $timetable->delete();
        return redirect()->route('timetables.index')->with('success', 'Timetable entry deleted successfully.');
    }
}
