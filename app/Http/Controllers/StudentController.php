<?php
namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Traits\ExportsToExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
class StudentController extends Controller
{
    use ExportsToExcel;
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

    public function exportCsv(Request $request): StreamedResponse
    {
        $students = Student::with('schoolClass')
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn($q) => $q
                    ->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%$s%"])
                    ->orWhere('email', 'like', "%$s%")
                );
            })
            ->orderBy('first_name')
            ->get();

        $className = $request->filled('class_id')
            ? (SchoolClass::find($request->class_id)?->name ?? 'Class')
            : null;

        $rows = $students->map(fn($s) => [
            $s->first_name, $s->last_name, $s->email ?? '',
            $s->dob ?? '', $s->gender ?? '', $s->phone ?? '',
            $s->address ?? '', $s->schoolClass->name ?? '',
        ]);

        return $this->xlsResponse(
            $className ? "Students — {$className}" : 'Students — All Classes',
            ['First Name', 'Last Name', 'Email', 'Date of Birth', 'Gender', 'Phone', 'Address', 'Class'],
            $rows,
            $className ? 'students_' . \Str::slug($className) : 'students_all'
        );
    }

    public function transfer(Request $request, Student $student)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
        ]);

        $from = $student->schoolClass?->name ?? 'None';
        $student->update(['class_id' => $request->class_id]);
        $to = SchoolClass::find($request->class_id)->name;

        return redirect()->route('students.show', $student)
            ->with('success', "Transferred from {$from} to {$to}.");
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $handle   = fopen($request->file('csv_file')->getRealPath(), 'r');
        fgetcsv($handle); // skip header

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            [$firstName, $lastName, $email, $dob, $gender, $phone, $address, $className] = array_pad($row, 8, null);

            $firstName = trim($firstName ?? '');
            $lastName  = trim($lastName  ?? '');
            if ($firstName === '' || $lastName === '') { $skipped++; continue; }

            $email = trim($email ?? '') ?: null;
            if ($email && Student::where('email', $email)->exists()) { $skipped++; continue; }

            $classId = null;
            if (!empty($className)) {
                $classId = SchoolClass::where('name', trim($className))->value('id');
            }

            Student::create([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'dob'        => trim($dob    ?? '') ?: null,
                'gender'     => trim($gender ?? '') ?: null,
                'phone'      => trim($phone  ?? '') ?: null,
                'address'    => trim($address ?? '') ?: null,
                'class_id'   => $classId,
            ]);
            $imported++;
        }
        fclose($handle);

        $msg = "$imported student(s) imported.";
        if ($skipped) $msg .= " $skipped row(s) skipped (missing name or duplicate email).";

        return redirect()->route('students.index')->with('success', $msg);
    }
}
