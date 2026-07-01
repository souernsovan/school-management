<x-app-layout>
    <x-slot name="header">Attendance</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Attendance</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $sessions->total() }} session{{ $sessions->total() !== 1 ? 's' : '' }} found</p>
            </div>
            @can('manage attendance')
            <div class="flex flex-wrap items-center gap-2">
                {{-- Export --}}
                <div x-data="{ open: false, cls: '' }" class="relative">
                    <button @click="open = !open" @keydown.escape.window="open = false"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2.5 text-sm font-semibold hover:bg-emerald-100 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                        Export
                        <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute right-0 top-full mt-2 z-30 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Export Attendance</p>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Select Class</label>
                            <select x-model="cls" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}{{ $class->section ? ' — '.$class->section : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a :href="'{{ route('attendances.export') }}' + (cls ? '?class_id=' + cls : '')"
                           @click="open = false"
                           class="flex items-center justify-center gap-2 w-full bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-emerald-700 transition">
                            Download Excel
                        </a>
                    </div>
                </div>

                <a href="{{ route('attendances.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Take Attendance
                </a>
            </div>
            @endcan
        </div>

        @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Today's summary --}}
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Today's Sessions</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $todayStats['sessions'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Present Today</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $todayStats['present'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Absent Today</p>
                <p class="mt-2 text-2xl font-bold text-red-600">{{ $todayStats['absent'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Late / Permission</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $todayStats['late'] }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('attendances.index') }}"
                  class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Class</label>
                    <select name="class_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All classes</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}{{ $class->section ? ' — '.$class->section : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[160px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="shrink-0 flex gap-2">
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['class_id','date']))
                    <a href="{{ route('attendances.index') }}"
                       class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Sessions table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-3.5 font-semibold">Date</th>
                            <th class="px-5 py-3.5 font-semibold">Class</th>
                            <th class="px-5 py-3.5 font-semibold">Teacher</th>
                            <th class="px-5 py-3.5 font-semibold">Attendance</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($sessions as $session)
                        @php
                            $total  = max((int) $session->total, 1);
                            $pPct   = round($session->present_count    / $total * 100);
                            $aPct   = round($session->absent_count     / $total * 100);
                            $oPct   = round(($session->late_count + $session->permission_count) / $total * 100);
                            $isToday = $session->attendance_date === now()->toDateString();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition group">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-slate-900">
                                        {{ \Carbon\Carbon::parse($session->attendance_date)->format('d M Y') }}
                                    </p>
                                    @if($isToday)
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600">Today</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($session->attendance_date)->format('l') }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-800">{{ $session->class_name }}</span>
                                @if($session->class_section)
                                <span class="text-slate-400"> — {{ $session->class_section }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $session->teacher_name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 min-w-[200px]">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex gap-3">
                                            <span class="text-emerald-600 font-semibold">{{ $session->present_count }} P</span>
                                            <span class="text-red-600 font-semibold">{{ $session->absent_count }} A</span>
                                            @if($session->late_count + $session->permission_count > 0)
                                            <span class="text-amber-600 font-semibold">{{ $session->late_count + $session->permission_count }} L</span>
                                            @endif
                                        </div>
                                        <span class="text-slate-400">{{ $session->total }} total</span>
                                    </div>
                                    <div class="flex h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="bg-emerald-500" style="width:{{ $pPct }}%"></div>
                                        <div class="bg-red-500"     style="width:{{ $aPct }}%"></div>
                                        <div class="bg-amber-400"   style="width:{{ $oPct }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @can('manage attendance')
                                    <a href="{{ route('attendances.session.edit', [$session->class_id, $session->attendance_date]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit Session
                                    </a>

                                    <form action="{{ route('attendances.session.destroy', [$session->class_id, $session->attendance_date]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this entire session? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg p-2 text-red-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete session">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/></svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="mx-auto max-w-sm">
                                    <svg class="mx-auto h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="mt-3 text-sm font-semibold text-slate-700">No sessions found</p>
                                    <p class="mt-1 text-sm text-slate-400">Try adjusting the filters or take a new attendance session.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($sessions->hasPages())
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">
                Showing {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} of {{ $sessions->total() }} sessions
            </p>
            <div>{{ $sessions->links() }}</div>
        </div>
        @endif

    </div>
</x-app-layout>
