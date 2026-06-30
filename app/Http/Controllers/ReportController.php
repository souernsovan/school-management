<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // Shared data-building logic
    private function buildReport(Request $request): array
    {
        $class    = SchoolClass::findOrFail($request->class_id);
        $students = $class->students()->orderBy('first_name')->get();
        $firstType = Exam::types()[0] ?? '';
        $examType  = $request->input('type', $firstType);

        $exams = Exam::with('subject')
            ->where('class_id', $class->id)
            ->where('type', $examType)
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

        return compact('class', 'students', 'exams', 'allResults', 'examType', 'grandTotal', 'rankMap');
    }

    public function index(Request $request)
    {
        $classes   = SchoolClass::orderBy('name')->get();
        $examTypes = Exam::types();

        if (! $request->filled('class_id')) {
            return view('admin.reports.index', compact('classes', 'examTypes'));
        }

        $data = $this->buildReport($request);

        return view('admin.reports.index', array_merge($data, compact('classes', 'examTypes')));
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

        $gradeInfo = fn(float $pct): array => match (true) {
            $pct >= 90 => ['label' => 'A+', 'color' => '#059669'],
            $pct >= 80 => ['label' => 'A',  'color' => '#059669'],
            $pct >= 70 => ['label' => 'B+', 'color' => '#2563eb'],
            $pct >= 60 => ['label' => 'B',  'color' => '#2563eb'],
            $pct >= 50 => ['label' => 'C',  'color' => '#d97706'],
            $pct >= 40 => ['label' => 'D',  'color' => '#ea580c'],
            default    => ['label' => 'F',  'color' => '#dc2626'],
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

        if (! $request->filled('class_id')) {
            return view('admin.reports.rankings', compact('classes', 'examTypes'));
        }

        $class     = SchoolClass::findOrFail($request->class_id);
        $examType  = $request->input('type', '');
        $students  = Student::where('class_id', $class->id)->orderBy('first_name')->get();

        $examQuery = Exam::where('class_id', $class->id);
        if ($examType !== '') {
            $examQuery->where('type', $examType);
        }
        $examIds   = $examQuery->pluck('id');
        $maxTotal  = $examQuery->sum('total_marks');

        $allResults = ExamResult::whereIn('exam_id', $examIds)
            ->get()
            ->groupBy('student_id');

        // Build ranked list
        $rows = $students->map(function ($student) use ($allResults, $maxTotal) {
            $obtained = $allResults->get($student->id)?->sum('marks_obtained') ?? 0;
            $pct      = $maxTotal > 0 ? ($obtained / $maxTotal) * 100 : 0;
            return [
                'student'  => $student,
                'obtained' => $obtained,
                'pct'      => round($pct, 1),
                'grade'    => $this->gradeLabel($pct),
            ];
        })->sortByDesc('pct')->values();

        // Assign ranks with tie support — convert to plain array to allow mutation
        $rowsArr = $rows->toArray();
        $rank    = 1;
        foreach ($rowsArr as $i => &$row) {
            if ($i > 0 && $row['pct'] === $rowsArr[$i - 1]['pct']) {
                $row['rank'] = $rowsArr[$i - 1]['rank'];
            } else {
                $row['rank'] = $rank;
            }
            $rank++;
        }
        unset($row);
        $rows = collect($rowsArr);

        $avgTotal = $rows->avg('obtained');

        return view('admin.reports.rankings', compact('classes', 'examTypes', 'class', 'examType', 'rows', 'maxTotal', 'avgTotal'));
    }

    private function gradeLabel(float $pct): string
    {
        return match (true) {
            $pct >= 95 => 'A+',
            $pct >= 90 => 'A',
            $pct >= 85 => 'B+',
            $pct >= 80 => 'B',
            $pct >= 70 => 'C',
            $pct >= 60 => 'D',
            $pct >= 50 => 'E',
            default    => 'F',
        };
    }
}
