<x-app-layout>
    <x-slot name="header">Teacher Profile</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Top bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('teachers.index') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Teachers
            </a>
            @can('manage teachers')
            <a href="{{ route('teachers.edit', $teacher) }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Teacher
            </a>
            @endcan
        </div>

        {{-- Avatar + name --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4 flex flex-wrap items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white text-2xl font-bold shrink-0 shadow">
                {{ strtoupper(substr($teacher->first_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-slate-800">{{ $teacher->first_name }} {{ $teacher->last_name }}</h2>
                <p class="text-sm text-slate-500">{{ $teacher->specialization ?? $teacher->qualification ?? 'Teacher' }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold border
                {{ $teacher->status === 'Active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                {{ $teacher->status ?? 'Active' }}
            </span>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-teal-600">{{ $classes->count() }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Classes</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $subjects->count() }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Subjects</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ $students->count() }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Students</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-slate-800">{{ $recentAttendances->count() }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Recent Sessions</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Personal info --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800 text-sm">Personal Information</h3>
                </div>
                <div class="px-5 py-4 space-y-3 text-sm">
                    @foreach([
                        ['Email',           $teacher->email],
                        ['Phone',           $teacher->phone],
                        ['Gender',          $teacher->gender],
                        ['Date of Birth',   $teacher->dob ? \Carbon\Carbon::parse($teacher->dob)->format('d M Y') : null],
                        ['Hire Date',       $teacher->hire_date ? \Carbon\Carbon::parse($teacher->hire_date)->format('d M Y') : null],
                        ['Qualification',   $teacher->qualification],
                        ['Specialization',  $teacher->specialization],
                        ['Address',         $teacher->address],
                    ] as [$label, $value])
                    @if($value)
                    <div>
                        <p class="text-xs text-slate-400">{{ $label }}</p>
                        <p class="font-medium text-slate-700 mt-0.5">{{ $value }}</p>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>

            {{-- Subjects & Classes --}}
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-semibold text-slate-800 text-sm">Subjects Taught</h3>
                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg">{{ $subjects->count() }}</span>
                    </div>
                    <div class="px-5 py-4">
                        @forelse($subjects as $subject)
                        <div class="flex items-center gap-2 py-1.5 border-b border-slate-50 last:border-0">
                            <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>
                            <span class="text-sm text-slate-700">{{ $subject->name }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-slate-400 py-2">No subjects assigned via timetable</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-semibold text-slate-800 text-sm">Classes</h3>
                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg">{{ $classes->count() }}</span>
                    </div>
                    <div class="px-5 py-4">
                        @forelse($classes as $class)
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-50 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-teal-400 shrink-0"></span>
                                <span class="text-sm text-slate-700">{{ $class->name }}{{ $class->section ? ' — '.$class->section : '' }}</span>
                            </div>
                            <span class="text-xs text-slate-400">{{ $class->students_count ?? $class->students()->count() }} students</span>
                        </div>
                        @empty
                        <p class="text-sm text-slate-400 py-2">No classes assigned via timetable</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recent attendance sessions --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800 text-sm">Recent Attendance Sessions</h3>
                    <a href="{{ route('attendances.index') }}" class="text-xs text-blue-600 hover:underline">View all →</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recentAttendances as $att)
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">
                                {{ $att->student->first_name ?? '—' }} {{ $att->student->last_name ?? '' }}
                            </p>
                            <p class="text-xs text-slate-400 truncate">
                                {{ $att->schoolClass->name ?? '—' }}
                                @if($att->subject) · {{ $att->subject->name }}@endif
                                · {{ \Carbon\Carbon::parse($att->attendance_date)->format('d M Y') }}
                            </p>
                        </div>
                        @php
                            $statusColor = match($att->status) {
                                'Present'    => 'emerald',
                                'Absent'     => 'red',
                                'Late'       => 'amber',
                                'Permission' => 'blue',
                                default      => 'slate',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border border-{{ $statusColor }}-200 shrink-0">
                            {{ $att->status }}
                        </span>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-400">No attendance sessions yet</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
