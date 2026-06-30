<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::withCount('students')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(fn($q) => $q
                    ->where('name', 'like', "%$search%")
                    ->orWhere('section', 'like', "%$search%")
                );
            })
            ->paginate(in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10)
            ->withQueryString();

        return view('admin.school_classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.school_classes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'section' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        SchoolClass::create($validated);

        return redirect()->route('school-classes.index')->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->loadCount(['students', 'timetables', 'exams'])
            ->load([
                'students' => fn($q) => $q->orderBy('first_name'),
                'timetables' => fn($q) => $q->with(['subject', 'teacher', 'exam'])->latest('created_at')->take(8),
                'exams' => fn($q) => $q->with(['subject'])->latest('exam_date')->take(8),
            ]);

        return view('admin.school_classes.show', compact('schoolClass'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolClass $schoolClass)
    {
        return view('admin.school_classes.edit', compact('schoolClass'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolClass $schoolClass)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'section' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $schoolClass->update($validated);

        return redirect()->route('school-classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();

        return redirect()->route('school-classes.index')->with('success', 'Class deleted successfully.');
    }
}
