<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QuickScoreController extends Controller
{
    private function sessionMarksKey(string $classId, string $type, int $month): string
    {
        return 'qs_marks_' . md5("{$classId}_{$type}_{$month}_" . now()->year);
    }

    public function index(Request $request)
    {
        $classes   = SchoolClass::orderBy('name')->get();
        $examTypes = Exam::types();

        if (! $request->hasAny(['class_id', 'type', 'month', 'total_marks'])) {
            $saved = session('quick_score_filters');
            if ($saved) {
                return redirect()->route('quick-score.index', $saved);
            }
        }

        $classId    = $request->input('class_id');
        $examType   = $request->input('type', '');
        $month      = (int) $request->input('month', 0);
        $totalMarks = (int) $request->input('total_marks', 100);

        if ($classId) {
            session(['quick_score_filters' => array_filter([
                'class_id'    => $classId,
                'type'        => $examType,
                'month'       => $month ?: null,
                'total_marks' => $totalMarks ?: 100,
            ])]);
        }

        // Default exam_date for new entries (end of selected month)
        $examDate = $month
            ? Carbon::createFromDate(now()->year, $month, 1)->endOfMonth()->toDateString()
            : '';

        $subjects        = collect();
        $students        = collect();
        $results         = collect();
        $examMarks       = collect();
        $examsBySubject  = collect(); // subject_id → Exam model

        $ready = $classId && $examType && $month && $totalMarks > 0;

        if ($classId) {
            $subjects = Subject::orderBy('name')->get();
            $students = Student::where('class_id', $classId)->orderBy('first_name')->orderBy('last_name')->get();
        }

        if ($ready) {
            // All exams for this class+type in this month, newest first
            $monthExams = Exam::where('class_id', $classId)
                ->where('type', $examType)
                ->whereMonth('exam_date', $month)
                ->whereYear('exam_date', now()->year)
                ->orderByDesc('exam_date')
                ->get(['id', 'subject_id', 'total_marks', 'exam_date']);

            // Keep the most recent exam per subject
            $examsBySubject = $monthExams->groupBy('subject_id')
                ->map(fn($group) => $group->first());

            $examIds = $examsBySubject->map(fn($e) => $e->id);

            // Marks: session first (for subjects with no exam yet), DB overwrites
            $sessionMarks = session($this->sessionMarksKey($classId, $examType, $month), []);
            $examMarks = collect($sessionMarks)->map(fn($v) => (float) $v);
            foreach ($examsBySubject as $subjectId => $exam) {
                $examMarks->put($subjectId, (float) $exam->total_marks);
            }

            $results = ExamResult::whereIn('exam_id', $examIds->values())
                ->with('exam')
                ->get()
                ->groupBy(fn($r) => (string) $r->student_id)
                ->map(fn($rows) => $rows->values());
        }

        return view('admin.quick-score.index', compact(
            'classes', 'examTypes', 'classId', 'examType', 'examDate',
            'totalMarks', 'subjects', 'students', 'results', 'ready',
            'examMarks', 'month', 'examsBySubject'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'type'        => 'required|string',
            'exam_date'   => 'required|date',
            'month'       => 'required|integer|min:1|max:12',
            'total_marks' => 'required|numeric|min:1|max:1000',
        ]);

        $classId      = $request->class_id;
        $examType     = $request->type;
        $examDate     = $request->exam_date;  // default date for new exams
        $month        = (int) $request->month;
        $totalMarks   = (float) $request->total_marks;
        $subjectMarks = $request->input('subject_marks', []);
        $scores       = $request->input('scores', []);

        if (! empty($subjectMarks)) {
            session([$this->sessionMarksKey($classId, $examType, $month) => $subjectMarks]);
        }

        // Update total_marks on all existing exams in this month
        Exam::where('class_id', $classId)
            ->where('type', $examType)
            ->whereMonth('exam_date', $month)
            ->whereYear('exam_date', now()->year)
            ->get()
            ->each(function ($exam) use ($subjectMarks, $totalMarks) {
                $newTotal = isset($subjectMarks[$exam->subject_id]) && (float) $subjectMarks[$exam->subject_id] > 0
                    ? (float) $subjectMarks[$exam->subject_id]
                    : $totalMarks;
                if ((float) $exam->total_marks !== $newTotal) {
                    $exam->update(['total_marks' => $newTotal]);
                }
            });

        foreach ($scores as $subjectId => $studentScores) {
            $hasScore = collect($studentScores)->contains(fn($v) => $v !== null && $v !== '');
            if (! $hasScore) continue;

            $subjectTotal = isset($subjectMarks[$subjectId]) && (float) $subjectMarks[$subjectId] > 0
                ? (float) $subjectMarks[$subjectId]
                : $totalMarks;

            // Reuse the most recent existing exam for this subject in this month, or create new
            $exam = Exam::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('type', $examType)
                ->whereMonth('exam_date', $month)
                ->whereYear('exam_date', now()->year)
                ->orderByDesc('exam_date')
                ->first();

            if ($exam) {
                $exam->update(['total_marks' => $subjectTotal]);
            } else {
                $exam = Exam::create([
                    'class_id'    => $classId,
                    'subject_id'  => $subjectId,
                    'type'        => $examType,
                    'exam_date'   => $examDate,
                    'total_marks' => $subjectTotal,
                ]);
            }

            foreach ($studentScores as $studentId => $marks) {
                if ($marks === null || $marks === '') {
                    ExamResult::where('student_id', $studentId)
                        ->where('exam_id', $exam->id)
                        ->delete();
                    continue;
                }

                $marks = max(0, min((float) $marks, $exam->total_marks));

                ExamResult::updateOrCreate(
                    ['student_id' => $studentId, 'exam_id' => $exam->id],
                    ['marks_obtained' => $marks]
                );
            }
        }

        return redirect()->route('quick-score.index', [
            'class_id'    => $classId,
            'type'        => $examType,
            'month'       => $month,
            'total_marks' => (int) $totalMarks,
        ])->with('success', 'Scores saved successfully.');
    }
}
