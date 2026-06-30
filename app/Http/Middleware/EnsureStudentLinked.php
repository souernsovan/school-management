<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;

class EnsureStudentLinked
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasRole('Student')) {
            $linked = Student::where('email', auth()->user()->email)->exists();
            if (! $linked) {
                return redirect()->route('student.pending');
            }
        }
        return $next($request);
    }
}
