<?php

namespace App\Http\Controllers;

use App\Notifications\SystemNotification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class LinkAccountController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        // Emails of students already linked to a user account
        $linkedStudentEmails = Student::whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email');

        // Users with Student role who have no matching student record
        $pendingUsers = User::role('Student')
            ->whereNotIn('email', $linkedStudentEmails)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'pending_page')
            ->withQueryString();

        // Users with Student role who are already linked
        $linkedUsers = User::role('Student')
            ->whereIn('email', $linkedStudentEmails)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'linked_page')
            ->withQueryString();

        // Student records for the current page of linked users (one query)
        $linkedStudentsMap = Student::with('schoolClass')
            ->whereIn('email', $linkedUsers->pluck('email'))
            ->get()
            ->keyBy('email');

        // Students not yet linked to any user account
        $linkedUserEmails = User::pluck('email');
        $unlinkedStudents = Student::with('schoolClass')
            ->where(function ($q) use ($linkedUserEmails) {
                $q->whereNotIn('email', $linkedUserEmails)
                  ->orWhereNull('email')
                  ->orWhere('email', '');
            })
            ->orderBy('first_name')
            ->get();

        return view('admin.link-accounts.index', compact('pendingUsers', 'unlinkedStudents', 'linkedUsers', 'linkedStudentsMap'));
    }

    public function link(Request $request, User $user)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($request->student_id);
        $student->update(['email' => $user->email]);

        $user->notify(new SystemNotification(
            title: 'Account linked',
            message: "Your profile has been linked to {$student->first_name} {$student->last_name}.",
            url: route('student.timetable'),
            type: 'account-linked'
        ));

        return back()->with('success', "Linked {$user->name} → {$student->first_name} {$student->last_name}");
    }

    // From student list: pick a user to link to this student
    public function linkStudent(Request $request, Student $student)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $student->update(['email' => $user->email]);

        $user->notify(new SystemNotification(
            title: 'Account linked',
            message: "Your profile has been linked to {$student->first_name} {$student->last_name}.",
            url: route('student.timetable'),
            type: 'account-linked'
        ));

        return back()->with('success', "Linked {$student->first_name} {$student->last_name} → {$user->name}");
    }
}
