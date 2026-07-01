<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class ScoreEntryController extends Controller
{
    public function index(Request $request)
    {
        $classes   = SchoolClass::orderBy('name')->get();
        $examTypes = Exam::types();

        $classId  = $request->input('class_id');
        $examType = $request->input('type', '');
        $month    = $request->input('month', '');

        $availableMonths = collect();
        $exams           = collect();
        $students        = collect();
        $results         = collect();

        if ($classId) {
            // Months that actually have exams for this class/type (cast to int for reliable comparison)
            $availableMonths = Exam::where('class_id', $classId)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->whereNotNull('exam_date')
                ->selectRaw('MONTH(exam_date) as m')
                ->distinct()
                ->orderByDesc('m')
                ->pluck('m')
                ->map(fn($m) => (int) $m);

            if ($availableMonths->isNotEmpty()) {
                $currentMonth = (int) now()->month;
                $monthInt     = (int) $month;

                // Redirect to set month in URL when missing or no longer valid for this class/type
                if ($month === '' || ! $availableMonths->contains($monthInt)) {
                    $default = $availableMonths->contains($currentMonth)
                        ? $currentMonth
                        : $availableMonths->first();

                    return redirect()->route('score-entry.index', array_filter([
                        'class_id' => $classId,
                        'type'     => $examType,
                        'month'    => $default,
                    ], fn($v) => $v !== ''));
                }
            }

            $month = (int) $month;

            $exams = Exam::with('subject')
                ->where('class_id', $classId)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->when($month > 0, fn($q) => $q->whereMonth('exam_date', $month))
                ->orderBy('subject_id')
                ->orderBy('exam_date')
                ->get();

            $students = Student::where('class_id', $classId)
                ->orderBy('first_name')
                ->get();

            $results = ExamResult::whereIn('exam_id', $exams->pluck('id'))
                ->get()
                ->groupBy('student_id')
                ->map(fn($rows) => $rows->keyBy('exam_id'));
        }

        return view('admin.score-entry.index', compact(
            'classes', 'examTypes', 'classId', 'examType', 'month',
            'availableMonths', 'exams', 'students', 'results'
        ));
    }

    public function store(Request $request)
    {
        $scores = $request->input('scores', []);

        foreach ($scores as $studentId => $examScores) {
            foreach ($examScores as $examId => $marks) {
                if ($marks === null || $marks === '') {
                    ExamResult::where('student_id', $studentId)
                        ->where('exam_id', $examId)
                        ->delete();
                    continue;
                }

                $exam = Exam::find($examId);
                if (! $exam) continue;

                $marks = max(0, min((float) $marks, $exam->total_marks));

                ExamResult::updateOrCreate(
                    ['student_id' => $studentId, 'exam_id' => $examId],
                    ['marks_obtained' => $marks]
                );
            }
        }

        return back()->with('success', 'Scores saved successfully.');
    }
}
