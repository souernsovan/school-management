<x-app-layout>

    <x-slot name="header">Student Profile</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Top bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('students.index') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Students
            </a>
            @can('manage students')
            <a href="{{ route('students.edit', $student) }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Student
            </a>
            @endcan
        </div>

        {{-- Avatar + Name strip --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 sm:px-6 py-4 flex flex-wrap items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shrink-0 shadow">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</h2>
                <p class="text-sm text-slate-500">Student ID #{{ $student->id }}</p>
            </div>
            @if($student->schoolClass)
                <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-100">
                    {{ $student->schoolClass->name }} — {{ $student->schoolClass->section }}
                </span>
            @endif
        </div>

        {{-- Three info cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Personal Information --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800 text-sm">Personal Information</h3>
                </div>
                <div class="px-5 py-4 space-y-4 text-sm">

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Full Name</p>
                        <p class="font-medium text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Gender</p>
                            @if($student->gender === 'Male')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-sky-50 text-sky-600 text-xs font-semibold border border-sky-100">Male</span>
                            @elseif($student->gender === 'Female')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-pink-50 text-pink-600 text-xs font-semibold border border-pink-100">Female</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Age</p>
                            <p class="font-medium text-slate-800">
                                @if($student->dob)
                                    {{ \Carbon\Carbon::parse($student->dob)->age }} years old
                                @else —
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Date of Birth</p>
                        <p class="font-medium text-slate-800">
                            {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('M d, Y') : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Phone</p>
                        <p class="font-medium text-slate-800">{{ $student->phone ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Address</p>
                        <p class="font-medium text-slate-800 leading-relaxed">{{ $student->address ?? '—' }}</p>
                    </div>

                </div>
            </div>

            {{-- Academic Details --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800 text-sm">Academic Details</h3>
                </div>
                <div class="px-5 py-4 space-y-4 text-sm">

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Email</p>
                        <p class="font-medium text-slate-800 break-all">{{ $student->email ?? '—' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Class</p>
                        @if($student->schoolClass)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold border border-indigo-100">
                                {{ $student->schoolClass->name }}
                            </span>
                        @else
                            <span class="text-slate-400">Not assigned</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Section</p>
                        @if($student->schoolClass?->section)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 text-xs font-semibold border border-violet-100">
                                {{ $student->schoolClass->section }}
                            </span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Student Status</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-semibold border border-green-100">
                            Active
                        </span>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Enrolled</p>
                        <p class="font-medium text-slate-800">
                            {{ \Carbon\Carbon::parse($student->created_at)->format('M d, Y') }}
                            <span class="text-xs text-slate-400">({{ \Carbon\Carbon::parse($student->created_at)->diffForHumans() }})</span>
                        </p>
                    </div>

                </div>
            </div>

            {{-- Attendance Overview --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800 text-sm">Attendance Overview</h3>
                </div>
                <div class="px-5 py-4 space-y-3 text-sm">

                    @if($stats['total'] === 0)
                        <div class="flex flex-col items-center justify-center py-6 text-slate-400 gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-xs">No attendance records yet</p>
                        </div>
                    @else
                        {{-- Rate bar --}}
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <p class="text-xs text-slate-400">Attendance Rate</p>
                                <p class="text-xs font-bold text-slate-700">{{ round($stats['present'] / $stats['total'] * 100) }}%</p>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden flex">
                                @if($stats['present'])   <div class="bg-green-500 h-full" style="width:{{ round($stats['present']/$stats['total']*100) }}%"></div>@endif
                                @if($stats['late'])      <div class="bg-amber-400 h-full" style="width:{{ round($stats['late']/$stats['total']*100) }}%"></div>@endif
                                @if($stats['permission'])<div class="bg-blue-400 h-full"  style="width:{{ round($stats['permission']/$stats['total']*100) }}%"></div>@endif
                                @if($stats['absent'])    <div class="bg-red-400 h-full"   style="width:{{ round($stats['absent']/$stats['total']*100) }}%"></div>@endif
                            </div>
                        </div>

                        <div class="pt-1 space-y-2.5">
                            @foreach([
                                ['Present',    $stats['present'],    'bg-green-50 text-green-700 border-green-100',  'bg-green-500'],
                                ['Absent',     $stats['absent'],     'bg-red-50 text-red-700 border-red-100',        'bg-red-500'],
                                ['Late',       $stats['late'],       'bg-amber-50 text-amber-700 border-amber-100',  'bg-amber-500'],
                                ['Permission', $stats['permission'], 'bg-blue-50 text-blue-700 border-blue-100',     'bg-blue-500'],
                            ] as [$label, $count, $badge, $dot])
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $dot }} shrink-0"></span>
                                        <span class="text-slate-600 text-xs">{{ $label }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400">{{ round($count / $stats['total'] * 100) }}%</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $badge }}">{{ $count }}</span>
                                    </div>
                                </div>
                            @endforeach
                            <div class="pt-1 border-t border-slate-100 flex justify-between">
                                <span class="text-xs text-slate-400">Total Records</span>
                                <span class="text-xs font-bold text-slate-700">{{ $stats['total'] }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Attendance History --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Attendance History</h3>
                @can('manage attendance')
                <a href="{{ route('attendances.create') }}"
                   class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-700 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Take Attendance
                </a>
                @endcan
            </div>

            @if($attendances->isEmpty())
                <div class="px-4 sm:px-6 py-14 flex flex-col items-center gap-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-medium">No attendance records found</p>
                    <p class="text-xs">Records will appear here once attendance is taken</p>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-slate-400 uppercase text-xs tracking-wider border-b border-slate-100">
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold">Subject</th>
                            <th class="px-6 py-3 font-semibold">Teacher</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold">Remark</th>
                            @can('manage attendance')
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $att)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition">

                                <td class="px-6 py-3.5">
                                    <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($att->attendance_date)->format('M d, Y') }}</p>
                                    <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($att->attendance_date)->format('l') }}</p>
                                </td>

                                <td class="px-6 py-3.5 text-slate-600">{{ $att->subject->name ?? '—' }}</td>

                                <td class="px-6 py-3.5 text-slate-600">
                                    {{ trim(($att->teacher->first_name ?? '') . ' ' . ($att->teacher->last_name ?? '')) ?: '—' }}
                                </td>

                                <td class="px-6 py-3.5">
                                    @php
                                        $badges = [
                                            'Present'    => 'bg-green-50 text-green-700 border border-green-100',
                                            'Absent'     => 'bg-red-50 text-red-700 border border-red-100',
                                            'Late'       => 'bg-amber-50 text-amber-700 border border-amber-100',
                                            'Permission' => 'bg-blue-50 text-blue-700 border border-blue-100',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badges[$att->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $att->status }}
                                    </span>
                                </td>

                                <td class="px-6 py-3.5 text-slate-400 text-xs">{{ $att->remark ?: '—' }}</td>

                                @can('manage attendance')
                                <td class="px-6 py-3.5 text-right">
                                    <a href="{{ route('attendances.edit', $att) }}"
                                       class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>
                                </td>
                                @endcan

                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
                @if($attendances->hasPages())
                    <div class="px-4 sm:px-6 py-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm text-slate-500">
                            Showing {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} of {{ $attendances->total() }} records
                        </p>
                        {{ $attendances->links() }}
                    </div>
                @endif
            @endif

        </div>

    </div>

</x-app-layout>
