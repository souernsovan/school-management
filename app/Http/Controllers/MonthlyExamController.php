<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Traits\GradeHelper;
use Illuminate\Http\Request;

class MonthlyExamController extends Controller
{
    use GradeHelper;

    public function index(Request $request)
    {
        $classes   = SchoolClass::orderBy('name')->get();
        $examTypes = Exam::types();

        $years = Exam::selectRaw('YEAR(exam_date) as y')
            ->whereNotNull('exam_date')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y')
            ->toArray();

        if (empty($years)) {
            $years = [now()->year];
        }

        $classId       = $request->input('class_id');
        $year          = (int) $request->input('year', $years[0] ?? now()->year);
        $examType      = $request->input('type', '');
        $selectedMonth = (int) $request->input('month', 0);

        // Auto-select first class when none chosen
        if (! $classId && $classes->isNotEmpty()) {
            return redirect()->route('monthly-exam.index', [
                'class_id' => $classes->first()->id,
                'year'     => $year,
                'type'     => $examType,
            ]);
        }

        $months   = collect();
        $students = collect();

        if ($classId) {
            $students = Student::where('class_id', $classId)
                ->orderBy('first_name')
                ->get();

            $exams = Exam::with('subject')
                ->where('class_id', $classId)
                ->whereYear('exam_date', $year)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->when($selectedMonth > 0, fn($q) => $q->whereMonth('exam_date', $selectedMonth))
                ->orderBy('exam_date')
                ->get();

            $examsByMonth = $exams->groupBy(fn($e) => (int) date('n', strtotime($e->exam_date)));

            $allResults = ExamResult::whereIn('exam_id', $exams->pluck('id'))
                ->get()
                ->groupBy('student_id')
                ->map(fn($rows) => $rows->keyBy('exam_id'));

            foreach (range(1, 12) as $m) {
                $monthExams = $examsByMonth->get($m, collect());
                if ($monthExams->isEmpty()) continue;

                $maxTotal = $monthExams->sum('total_marks');

                $studentRows = $students->map(function ($student) use ($monthExams, $allResults, $maxTotal) {
                    $sr       = $allResults->get($student->id, collect());
                    $obtained = 0;
                    $hasScore = false;
                    foreach ($monthExams as $exam) {
                        $r = $sr->get($exam->id);
                        if ($r) { $obtained += $r->marks_obtained; $hasScore = true; }
                    }
                    $pct = ($maxTotal > 0 && $hasScore) ? round($obtained / $maxTotal * 100, 1) : null;
                    return [
                        'student'  => $student,
                        'obtained' => $hasScore ? $obtained : null,
                        'pct'      => $pct,
                        'grade'    => self::gradeFromPct($pct),
                    ];
                })->sortByDesc('pct')->values();

                $months->push([
                    'month'       => $m,
                    'name'        => date('F', mktime(0, 0, 0, $m, 1)),
                    'short'       => date('M', mktime(0, 0, 0, $m, 1)),
                    'exams'       => $monthExams,
                    'maxTotal'    => $maxTotal,
                    'studentRows' => $studentRows,
                    'avgPct'      => round($studentRows->whereNotNull('pct')->avg('pct') ?? 0, 1),
                    'passCount'   => $studentRows->whereNotNull('pct')->where('pct', '>=', 50)->count(),
                ]);
            }
        }

        $months = $months->reverse()->values();

        // All months that have exams (for chips), independent of selectedMonth filter
        $availableMonths = collect();
        if ($classId) {
            $availableMonths = Exam::where('class_id', $classId)
                ->whereYear('exam_date', $year)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->whereNotNull('exam_date')
                ->selectRaw('MONTH(exam_date) as m')
                ->distinct()
                ->orderByDesc('m')
                ->pluck('m')
                ->map(fn($m) => (int) $m);
        }

        return view('admin.monthly-exam.index', compact(
            'classes', 'examTypes', 'years', 'classId', 'year', 'examType',
            'months', 'students', 'selectedMonth', 'availableMonths'
        ));
    }
}
