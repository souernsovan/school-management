<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Traits\ExportsToExcel;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    use ExportsToExcel;
    public function index(Request $request)
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $today    = now()->toDateString();
        $classId  = $request->input('class_id', '');
        $examType = $request->input('type', '');
        $month    = $request->input('month', '');
        $status   = $request->input('status', '');

        // When a class is selected, compute available months and auto-redirect to current month
        $availableMonths = collect();
        if ($classId) {
            $availableMonths = Exam::where('class_id', $classId)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->whereNotNull('exam_date')
                ->selectRaw('MONTH(exam_date) as m')
                ->distinct()
                ->orderByDesc('m')
                ->pluck('m')
                ->map(fn($m) => (int) $m);

        }

        $exams = Exam::with(['schoolClass', 'subject'])
            ->withCount('results')
            ->when($classId !== '',                fn($q) => $q->where('class_id',   $classId))
            ->when($request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($examType !== '',               fn($q) => $q->where('type',        $examType))
            ->when($month !== '',                  fn($q) => $q->whereMonth('exam_date', (int) $month))
            ->when($status === 'upcoming',         fn($q) => $q->where('exam_date', '>=', $today))
            ->when($status === 'past',             fn($q) => $q->where('exam_date', '<',  $today))
            ->orderBy('exam_date', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('admin.exams.index', compact(
            'exams', 'classes', 'subjects',
            'availableMonths', 'classId', 'examType', 'month', 'status'
        ));
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

    public function exportResultsCsv(Exam $exam): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $exam->load(['schoolClass', 'subject', 'results.student']);

        $students   = $exam->schoolClass
            ? $exam->schoolClass->students()->orderBy('first_name')->get()
            : collect();
        $resultsMap = $exam->results->keyBy('student_id');

        $rows = $students->map(function ($s) use ($exam, $resultsMap) {
            $result = $resultsMap->get($s->id);
            $marks  = $result?->marks_obtained ?? '';
            $pct    = ($marks !== '' && $exam->total_marks > 0) ? round($marks / $exam->total_marks * 100, 1) : '';
            $grade  = '';
            if ($pct !== '') {
                $grade = match(true) {
                    $pct >= 90 => 'A+', $pct >= 80 => 'A', $pct >= 70 => 'B',
                    $pct >= 60 => 'C', $pct >= 50 => 'D', default => 'F',
                };
            }
            return [
                $s->first_name . ' ' . $s->last_name,
                $exam->schoolClass->name ?? '',
                $exam->subject->name ?? '',
                $exam->type,
                $exam->exam_date->format('d M Y'),
                $exam->total_marks,
                $marks,
                $pct !== '' ? $pct . '%' : '',
                $grade,
            ];
        });

        return $this->xlsResponse(
            'Exam Results — ' . ($exam->subject->name ?? 'Exam') . ' (' . $exam->type . ')',
            ['Student', 'Class', 'Subject', 'Exam Type', 'Date', 'Total Marks', 'Marks Obtained', 'Percentage', 'Grade'],
            $rows,
            'exam_results_' . $exam->id
        );
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
