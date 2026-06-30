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

        $entries = $classId
            ? Timetable::with(['subject', 'teacher', 'exam.subject'])
                ->where('class_id', $classId)
                ->orderBy('start_time')
                ->get()
            : collect();

        return view('admin.timetables.index', compact('classes', 'days', 'entries', 'selectedClass', 'classId'));
    }

    public function create()
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        $exams    = Exam::with('subject')->orderBy('exam_date')->get();
        $days     = self::DAYS;

        return view('admin.timetables.create', compact('classes', 'subjects', 'teachers', 'exams', 'days'));
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
            'room'        => 'nullable|string|max:50',
        ]);

        $data = $request->only('class_id', 'day', 'start_time', 'end_time', 'room');
        $data['entry_type'] = $request->entry_type;
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

            // Calculate exam_date from the selected day (this week if not yet passed, next week otherwise)
            $examDate = Carbon::parse('this ' . $request->day);
            if ($examDate->isPast()) {
                $examDate = Carbon::parse('next ' . $request->day);
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

        return view('admin.timetables.edit', compact('timetable', 'classes', 'subjects', 'teachers', 'exams', 'days'));
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
            'room'        => 'nullable|string|max:50',
        ]);

        $data = $request->only('class_id', 'day', 'start_time', 'end_time', 'room');
        $data['entry_type'] = $request->entry_type;
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

            // Calculate exam_date from the selected day (this week if not yet passed, next week otherwise)
            $examDate = Carbon::parse('this ' . $request->day);
            if ($examDate->isPast()) {
                $examDate = Carbon::parse('next ' . $request->day);
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
