<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    // Admin/Teacher: list all students with summary
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();

        $query = Student::with(['schoolClass', 'examResults.exam']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name',  'like', '%' . $request->search . '%');
            });
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->get()
            ->map(function ($student) {
                $obtained = $student->examResults->sum('marks_obtained');
                $possible = $student->examResults->sum(fn($r) => $r->exam?->total_marks ?? 0);
                $pct      = $possible > 0 ? round(($obtained / $possible) * 100, 1) : null;
                $grade    = $pct === null ? '—' : match(true) {
                    $pct >= 90 => 'A+', $pct >= 80 => 'A',
                    $pct >= 70 => 'B+', $pct >= 60 => 'B',
                    $pct >= 50 => 'C',  $pct >= 40 => 'D',
                    default    => 'F',
                };
                $student->summary = [
                    'count'    => $student->examResults->count(),
                    'obtained' => $obtained,
                    'possible' => $possible,
                    'pct'      => $pct,
                    'grade'    => $grade,
                ];
                return $student;
            });

        return view('admin.student-results.index', compact('students', 'classes'));
    }

    // Admin/Teacher: individual student results
    public function show(Student $student)
    {
        $student->load('schoolClass');

        $results = ExamResult::where('student_id', $student->id)
            ->with(['exam.subject', 'exam.schoolClass'])
            ->get()
            ->sortByDesc(fn($r) => $r->exam?->exam_date);

        $grouped  = $results->groupBy(fn($r) => $r->exam?->type ?? 'Other');
        $total    = $results->count();
        $obtained = $results->sum('marks_obtained');
        $possible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
        $pct      = $possible > 0 ? ($obtained / $possible) * 100 : 0;

        $gradeLabel = match(true) {
            $pct >= 90 => 'A+', $pct >= 80 => 'A',
            $pct >= 70 => 'B+', $pct >= 60 => 'B',
            $pct >= 50 => 'C',  $pct >= 40 => 'D',
            default    => 'F',
        };

        $summary = compact('total', 'obtained', 'possible', 'pct', 'gradeLabel');

        return view('admin.student-results.show', compact('student', 'grouped', 'summary'));
    }

    // Student portal: class rankings
    public function classRankings(\Illuminate\Http\Request $request)
    {
        $me = Student::where('email', auth()->user()->email)->with('schoolClass')->first();

        if (! $me) {
            return view('student.rankings', ['me' => null, 'rows' => collect(), 'examTypes' => [], 'examType' => '', 'maxTotal' => 0, 'avgTotal' => 0]);
        }

        $examTypes = \App\Models\Exam::where('class_id', $me->class_id)->distinct()->orderBy('type')->pluck('type');
        $examType  = $request->input('type', '');

        $examQuery = \App\Models\Exam::where('class_id', $me->class_id);
        if ($examType !== '') {
            $examQuery->where('type', $examType);
        }
        $examIds  = $examQuery->pluck('id');
        $maxTotal = $examQuery->sum('total_marks');

        $allResults = ExamResult::whereIn('exam_id', $examIds)->get()->groupBy('student_id');

        $students = Student::where('class_id', $me->class_id)->get();

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

        // Assign ranks with tie support
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

        return view('student.rankings', compact('me', 'rows', 'examTypes', 'examType', 'maxTotal', 'avgTotal'));
    }

    private function gradeLabel(float $pct): string
    {
        return match(true) {
            $pct >= 95 => 'A+', $pct >= 90 => 'A',
            $pct >= 85 => 'B+', $pct >= 80 => 'B',
            $pct >= 70 => 'C',  $pct >= 60 => 'D',
            $pct >= 50 => 'E',  default    => 'F',
        };
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
    public function myResults()
    {
        $student = Student::where('email', auth()->user()->email)
            ->with('schoolClass')
            ->first();

        if (! $student) {
            return view('student.results', ['student' => null, 'grouped' => collect(), 'summary' => []]);
        }

        $results = ExamResult::where('student_id', $student->id)
            ->with(['exam.subject', 'exam.schoolClass'])
            ->get()
            ->sortByDesc(fn($r) => $r->exam?->exam_date);

        $grouped  = $results->groupBy(fn($r) => $r->exam?->type ?? 'Other');
        $total    = $results->count();
        $obtained = $results->sum('marks_obtained');
        $possible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
        $pct      = $possible > 0 ? ($obtained / $possible) * 100 : 0;

        $gradeLabel = match(true) {
            $pct >= 90 => 'A+', $pct >= 80 => 'A',
            $pct >= 70 => 'B+', $pct >= 60 => 'B',
            $pct >= 50 => 'C',  $pct >= 40 => 'D',
            default    => 'F',
        };

        $summary = compact('total', 'obtained', 'possible', 'pct', 'gradeLabel');

        return view('student.results', compact('student', 'grouped', 'summary'));
    }
}
