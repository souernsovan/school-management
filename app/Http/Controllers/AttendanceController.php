<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Traits\ExportsToExcel;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ExportsToExcel;

    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();
        $today   = now()->toDateString();

        $todayStats = [
            'sessions' => Attendance::where('attendance_date', $today)->distinct('class_id')->count('class_id'),
            'present'  => Attendance::where('attendance_date', $today)->where('status', 'Present')->count(),
            'absent'   => Attendance::where('attendance_date', $today)->where('status', 'Absent')->count(),
            'late'     => Attendance::where('attendance_date', $today)->whereIn('status', ['Late', 'Permission'])->count(),
        ];

        $sessions = Attendance::join('school_classes', 'attendances.class_id', '=', 'school_classes.id')
            ->leftJoin('teachers', 'attendances.teacher_id', '=', 'teachers.id')
            ->selectRaw('\n                attendances.attendance_date,\n                attendances.class_id,\n                school_classes.name        AS class_name,\n                school_classes.section     AS class_section,\n                MIN(CONCAT(teachers.first_name, \' \' , teachers.last_name)) AS teacher_name,\n                COUNT(*)                                                  AS total,\n                SUM(CASE WHEN attendances.status = \'Present\' THEN 1 ELSE 0 END)                       AS present_count,\n                SUM(CASE WHEN attendances.status = \'Absent\' THEN 1 ELSE 0 END)                        AS absent_count,\n                SUM(CASE WHEN attendances.status = \'Late\' THEN 1 ELSE 0 END)                          AS late_count,\n                SUM(CASE WHEN attendances.status = \'Permission\' THEN 1 ELSE 0 END)                    AS permission_count\n            ')
            ->when($request->filled('class_id'), fn($q) => $q->where('attendances.class_id', $request->class_id))
            ->when($request->filled('date'),     fn($q) => $q->where('attendances.attendance_date', $request->date))
            ->groupBy('attendances.attendance_date', 'attendances.class_id', 'school_classes.name', 'school_classes.section')
            ->orderByDesc('attendances.attendance_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendances.index', compact('sessions', 'classes', 'todayStats'));
    }

    public function create()
    {
        $classes  = SchoolClass::orderBy('name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        $subjects = Subject::orderBy('name')->get();

        // Pass existing sessions for duplicate detection in JS
        $existingSessions = Attendance::selectRaw('class_id, attendance_date')
            ->distinct()
            ->get()
            ->groupBy('class_id')
            ->map(fn($g) => $g->pluck('attendance_date')->map(fn($d) => (string) $d)->values()->toArray());

        return view('admin.attendances.create', compact('classes', 'teachers', 'subjects', 'existingSessions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'          => 'required|exists:school_classes,id',
            'teacher_id'        => 'required|exists:teachers,id',
            'subject_id'        => 'nullable|exists:subjects,id',
            'attendance_date'   => 'required|date',
            'rows'              => 'required|array|min:1',
            'rows.*.student_id' => 'required|exists:students,id',
            'rows.*.status'     => 'required|in:Present,Absent,Late,Permission',
            'rows.*.remark'     => 'nullable|string|max:255',
        ]);

        foreach ($request->rows as $row) {
            Attendance::updateOrCreate(
                [
                    'student_id'      => $row['student_id'],
                    'attendance_date' => $request->attendance_date,
                    'subject_id'      => $request->subject_id ?: null,
                ],
                [
                    'teacher_id' => $request->teacher_id,
                    'class_id'   => $request->class_id,
                    'status'     => $row['status'],
                    'remark'     => $row['remark'] ?? null,
                ]
            );
        }

        $class   = SchoolClass::find($request->class_id);
        $subject = $request->filled('subject_id') ? Subject::find($request->subject_id) : null;

        $this->notifyStaff(
            title:   'Attendance recorded',
            message: 'Attendance saved for ' . ($class?->name ?? 'a class') . ' on ' . $request->attendance_date . ($subject ? ' (' . $subject->name . ')' : '') . '.',
            url:     route('attendances.index'),
            type:    'attendance-saved'
        );

        $this->notifyStudentsForRows($request->rows, $request->attendance_date, $request->class_id, $request->subject_id);

        return redirect()
            ->route('attendances.session.edit', [$request->class_id, $request->attendance_date])
            ->with('success', 'Attendance saved.');
    }

    public function students(Request $request)
    {
        $students = Student::where('class_id', $request->class_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->first_name . ' ' . $s->last_name,
                'init' => strtoupper(substr($s->first_name, 0, 1)),
            ]);

        return response()->json($students);
    }

    // ── Session bulk-edit ──────────────────────────────────────────────────────

    public function sessionEdit(Request $request, $classId, $date)
    {
        $class    = SchoolClass::findOrFail($classId);
        $students = Student::where('class_id', $classId)->orderBy('first_name')->get();
        $existing = Attendance::where('class_id', $classId)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        $teachers = Teacher::orderBy('first_name')->get();
        $subjects = Subject::orderBy('name')->get();
        $first    = $existing->first(); // default teacher/subject from existing records

        $stats = [
            'present'    => $existing->where('status', 'Present')->count(),
            'absent'     => $existing->where('status', 'Absent')->count(),
            'late'       => $existing->where('status', 'Late')->count(),
            'permission' => $existing->where('status', 'Permission')->count(),
            'total'      => $existing->count(),
        ];

        return view('admin.attendances.session', compact(
            'class', 'students', 'existing', 'teachers', 'subjects', 'date', 'first', 'stats'
        ));
    }

    public function sessionDestroy(Request $request, $classId, $date)
    {
        Attendance::where('class_id', $classId)
            ->where('attendance_date', $date)
            ->delete();

        return redirect()->route('attendances.index')->with('success', 'Session deleted.');
    }

    // ── Single record edit (kept for direct links) ─────────────────────────────

    public function show(Attendance $attendance)
    {
        return redirect()->route('attendances.session.edit', [
            $attendance->class_id,
            $attendance->attendance_date,
        ]);
    }

    public function edit(Attendance $attendance)
    {
        return redirect()->route('attendances.session.edit', [
            $attendance->class_id,
            $attendance->attendance_date,
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        return redirect()->route('attendances.session.edit', [
            $attendance->class_id,
            $attendance->attendance_date,
        ]);
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $attendances = Attendance::with(['student', 'teacher', 'schoolClass', 'subject'])
            ->when($request->filled('class_id'),  fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('status'),    fn($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn($q) => $q->where('attendance_date', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn($q) => $q->where('attendance_date', '<=', $request->date_to))
            ->when($request->filled('date'),      fn($q) => $q->where('attendance_date', $request->date))
            ->orderBy('attendance_date', 'desc')
            ->get();

        $className = $request->filled('class_id')
            ? (SchoolClass::find($request->class_id)?->name ?? 'Class')
            : null;

        $rows = $attendances->map(fn($a) => [
            $a->attendance_date,
            trim(($a->student->first_name ?? '') . ' ' . ($a->student->last_name ?? '')),
            $a->schoolClass->name ?? '',
            $a->subject->name ?? '',
            trim(($a->teacher->first_name ?? '') . ' ' . ($a->teacher->last_name ?? '')),
            $a->status,
        ]);

        return $this->xlsResponse(
            $className ? "Attendance — {$className}" : 'Attendance — All Classes',
            ['Date', 'Student', 'Class', 'Subject', 'Teacher', 'Status'],
            $rows,
            $className ? 'attendance_' . \Str::slug($className) : 'attendance_all'
        );
    }

    public function destroy(Attendance $attendance)
    {
        $classId = $attendance->class_id;
        $date    = $attendance->attendance_date;
        $attendance->delete();

        return redirect()->route('attendances.index')->with('success', 'Record deleted.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function notifyStaff(string $title, string $message, ?string $url, string $type): void
    {
        User::role(['Admin', 'Teacher'])->get()->each(fn($user) => $user->notify(
            new SystemNotification(title: $title, message: $message, url: $url, type: $type)
        ));
    }

    private function notifyStudentsForRows(iterable $rows, string $date, int $classId, ?int $subjectId): void
    {
        $subject = $subjectId ? Subject::find($subjectId) : null;

        foreach ($rows as $row) {
            $att = Attendance::where('student_id', $row['student_id'])
                ->where('attendance_date', $date)
                ->where('class_id', $classId)
                ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId), fn($q) => $q->whereNull('subject_id'))
                ->first();

            if ($att) {
                $this->notifyStudentForAttendance($att);
            }
        }
    }

    private function notifyStudentForAttendance(Attendance $attendance): void
    {
        $user = User::where('email', $attendance->student?->email)->first();
        if (! $user) return;

        $user->notify(new SystemNotification(
            title:   'Attendance updated',
            message: sprintf('Your attendance was marked as %s on %s%s.',
                $attendance->status,
                $attendance->attendance_date,
                $attendance->subject?->name ? ' for ' . $attendance->subject->name : ''
            ),
            url:  route('student.pending'),
            type: 'attendance-student'
        ));
    }
}
