<x-app-layout>

    <x-slot name="header">Attendance Management</x-slot>

    <div class="p-4 sm:p-6 space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Attendance</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $attendances->total() }} record{{ $attendances->total() !== 1 ? 's' : '' }} found</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('manage attendance')
                {{-- Export popover --}}
                <div x-data="{ open: false, cls: '' }" class="relative">
                    <button @click="open = !open" @keydown.escape.window="open = false"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2.5 text-sm font-semibold hover:bg-emerald-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                        Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute left-0 top-full mt-2 z-30 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Export Attendance</p>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Select Class</label>
                            <select x-model="cls" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}{{ $class->section ? ' — '.$class->section : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        @php $attFilters = http_build_query(request()->only('search','date','status','date_from','date_to')); @endphp
                        <a :href="'{{ route('attendances.export') }}' + (cls ? '?class_id=' + cls + ({{ json_encode($attFilters ? '&'.$attFilters : '') }}) : {{ json_encode($attFilters ? '?'.$attFilters : '') }})"
                           @click="open = false"
                           class="flex items-center justify-center gap-2 w-full bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-emerald-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                            Download Excel
                        </a>
                    </div>
                </div>
                <a href="{{ route('attendances.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 hover:bg-blue-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Take Attendance
                </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Total</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $attendances->total() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Present</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $attendances->where('status', 'Present')->count() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Absent</p>
                <p class="mt-2 text-2xl font-bold text-red-600">{{ $attendances->where('status', 'Absent')->count() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Late / Permission</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">
                    {{ $attendances->whereIn('status', ['Late', 'Permission'])->count() }}
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('attendances.index') }}" class="grid gap-3 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto_auto]">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 11 5 11a6 6 0 0112 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <select name="class_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}@if($class->section) - {{ $class->section }}@endif
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date" value="{{ request('date') }}"
                       class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">

                <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All status</option>
                    @foreach(['Present','Absent','Late','Permission'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    Filter
                </button>

                @if(request()->hasAny(['search','class_id','date','status']))
                    <a href="{{ route('attendances.index') }}"
                       class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-3.5 font-semibold">Student</th>
                            <th class="px-5 py-3.5 font-semibold">Class</th>
                            <th class="px-5 py-3.5 font-semibold">Date</th>
                            <th class="px-5 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold">Teacher</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($attendances as $attendance)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-sm font-bold text-indigo-600">
                                            {{ strtoupper(substr($attendance->student->first_name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">
                                                {{ $attendance->student->first_name ?? '' }} {{ $attendance->student->last_name ?? '' }}
                                            </p>
                                            <p class="text-xs text-slate-400">{{ $attendance->remark ?: 'No remark' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $attendance->schoolClass->name ?? '—' }}
                                    @if($attendance->schoolClass?->section) - {{ $attendance->schoolClass->section }} @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $colors = [
                                            'Present'    => 'bg-emerald-50 text-emerald-700',
                                            'Absent'     => 'bg-red-50 text-red-700',
                                            'Late'       => 'bg-amber-50 text-amber-700',
                                            'Permission' => 'bg-blue-50 text-blue-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $colors[$attendance->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $attendance->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $attendance->teacher->first_name ?? '' }} {{ $attendance->teacher->last_name ?? '' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('manage attendance')
                                            <a href="{{ route('attendances.edit', $attendance) }}"
                                               class="rounded-lg p-2 text-blue-500 hover:bg-blue-50 hover:text-blue-700 transition" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('attendances.destroy', $attendance) }}" method="POST"
                                                  data-swal-confirm
                                                  data-swal-title="Delete attendance?"
                                                  data-swal-text="Delete this attendance record? This cannot be undone."
                                                  data-swal-confirm-text="Yes, delete it">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg p-2 text-red-500 hover:bg-red-50 hover:text-red-700 transition" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <p class="text-sm font-semibold text-slate-700">No attendance records found</p>
                                        <p class="mt-1 text-sm text-slate-400">Try adjusting the filters or create a new attendance session.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($attendances->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Showing {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} of {{ $attendances->total() }} records
                </p>
                <div>{{ $attendances->links() }}</div>
            </div>
        @endif
    </div>

</x-app-layout>
