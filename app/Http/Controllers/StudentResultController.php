<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Traits\GradeHelper;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    use GradeHelper;
    // Admin/Teacher: list all students with summary
    public function index(Request $request)
    {
        $classes   = SchoolClass::orderBy('name')->get();
        $examTypes = Exam::types();
        $examType  = $request->input('type', '');
        $month     = (int) $request->input('month', 0);

        // Available months — scoped to selected class + type if set
        $availableMonths = collect();
        if ($request->filled('class_id')) {
            $availableMonths = Exam::where('class_id', $request->class_id)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->whereNotNull('exam_date')
                ->selectRaw('MONTH(exam_date) as m')
                ->distinct()
                ->orderByDesc('m')
                ->pluck('m')
                ->map(fn($m) => (int) $m);
        }

        $baseQuery = Student::with(['schoolClass', 'examResults.exam']);

        if ($request->filled('class_id')) {
            $baseQuery->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $baseQuery->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name',  'like', '%' . $request->search . '%');
            });
        }

        $baseStudents = $baseQuery->orderBy('last_name')->orderBy('first_name')->get();

        // Helper to compute a student's summary for a specific month (0 = all)
        $summarise = function ($student, int $forMonth) use ($examType) {
            $results = $student->examResults;
            if ($examType !== '') {
                $results = $results->filter(fn($r) => $r->exam?->type === $examType);
            }
            if ($forMonth > 0) {
                $results = $results->filter(fn($r) =>
                    $r->exam?->exam_date &&
                    (int) date('n', strtotime($r->exam->exam_date)) === $forMonth
                );
            }
            $obtained = $results->sum('marks_obtained');
            $possible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
            $pct      = $possible > 0 ? round(($obtained / $possible) * 100, 1) : null;
            return [
                'count'    => $results->count(),
                'obtained' => $obtained,
                'possible' => $possible,
                'pct'      => $pct,
                'grade'    => self::gradeFromPct($pct),
            ];
        };

        // When a specific month is chosen: flat table (existing behaviour)
        // When "All Months": group into month sections
        $students    = collect();
        $monthGroups = null;

        if ($month > 0 || $availableMonths->isEmpty()) {
            $students = $baseStudents->map(function ($student) use ($month, $summarise) {
                $student->summary = $summarise($student, $month);
                return $student;
            });
        } else {
            // Build one group per available month; sort students by score desc within each
            $monthGroups = $availableMonths->map(function ($m) use ($baseStudents, $summarise) {
                $rows = $baseStudents->map(function ($student) use ($m, $summarise) {
                    $s = clone $student;
                    $s->summary = $summarise($s, $m);
                    return $s;
                })->filter(fn($s) => $s->summary['count'] > 0)
                  ->sortByDesc(fn($s) => $s->summary['pct'] ?? -1)
                  ->values();

                return ['month' => $m, 'students' => $rows];
            })->filter(fn($g) => $g['students']->isNotEmpty())->values();
        }

        return view('admin.student-results.index', compact(
            'students', 'classes', 'examTypes', 'examType', 'month', 'availableMonths', 'monthGroups'
        ));
    }

    // Admin/Teacher: individual student results
    public function show(Student $student, Request $request)
    {
        $student->load('schoolClass');

        $examType = $request->input('type', '');
        $month    = (int) $request->input('month', 0);

        $results = ExamResult::where('student_id', $student->id)
            ->with(['exam.subject', 'exam.schoolClass'])
            ->get()
            ->when($examType !== '', fn($c) => $c->filter(fn($r) => $r->exam?->type === $examType))
            ->when($month > 0, fn($c) => $c->filter(fn($r) =>
                $r->exam?->exam_date &&
                (int) date('n', strtotime($r->exam->exam_date)) === $month
            ))
            ->sortByDesc(fn($r) => $r->exam?->exam_date);

        $grouped  = $results->groupBy(fn($r) => $r->exam?->type ?? 'Other');
        $total    = $results->count();
        $obtained = $results->sum('marks_obtained');
        $possible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
        $pct        = $possible > 0 ? ($obtained / $possible) * 100 : 0;
        $gradeLabel = self::gradeFromPct($pct);
        $summary    = compact('total', 'obtained', 'possible', 'pct', 'gradeLabel');

        return view('admin.student-results.show', compact('student', 'grouped', 'summary', 'examType', 'month'));
    }

    // Student portal: class rankings
    public function classRankings(\Illuminate\Http\Request $request)
    {
        $me = Student::where('email', auth()->user()->email)->with('schoolClass')->first();

        if (! $me) {
            return view('student.rankings', [
                'me' => null, 'rows' => collect(), 'examTypes' => collect(),
                'examType' => '', 'month' => 0, 'availableMonths' => collect(),
                'maxTotal' => 0, 'avgTotal' => 0,
            ]);
        }

        $examTypes = \App\Models\Exam::where('class_id', $me->class_id)->distinct()->orderBy('type')->pluck('type');
        $examType  = $request->input('type', '');
        $month     = $request->input('month', '');

        // Available months for this class/type
        $availableMonths = \App\Models\Exam::where('class_id', $me->class_id)
            ->when($examType !== '', fn($q) => $q->where('type', $examType))
            ->whereNotNull('exam_date')
            ->selectRaw('MONTH(exam_date) as m')
            ->distinct()
            ->orderByDesc('m')
            ->pluck('m')
            ->map(fn($m) => (int) $m);

        if ($availableMonths->isNotEmpty() && $month === '' && ! $request->has('month')) {
            $currentMonth = (int) now()->month;
            $default = $availableMonths->contains($currentMonth)
                ? $currentMonth
                : $availableMonths->first();

            return redirect()->route('student.rankings', array_filter([
                'type'  => $examType,
                'month' => $default,
            ], fn($v) => $v !== ''));
        }

        $month = (int) $month;

        $students = Student::where('class_id', $me->class_id)->get();

        // Helper: build ranked rows for a specific month (0 = all)
        $buildRows = function (int $forMonth) use ($me, $examType, $students) {
            $examIds    = \App\Models\Exam::where('class_id', $me->class_id)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->when($forMonth > 0, fn($q) => $q->whereMonth('exam_date', $forMonth))
                ->pluck('id');
            $maxTotal   = \App\Models\Exam::whereIn('id', $examIds)->sum('total_marks');
            $allResults = ExamResult::whereIn('exam_id', $examIds)->get()->groupBy('student_id');

            $rows = $students->map(function ($student) use ($allResults, $maxTotal) {
                $obtained = $allResults->get($student->id)?->sum('marks_obtained') ?? 0;
                $pct      = $maxTotal > 0 ? ($obtained / $maxTotal) * 100 : 0;
                return ['student' => $student, 'obtained' => $obtained,
                        'pct' => round($pct, 1), 'grade' => self::gradeFromPct($pct)];
            })->sortByDesc('pct')->values();

            $rowsArr = $rows->toArray();
            $rank    = 1;
            foreach ($rowsArr as $i => &$row) {
                $row['rank'] = ($i > 0 && $row['pct'] === $rowsArr[$i - 1]['pct'])
                    ? $rowsArr[$i - 1]['rank'] : $rank;
                $rank++;
            }
            unset($row);
            return ['rows' => collect($rowsArr), 'maxTotal' => $maxTotal,
                    'avgTotal' => collect($rowsArr)->avg('obtained') ?? 0];
        };

        // All months — one section per month
        if ($month === 0 && $availableMonths->isNotEmpty()) {
            $monthGroups = $availableMonths->map(function ($m) use ($buildRows) {
                $data = $buildRows($m);
                return array_merge(['month' => $m], $data);
            })->filter(fn($g) => $g['rows']->isNotEmpty())->values();

            return view('student.rankings', compact(
                'me', 'examTypes', 'examType', 'month', 'availableMonths', 'monthGroups'
            ));
        }

        // Single month
        $data     = $buildRows($month);
        $rows     = $data['rows'];
        $maxTotal = $data['maxTotal'];
        $avgTotal = $data['avgTotal'];

        return view('student.rankings', compact(
            'me', 'rows', 'examTypes', 'examType', 'month', 'availableMonths', 'maxTotal', 'avgTotal'
        ));
    }

    // Student portal: grade cards per exam
    public function myGrades()
    {
        $student = Student::where('email', auth()->user()->email)
            ->with('schoolClass')
            ->first();

        if (! $student) {
            return view('student.grades', ['student' => null, 'results' => collect()]);
        }

        $results = ExamResult::where('student_id', $student->id)
            ->with(['exam.subject'])
            ->get()
            ->sortByDesc(fn($r) => $r->exam?->exam_date);

        return view('student.grades', compact('student', 'results'));
    }

    // Student portal: own results
    public function myResults(\Illuminate\Http\Request $request)
    {
        $student = Student::where('email', auth()->user()->email)
            ->with('schoolClass')
            ->first();

        if (! $student) {
            return view('student.results', ['student' => null, 'grouped' => collect(), 'summary' => [], 'availableMonths' => collect(), 'month' => 0]);
        }

        $month = (int) $request->input('month', 0);

        // Available months for this student
        $availableMonths = Exam::whereIn('id',
                ExamResult::where('student_id', $student->id)->pluck('exam_id')
            )
            ->whereNotNull('exam_date')
            ->selectRaw('MONTH(exam_date) as m')
            ->distinct()
            ->orderByDesc('m')
            ->pluck('m')
            ->map(fn($m) => (int) $m);

        $allResults = ExamResult::where('student_id', $student->id)
            ->with(['exam.subject', 'exam.schoolClass'])
            ->get()
            ->sortByDesc(fn($r) => $r->exam?->exam_date);

        // When "All" and multiple months exist — build one section per month
        $monthGroups = null;
        if ($month === 0 && $availableMonths->count() > 1) {
            $monthGroups = $availableMonths->map(function ($m) use ($allResults) {
                $monthResults = $allResults->filter(fn($r) =>
                    $r->exam?->exam_date &&
                    (int) date('n', strtotime($r->exam->exam_date)) === $m
                );
                $obtained = $monthResults->sum('marks_obtained');
                $possible = $monthResults->sum(fn($r) => $r->exam?->total_marks ?? 0);
                $pct      = $possible > 0 ? ($obtained / $possible) * 100 : 0;
                return [
                    'month'   => $m,
                    'grouped' => $monthResults->groupBy(fn($r) => $r->exam?->type ?? 'Other'),
                    'summary' => [
                        'total'      => $monthResults->count(),
                        'obtained'   => $obtained,
                        'possible'   => $possible,
                        'pct'        => $pct,
                        'gradeLabel' => self::gradeFromPct($pct),
                    ],
                ];
            })->filter(fn($g) => $g['summary']['total'] > 0)->values();
        }

        $results    = $allResults->when($month > 0, fn($c) => $c->filter(fn($r) =>
            $r->exam?->exam_date &&
            (int) date('n', strtotime($r->exam->exam_date)) === $month
        ));
        $grouped  = $results->groupBy(fn($r) => $r->exam?->type ?? 'Other');
        $total    = $results->count();
        $obtained = $results->sum('marks_obtained');
        $possible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
        $pct        = $possible > 0 ? ($obtained / $possible) * 100 : 0;
        $gradeLabel = self::gradeFromPct($pct);
        $summary    = compact('total', 'obtained', 'possible', 'pct', 'gradeLabel');

        return view('student.results', compact('student', 'grouped', 'summary', 'availableMonths', 'month', 'monthGroups'));
    }
}
