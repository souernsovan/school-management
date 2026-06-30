<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
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
}
