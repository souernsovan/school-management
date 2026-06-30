<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(fn($q) => $q
                    ->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                );
            })
            ->paginate(in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10)
            ->withQueryString();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'code'   => 'nullable|string|max:50',
            'credit' => 'nullable|integer|min:1',
        ]);

        Subject::create($request->only('name', 'code', 'credit'));

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'code'   => 'nullable|string|max:50',
            'credit' => 'nullable|integer|min:1',
        ]);

        $subject->update($request->only('name', 'code', 'credit'));

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
