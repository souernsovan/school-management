<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Traits\ExportsToExcel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    use ExportsToExcel;
    public function index(Request $request)
    {
        $teachers = Teacher::when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(fn($q) => $q
                    ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                );
            })
            ->paginate(in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'nullable|email|unique:teachers',
            'phone'          => 'nullable|string|max:20',
            'dob'            => 'nullable|date',
            'gender'         => 'nullable|in:Male,Female',
            'hire_date'      => 'nullable|date',
            'qualification'  => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'address'        => 'nullable|string',
            'status'         => 'nullable|in:Active,Inactive',
        ]);

        Teacher::create($request->only([
            'first_name', 'last_name', 'email', 'phone', 'dob',
            'gender', 'hire_date', 'qualification', 'specialization', 'address', 'status',
        ]));

        return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
    }

    public function edit(Teacher $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'nullable|email|unique:teachers,email,' . $teacher->id,
            'phone'          => 'nullable|string|max:20',
            'dob'            => 'nullable|date',
            'gender'         => 'nullable|in:Male,Female',
            'hire_date'      => 'nullable|date',
            'qualification'  => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'address'        => 'nullable|string',
            'status'         => 'nullable|in:Active,Inactive',
        ]);

        $teacher->update($request->only([
            'first_name', 'last_name', 'email', 'phone', 'dob',
            'gender', 'hire_date', 'qualification', 'specialization', 'address', 'status',
        ]));

        return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $teachers = Teacher::when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn($q) => $q
                    ->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%$s%"])
                    ->orWhere('email', 'like', "%$s%")
                );
            })
            ->orderBy('first_name')
            ->get();

        $rows = $teachers->map(fn($t) => [
            $t->first_name, $t->last_name, $t->email ?? '', $t->phone ?? '',
            $t->dob ?? '', $t->gender ?? '', $t->hire_date ?? '',
            $t->qualification ?? '', $t->specialization ?? '', $t->status ?? '',
        ]);

        return $this->xlsResponse(
            'Teachers Report',
            ['First Name', 'Last Name', 'Email', 'Phone', 'Date of Birth', 'Gender', 'Hire Date', 'Qualification', 'Specialization', 'Status'],
            $rows,
            'teachers'
        );
    }
}
