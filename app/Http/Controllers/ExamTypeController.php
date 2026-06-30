<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamType;
use Illuminate\Http\Request;

class ExamTypeController extends Controller
{
    public function index()
    {
        $types = ExamType::orderBy('sort_order')->orderBy('name')->get();

        // Attach usage count from the exams table
        $counts = Exam::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $types->each(fn($t) => $t->exams_count = $counts[$t->name] ?? 0);

        return view('admin.exam-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:exam_types,name',
        ]);

        ExamType::create(['name' => trim($request->name)]);

        return redirect()->route('exam-types.index')->with('success', 'Exam type "' . $request->name . '" added.');
    }

    public function update(Request $request, ExamType $examType)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:exam_types,name,' . $examType->id,
        ]);

        $oldName = $examType->name;
        $newName = trim($request->name);

        $examType->update(['name' => $newName]);

        // Keep existing exam records in sync
        if ($oldName !== $newName) {
            Exam::where('type', $oldName)->update(['type' => $newName]);
        }

        return redirect()->route('exam-types.index')->with('success', 'Exam type updated.');
    }

    public function destroy(ExamType $examType)
    {
        $count = Exam::where('type', $examType->name)->count();
        if ($count > 0) {
            return redirect()->route('exam-types.index')
                ->with('error', "Cannot delete \"{$examType->name}\" — {$count} exam(s) are using this type.");
        }

        $examType->delete();

        return redirect()->route('exam-types.index')->with('success', 'Exam type deleted.');
    }
}
