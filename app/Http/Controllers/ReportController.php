<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Traits\GradeHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    use GradeHelper;
    // Shared data-building logic
    private function buildReport(Request $request): array
    {
        $class     = SchoolClass::findOrFail($request->class_id);
        $students  = $class->students()->orderBy('first_name')->get();
        $firstType = Exam::types()[0] ?? '';
        $examType  = $request->input('type', $firstType);
        $month     = (int) $request->input('month', 0);

        $exams = Exam::with('subject')
            ->where('class_id', $class->id)
            ->where('type', $examType)
            ->when($month > 0, fn($q) => $q->whereMonth('exam_date', $month))
            ->orderBy('subject_id')
            ->orderBy('exam_date', 'desc')
            ->get()
            ->unique('subject_id');

        $allResults = ExamResult::whereIn('exam_id', $exams->pluck('id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->keyBy('exam_id'));

        // Pre-compute totals + ranks
        $grandTotal    = $exams->sum('total_marks');
        $studentTotals = $students->map(function ($student) use ($exams, $allResults) {
            $obtained = 0;
            foreach ($exams as $exam) {
                $result = $allResults->get($student->id)?->get($exam->id);
                if ($result) $obtained += $result->marks_obtained;
            }
            return ['student_id' => $student->id, 'obtained' => $obtained];
        })->sortByDesc('obtained')->values();

        $rankMap = $studentTotals->mapWithKeys(fn($row, $i) => [$row['student_id'] => $i + 1]);

        $students = $students->sortBy(fn($s) => $rankMap[$s->id] ?? 999)->values();

        return compact('class', 'students', 'exams', 'allResults', 'examType', 'month', 'grandTotal', 'rankMap');
    }

    public function index(Request $request)
    {
        $classes         = SchoolClass::orderBy('name')->get();
        $examTypes       = Exam::types();
        $availableMonths = collect();

        if (! $request->filled('class_id')) {
            return view('admin.reports.index', compact('classes', 'examTypes', 'availableMonths'));
        }

        $examType = $request->input('type', $examTypes[0] ?? '');
        $month    = $request->input('month', '');

        $availableMonths = Exam::where('class_id', $request->class_id)
            ->when($examType !== '', fn($q) => $q->where('type', $examType))
            ->whereNotNull('exam_date')
            ->selectRaw('MONTH(exam_date) as m')
            ->distinct()
            ->orderBy('m')
            ->pluck('m')
            ->map(fn($m) => (int) $m);

        if ($availableMonths->isNotEmpty()) {
            $currentMonth = (int) now()->month;
            $monthInt     = (int) $month;

            if ($month === '' || ! $availableMonths->contains($monthInt)) {
                $default = $availableMonths->contains($currentMonth)
                    ? $currentMonth
                    : $availableMonths->first();

                return redirect()->route('reports.index', array_filter([
                    'class_id' => $request->class_id,
                    'type'     => $examType,
                    'month'    => $default,
                ], fn($v) => $v !== ''));
            }
        }

        $data = $this->buildReport($request);

        return view('admin.reports.index', array_merge($data, compact('classes', 'examTypes', 'availableMonths')));
    }

    public function exportCsv(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:school_classes,id']);
        $data = $this->buildReport($request);
        extract($data);

        $filename = 'report_' . str($class->name)->slug() . '_' . str($examType)->slug() . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($class, $students, $exams, $allResults, $examType, $grandTotal, $rankMap) {
            $fh = fopen('php://output', 'w');

            // Title
            fputcsv($fh, [$class->name . ' – ' . $class->section . ' | ' . $examType . ' Report']);
            fputcsv($fh, []);

            // Header row
            $header = ['Rank', 'Student'];
            foreach ($exams as $exam) {
                $header[] = ($exam->subject->name ?? '—') . ' /' . $exam->total_marks;
            }
            $header[] = 'Total /' . $grandTotal;
            $header[] = 'Percentage';
            $header[] = 'Grade';
            fputcsv($fh, $header);

            foreach ($students as $student) {
                $obtained = 0;
                $row      = [$rankMap[$student->id] ?? '—', $student->first_name . ' ' . $student->last_name];

                foreach ($exams as $exam) {
                    $result = $allResults->get($student->id)?->get($exam->id);
                    if ($result) {
                        $row[]    = $result->marks_obtained;
                        $obtained += $result->marks_obtained;
                    } else {
                        $row[] = '';
                    }
                }

                $pct   = $grandTotal > 0 ? round(($obtained / $grandTotal) * 100, 1) : 0;
                $grade = $obtained > 0 ? $this->gradeLabel($pct) : '—';
                $row[] = $obtained > 0 ? $obtained : '—';
                $row[] = $obtained > 0 ? $pct . '%' : '—';
                $row[] = $grade;
                fputcsv($fh, $row);
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:school_classes,id']);
        $data = $this->buildReport($request);

        $gradeInfo = function (float $pct): array {
            $label = self::gradeFromPct($pct);
            $color = match ($label) {
                'A+', 'A' => '#059669',
                'B+', 'B' => '#2563eb',
                'C'       => '#0d9488',
                'D'       => '#d97706',
                'E'       => '#ea580c',
                default   => '#dc2626',
            };
            return ['label' => $label, 'color' => $color];
        };

        $pdf = Pdf::loadView('admin.reports.pdf', array_merge($data, compact('gradeInfo')))
            ->setPaper('a4', 'landscape');

        $filename = 'report_' . str($data['class']->name)->slug() . '_' . str($data['examType'])->slug() . '.pdf';

        return $pdf->download($filename);
    }

    public function rankings(Request $request)
    {
        $classes   = SchoolClass::orderBy('name')->get();
        $examTypes = Exam::types();

        $firstClass = $classes->first();
        if (! $request->filled('class_id') && $firstClass) {
            return redirect()->route('reports.rankings', ['class_id' => $firstClass->id]);
        }

        if (! $request->filled('class_id')) {
            return view('admin.reports.rankings', compact('classes', 'examTypes'));
        }

        $class    = SchoolClass::findOrFail($request->class_id);
        $examType = $request->input('type', '');
        $month    = $request->input('month', '');
        $students = Student::where('class_id', $class->id)->orderBy('first_name')->get();

        // Available months for this class/type
        $availableMonths = Exam::where('class_id', $class->id)
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

            // Auto-redirect only when month is completely absent from URL (not when set to 0)
            if ($month === '' && ! $request->has('month')) {
                $default = $availableMonths->contains($currentMonth)
                    ? $currentMonth
                    : $availableMonths->first();

                return redirect()->route('reports.rankings', array_filter([
                    'class_id' => $request->class_id,
                    'type'     => $examType,
                    'month'    => $default,
                ], fn($v) => $v !== ''));
            }
        }

        $month = (int) $month;

        // Helper: build a ranked rows collection for a specific month (or all if 0)
        $buildRows = function (int $forMonth) use ($class, $examType, $students) {
            $examIds = Exam::where('class_id', $class->id)
                ->when($examType !== '', fn($q) => $q->where('type', $examType))
                ->when($forMonth > 0, fn($q) => $q->whereMonth('exam_date', $forMonth))
                ->pluck('id');

            $maxTotal   = Exam::whereIn('id', $examIds)->sum('total_marks');
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

        // All months — build one section per month
        if ($month === 0 && $availableMonths->isNotEmpty()) {
            $monthGroups = $availableMonths->map(function ($m) use ($buildRows) {
                $data = $buildRows($m);
                return array_merge(['month' => $m], $data);
            })->filter(fn($g) => $g['rows']->isNotEmpty())->values();

            return view('admin.reports.rankings', compact(
                'classes', 'examTypes', 'class', 'examType', 'month', 'availableMonths', 'monthGroups'
            ));
        }

        // Single month (or no months available)
        $data     = $buildRows($month);
        $rows     = $data['rows'];
        $maxTotal = $data['maxTotal'];
        $avgTotal = $data['avgTotal'];

        return view('admin.reports.rankings', compact(
            'classes', 'examTypes', 'class', 'examType', 'month', 'availableMonths',
            'rows', 'maxTotal', 'avgTotal'
        ));
    }

    private function gradeLabel(float $pct): string
    {
        return self::gradeFromPct($pct);
    }
}
