<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentTimetableController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\LinkAccountController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ScoreEntryController;
use App\Http\Controllers\QuickScoreController;
use App\Http\Controllers\MonthlyExamController;

/*
|--------------------------------------------------------------------------
| ROOT REDIRECT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('Student')) {
        $linked = \App\Models\Student::where('email', auth()->user()->email)->exists();
        return $linked
            ? redirect()->route('student.timetable')
            : redirect()->route('student.pending');
    }

    $stats = [
        'students' => \App\Models\Student::count(),
        'teachers' => \App\Models\Teacher::count(),
        'classes'  => \App\Models\SchoolClass::count(),
        'subjects' => \App\Models\Subject::count(),
        'exams'    => \App\Models\Exam::count(),
    ];

    $week = collect();
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->toDateString();
        $week->push([
            'date'       => $date,
            'label'      => now()->subDays($i)->format('D'),
            'present'    => \App\Models\Attendance::where('attendance_date', $date)->where('status', 'Present')->count(),
            'absent'     => \App\Models\Attendance::where('attendance_date', $date)->where('status', 'Absent')->count(),
            'late'       => \App\Models\Attendance::where('attendance_date', $date)->where('status', 'Late')->count(),
            'permission' => \App\Models\Attendance::where('attendance_date', $date)->where('status', 'Permission')->count(),
        ]);
    }

    $today = now()->toDateString();
    $todayAtt = [
        'present'    => \App\Models\Attendance::where('attendance_date', $today)->where('status', 'Present')->count(),
        'absent'     => \App\Models\Attendance::where('attendance_date', $today)->where('status', 'Absent')->count(),
        'late'       => \App\Models\Attendance::where('attendance_date', $today)->where('status', 'Late')->count(),
        'permission' => \App\Models\Attendance::where('attendance_date', $today)->where('status', 'Permission')->count(),
    ];
    $todayAtt['total'] = array_sum($todayAtt);

    $classDist = \App\Models\SchoolClass::withCount('students')->orderByDesc('students_count')->take(8)->get();

    $upcomingExams = \App\Models\Exam::with(['subject', 'schoolClass'])
        ->whereDate('exam_date', '>=', $today)
        ->orderBy('exam_date')
        ->take(5)
        ->get();

    $examStats = [
        'upcoming' => \App\Models\Exam::whereDate('exam_date', '>=', $today)->count(),
        'past'     => \App\Models\Exam::whereDate('exam_date', '<', $today)->count(),
        'thisWeek' => \App\Models\Exam::whereBetween('exam_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count(),
    ];

    $recentExams = \App\Models\Exam::with(['subject', 'schoolClass'])
        ->withCount('results')
        ->latest('exam_date')->take(5)->get();

    $recentStudents = \App\Models\Student::with('schoolClass')->latest()->take(5)->get();

    $announcements = \App\Models\Announcement::with('author')
        ->active()
        ->forUser(auth()->user())
        ->orderByDesc('pinned')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

    return view('dashboard', compact('stats','week','todayAtt','classDist','upcomingExams','examStats','recentExams','recentStudents','announcements'));
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| STUDENT PORTAL (no /admin prefix)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/pending', function () {
        $linked = \App\Models\Student::where('email', auth()->user()->email)->exists();
        if ($linked) return redirect()->route('student.timetable');
        return view('student.pending');
    })->name('student.pending');
});

Route::middleware(['auth', 'student.linked', 'permission:view timetable'])->group(function () {
    Route::get('/my-timetable',      [StudentTimetableController::class, 'index'])->name('student.timetable');
    Route::get('/my-announcements',  [StudentTimetableController::class, 'announcements'])->name('student.announcements');
});

Route::middleware(['auth', 'student.linked', 'permission:view results'])->group(function () {
    Route::get('/my-results',  [StudentResultController::class, 'myResults'])->name('student.results');
    Route::get('/my-rankings', [StudentResultController::class, 'classRankings'])->name('student.rankings');
});

/*
|--------------------------------------------------------------------------
| SHARED (profile, notifications — no /admin prefix)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}',      [NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifications/{id}',    [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES  →  /admin/*
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->group(function () {

    // Users
    Route::middleware(['permission:manage users'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Students export / import  (must be before {student} wildcard)
    Route::middleware(['permission:manage students'])->group(function () {
        Route::get('students/export',  [StudentController::class, 'exportCsv'])->name('students.export');
        Route::post('students/import', [StudentController::class, 'importCsv'])->name('students.import');
    });

    // Students
    Route::middleware(['permission:manage students|create students'])->group(function () {
        Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('students',       [StudentController::class, 'store'])->name('students.store');
    });
    Route::middleware(['permission:view students|manage students'])->group(function () {
        Route::get('students',           [StudentController::class, 'index'])->name('students.index');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
    });
    Route::middleware(['permission:manage students'])->group(function () {
        Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('students/{student}',      [StudentController::class, 'update'])->name('students.update');
        Route::patch('students/{student}',    [StudentController::class, 'update']);
        Route::delete('students/{student}',   [StudentController::class, 'destroy'])->name('students.destroy');
    });

    // Link accounts
    Route::middleware(['permission:manage students'])->group(function () {
        Route::get('link-accounts',                         [LinkAccountController::class, 'index'])->name('link-accounts.index');
        Route::post('link-accounts/{user}/link',            [LinkAccountController::class, 'link'])->name('link-accounts.link');
        Route::post('link-accounts/student/{student}/link', [LinkAccountController::class, 'linkStudent'])->name('link-accounts.link-student');
    });

    // Student results (admin/teacher view)
    Route::middleware(['permission:view results|manage exams'])->group(function () {
        Route::get('student-results',           [StudentResultController::class, 'index'])->name('student-results.index');
        Route::get('student-results/{student}', [StudentResultController::class, 'show'])->name('student-results.show');
    });

    // Attendance
    Route::middleware(['permission:manage attendance'])->group(function () {
        Route::get('attendances/export',                          [AttendanceController::class, 'exportCsv'])->name('attendances.export');
        Route::get('attendances/students',                        [AttendanceController::class, 'students'])->name('attendances.students');
        Route::get('attendances/{classId}/{date}/edit',           [AttendanceController::class, 'sessionEdit'])->name('attendances.session.edit');
        Route::delete('attendances/{classId}/{date}/session',     [AttendanceController::class, 'sessionDestroy'])->name('attendances.session.destroy');
        Route::resource('attendances', AttendanceController::class);
    });

    // Timetables
    Route::middleware(['permission:view timetable|manage timetables'])->group(function () {
        Route::get('timetables', [TimetableController::class, 'index'])->name('timetables.index');
    });
    Route::middleware(['permission:manage timetables'])->group(function () {
        Route::resource('timetables', TimetableController::class)->except(['index']);
    });

    // Teachers export
    Route::middleware(['permission:manage teachers'])->group(function () {
        Route::get('teachers/export', [TeacherController::class, 'exportCsv'])->name('teachers.export');
    });

    // Teachers
    Route::middleware(['permission:manage teachers'])->group(function () {
        Route::get('teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('teachers',       [TeacherController::class, 'store'])->name('teachers.store');
    });
    Route::middleware(['permission:view teachers|manage teachers'])->group(function () {
        Route::get('teachers',           [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
    });
    Route::middleware(['permission:manage teachers'])->group(function () {
        Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('teachers/{teacher}',      [TeacherController::class, 'update'])->name('teachers.update');
        Route::patch('teachers/{teacher}',    [TeacherController::class, 'update']);
        Route::delete('teachers/{teacher}',   [TeacherController::class, 'destroy'])->name('teachers.destroy');
    });

    // Classes
    Route::middleware(['permission:manage classes|create classes'])->group(function () {
        Route::get('school-classes/create', [SchoolClassController::class, 'create'])->name('school-classes.create');
        Route::post('school-classes',       [SchoolClassController::class, 'store'])->name('school-classes.store');
    });
    Route::middleware(['permission:view classes|manage classes'])->group(function () {
        Route::get('school-classes',                [SchoolClassController::class, 'index'])->name('school-classes.index');
        Route::get('school-classes/{school_class}', [SchoolClassController::class, 'show'])->name('school-classes.show');
    });
    Route::middleware(['permission:manage classes'])->group(function () {
        Route::get('school-classes/{school_class}/edit', [SchoolClassController::class, 'edit'])->name('school-classes.edit');
        Route::put('school-classes/{school_class}',      [SchoolClassController::class, 'update'])->name('school-classes.update');
        Route::patch('school-classes/{school_class}',    [SchoolClassController::class, 'update']);
        Route::delete('school-classes/{school_class}',   [SchoolClassController::class, 'destroy'])->name('school-classes.destroy');
    });

    // Subjects
    Route::middleware(['permission:manage subjects|create subjects'])->group(function () {
        Route::get('subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('subjects',       [SubjectController::class, 'store'])->name('subjects.store');
    });
    Route::middleware(['permission:view subjects|manage subjects'])->group(function () {
        Route::get('subjects',          [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    });
    Route::middleware(['permission:manage subjects'])->group(function () {
        Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('subjects/{subject}',      [SubjectController::class, 'update'])->name('subjects.update');
        Route::patch('subjects/{subject}',    [SubjectController::class, 'update']);
        Route::delete('subjects/{subject}',   [SubjectController::class, 'destroy'])->name('subjects.destroy');
    });

    // Exam Types
    Route::middleware(['permission:manage exams'])->group(function () {
        Route::get('exam-types',              [ExamTypeController::class, 'index'])->name('exam-types.index');
        Route::post('exam-types',             [ExamTypeController::class, 'store'])->name('exam-types.store');
        Route::put('exam-types/{examType}',   [ExamTypeController::class, 'update'])->name('exam-types.update');
        Route::delete('exam-types/{examType}',[ExamTypeController::class, 'destroy'])->name('exam-types.destroy');
    });

    // Exams
    Route::middleware(['permission:manage exams'])->group(function () {
        Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('exams',       [ExamController::class, 'store'])->name('exams.store');
    });
    Route::middleware(['permission:view results|manage exams'])->group(function () {
        Route::get('exams',        [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
    });
    Route::middleware(['permission:manage exams'])->group(function () {
        Route::get('exams/{exam}/edit',      [ExamController::class, 'edit'])->name('exams.edit');
        Route::put('exams/{exam}',           [ExamController::class, 'update'])->name('exams.update');
        Route::patch('exams/{exam}',         [ExamController::class, 'update']);
        Route::delete('exams/{exam}',        [ExamController::class, 'destroy'])->name('exams.destroy');
        Route::post('exams/{exam}/results',  [ExamController::class, 'saveResults'])->name('exams.results.save');
        Route::get('exams/{exam}/export',    [ExamController::class, 'exportResultsCsv'])->name('exams.results.export');
    });

    // Reports
    Route::middleware(['permission:view results|manage exams'])->group(function () {
        Route::get('reports',            [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('reports/rankings',   [ReportController::class, 'rankings'])->name('reports.rankings');
    });

    // Monthly Exam Summary
    Route::middleware(['permission:manage exams|view results'])->group(function () {
        Route::get('monthly-exam', [MonthlyExamController::class, 'index'])->name('monthly-exam.index');
    });

    // Score Entry (Gradebook)
    Route::middleware(['permission:manage exams'])->group(function () {
        Route::get('score-entry',  [ScoreEntryController::class, 'index'])->name('score-entry.index');
        Route::post('score-entry', [ScoreEntryController::class, 'store'])->name('score-entry.store');
        Route::get('quick-score',  [QuickScoreController::class, 'index'])->name('quick-score.index');
        Route::post('quick-score', [QuickScoreController::class, 'store'])->name('quick-score.store');
    });

    // Announcements
    Route::get('announcements',                       [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('announcements',                      [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('announcements/{announcement}',     [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Student class transfer
    Route::post('students/{student}/transfer', [StudentController::class, 'transfer'])->name('students.transfer');

});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
