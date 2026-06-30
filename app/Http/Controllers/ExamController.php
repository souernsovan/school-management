<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $today = now()->toDateString();

        $exams = Exam::with(['schoolClass', 'subject'])
            ->withCount('results')
            ->when($request->filled('class_id'),   fn($q) => $q->where('class_id',   $request->class_id))
            ->when($request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->filled('type'),       fn($q) => $q->where('type',        $request->type))
            ->when($request->status === 'upcoming', fn($q) => $q->where('exam_date', '>=', $today))
            ->when($request->status === 'past',     fn($q) => $q->where('exam_date', '<',  $today))
            ->orderBy('exam_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.exams.index', compact('exams', 'classes', 'subjects'));
    }

    public function create()
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        return view('admin.exams.create', compact('classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'type'        => 'required|in:' . implode(',', Exam::types()),
            'exam_date'   => 'required|date',
            'total_marks' => 'required|numeric|min:1|max:1000',
            'description' => 'nullable|string|max:500',
        ]);

        $exam = Exam::create($data);

        return redirect()->route('exams.show', $exam)
            ->with('success', 'Exam created. You can now enter student marks.');
    }

    public function show(Exam $exam)
    {
        $exam->load(['schoolClass', 'subject', 'results.student', 'results.exam']);

        $students   = $exam->schoolClass
            ? $exam->schoolClass->students()->orderBy('first_name')->get()
            : collect();

        $resultsMap = $exam->results->keyBy('student_id');

        $stats = [
            'entered'  => $exam->results->count(),
            'total'    => $students->count(),
            'average'  => $exam->results->avg('marks_obtained'),
            'highest'  => $exam->results->max('marks_obtained'),
            'lowest'   => $exam->results->min('marks_obtained'),
            'passed'   => $exam->results->filter(fn($r) => $exam->total_marks > 0 && ($r->marks_obtained / $exam->total_marks) >= 0.4)->count(),
        ];

        return view('admin.exams.show', compact('exam', 'students', 'resultsMap', 'stats'));
    }

    public function edit(Exam $exam)
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        return view('admin.exams.edit', compact('exam', 'classes', 'subjects'));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'type'        => 'required|in:' . implode(',', Exam::types()),
            'exam_date'   => 'required|date',
            'total_marks' => 'required|numeric|min:1|max:1000',
            'description' => 'nullable|string|max:500',
        ]);

        $exam->update($data);

        return redirect()->route('exams.show', $exam)
            ->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('exams.index')
            ->with('success', 'Exam deleted successfully.');
    }

    public function saveResults(Request $request, Exam $exam)
    {
        $request->validate([
            'results'                    => 'required|array',
            'results.*.student_id'       => 'required|exists:students,id',
            'results.*.marks_obtained'   => 'nullable|numeric|min:0|max:' . $exam->total_marks,
        ]);

        foreach ($request->results as $row) {
            if ($row['marks_obtained'] !== null && $row['marks_obtained'] !== '') {
                ExamResult::updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $row['student_id']],
                    ['marks_obtained' => $row['marks_obtained']]
                );
            }
        }

        return redirect()->route('exams.show', $exam)
            ->with('success', 'Results saved successfully.');
    }
}
