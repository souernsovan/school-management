<?php
namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
class StudentController extends Controller
{
    /**
     * Display all students
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::all();

        $students = Student::with('schoolClass')
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(fn($q) => $q
                    ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                );
            })
            ->paginate(in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10)
            ->withQueryString();

        // For the link-to-user modal: only show users not already linked to a student
        $linkedEmails = Student::whereNotNull('email')->where('email', '!=', '')->pluck('email');
        $studentUsers = User::role('Student')
            ->whereNotIn('email', $linkedEmails)
            ->orderBy('name')
            ->get();

        return view('admin.students.index', compact('students', 'classes', 'studentUsers'));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $classes = SchoolClass::all();
        $selectedClassId = $request->integer('class_id');

        // Only show users NOT already linked to any student
        $linkedEmails = Student::whereNotNull('email')->where('email', '!=', '')->pluck('email');
        $studentUsers = User::role('Student')
            ->whereNotIn('email', $linkedEmails)
            ->orderBy('name')
            ->get();

        return view('admin.students.create', compact('classes', 'studentUsers', 'selectedClassId'));
    }

    /**
     * Store student (ADMIN creates student only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email|unique:students',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        // ✔ CREATE STUDENT DIRECTLY (NO auth()->id())
        Student::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'class_id' => $request->class_id,
            'address' => $request->address,
        ]);

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully');
    }

    public function show(Student $student)
    {
        $student->load('schoolClass');

        $attendances = $student->attendances()
            ->with(['subject', 'teacher', 'schoolClass'])
            ->latest('attendance_date')
            ->paginate(15);

        $stats = [
            'total'      => $student->attendances()->count(),
            'present'    => $student->attendances()->where('status', 'Present')->count(),
            'absent'     => $student->attendances()->where('status', 'Absent')->count(),
            'late'       => $student->attendances()->where('status', 'Late')->count(),
            'permission' => $student->attendances()->where('status', 'Permission')->count(),
        ];

        return view('admin.students.show', compact('student', 'attendances', 'stats'));
    }

    /**
     * Edit student
     */
    public function edit(Student $student)
    {
        $classes    = SchoolClass::all();
        $linkedUser = $student->email ? User::where('email', $student->email)->first() : null;

        // Show users not linked to any OTHER student (keep current student's linked user visible)
        $linkedEmails = Student::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('id', '!=', $student->id)
            ->pluck('email');
        $studentUsers = User::role('Student')
            ->whereNotIn('email', $linkedEmails)
            ->orderBy('name')
            ->get();

        return view('admin.students.edit', compact('student', 'classes', 'studentUsers', 'linkedUser'));
    }

    /**
     * Update student
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        $student->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'address' => $request->address,
            'class_id' => $request->class_id,
        ]);

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully');
    }

    /**
     * Delete student
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully');
    }
}
