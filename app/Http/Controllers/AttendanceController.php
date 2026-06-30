<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::all();

        $attendances = Attendance::with(['student', 'teacher', 'schoolClass', 'subject'])
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('date'),     fn($q) => $q->where('attendance_date', $request->date))
            ->when($request->filled('status'),   fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->whereHas('student', fn($q) => $q
                    ->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%$s%"])
                );
            })
            ->latest('attendance_date')
            ->paginate(in_array((int)$request->per_page, [10,25,50,100]) ? (int)$request->per_page : 10)
            ->withQueryString();

        return view('admin.attendances.index', compact('attendances', 'classes'));
    }

    public function create()
    {
        $classes  = SchoolClass::all();
        $teachers = Teacher::orderBy('first_name')->get();
        $subjects = Subject::orderBy('name')->get();
        $students = Student::with('schoolClass')->orderBy('first_name')->get();

        return view('admin.attendances.create', compact('classes', 'teachers', 'subjects', 'students'));
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

        $class = SchoolClass::find($request->class_id);
        $subject = $request->filled('subject_id') ? Subject::find($request->subject_id) : null;
        $this->notifyStaff(
            title: 'Attendance recorded',
            message: 'Attendance has been saved for ' . ($class?->name ?? 'a class') . ' on ' . $request->attendance_date . ($subject ? ' for ' . $subject->name : '') . '.',
            url: route('attendances.index'),
            type: 'attendance-saved'
        );

        $this->notifyStudentsForRows($request->rows, $request->attendance_date, $request->class_id, $request->subject_id);

        return redirect()->route('attendances.index')->with('success', 'Attendance saved successfully.');
    }

    public function show(Attendance $attendance)
    {
        return redirect()->route('attendances.edit', $attendance);
    }

    public function edit(Attendance $attendance)
    {
        $classes  = SchoolClass::all();
        $teachers = Teacher::orderBy('first_name')->get();
        $subjects = Subject::all();
        $students = Student::orderBy('first_name')->get();

        return view('admin.attendances.edit', compact('attendance', 'classes', 'teachers', 'subjects', 'students'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'student_id'      => 'required|exists:students,id',
            'teacher_id'      => 'required|exists:teachers,id',
            'class_id'        => 'required|exists:school_classes,id',
            'subject_id'      => 'nullable|exists:subjects,id',
            'attendance_date' => 'required|date',
            'status'          => 'required|in:Present,Absent,Late,Permission',
            'remark'          => 'nullable|string|max:255',
        ]);

        $attendance->update($request->only(
            'student_id','teacher_id','class_id','subject_id',
            'attendance_date','status','remark'
        ));

        $class = SchoolClass::find($request->class_id);
        $subject = $request->filled('subject_id') ? Subject::find($request->subject_id) : null;
        $this->notifyStaff(
            title: 'Attendance updated',
            message: 'Attendance was updated for ' . ($class?->name ?? 'a class') . ' on ' . $request->attendance_date . ($subject ? ' for ' . $subject->name : '') . '.',
            url: route('attendances.index'),
            type: 'attendance-updated'
        );

        $this->notifyStudentForAttendance($attendance);

        return redirect()->route('attendances.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $className = $attendance->schoolClass?->name ?? 'a class';
        $attendanceDate = $attendance->attendance_date;
        $subjectName = $attendance->subject?->name;

        $attendance->delete();

        $this->notifyStaff(
            title: 'Attendance removed',
            message: 'Attendance was deleted for ' . $className . ' on ' . $attendanceDate . ($subjectName ? ' for ' . $subjectName : '') . '.',
            url: route('attendances.index'),
            type: 'attendance-deleted'
        );

        return redirect()->route('attendances.index')->with('success', 'Attendance deleted successfully.');
    }

    private function notifyStaff(string $title, string $message, ?string $url, string $type): void
    {
        User::role(['Admin', 'Teacher'])->get()->each(function (User $user) use ($title, $message, $url, $type) {
            $user->notify(new SystemNotification(
                title: $title,
                message: $message,
                url: $url,
                type: $type
            ));
        });
    }

    private function notifyStudentsForRows(iterable $rows, string $attendanceDate, int $classId, ?int $subjectId): void
    {
        $subject = $subjectId ? Subject::find($subjectId) : null;

        foreach ($rows as $row) {
            $attendance = Attendance::where('student_id', $row['student_id'])
                ->where('attendance_date', $attendanceDate)
                ->where('class_id', $classId)
                ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId), fn($q) => $q->whereNull('subject_id'))
                ->first();

            if ($attendance) {
                $this->notifyStudentForAttendance($attendance);
            }
        }
    }

    private function notifyStudentForAttendance(Attendance $attendance): void
    {
        $studentUser = User::where('email', $attendance->student?->email)->first();

        if (! $studentUser) {
            return;
        }

        $studentUser->notify(new SystemNotification(
            title: 'Attendance updated',
            message: sprintf(
                'Your attendance was marked as %s on %s%s.',
                $attendance->status,
                $attendance->attendance_date,
                $attendance->subject?->name ? ' for ' . $attendance->subject->name : ''
            ),
            url: route('student.pending'),
            type: 'attendance-student'
        ));
    }
}
