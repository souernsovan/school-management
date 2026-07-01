<x-app-layout>

    <x-slot name="header">Timetable Management</x-slot>

    <style>
    @media (min-width: 768px) {
        .tt-day-col  { display: block !important; }
        .tt-day-head { display: flex !important; }
    }
    .tt-tabs::-webkit-scrollbar { display: none; }
    .tt-tabs { scrollbar-width: none; -ms-overflow-style: none; }
    .tt-entry { transition: filter .15s, transform .15s; }
    .tt-entry:hover { filter: brightness(1.08); transform: translateY(-1px); }
    </style>

    <div class="p-4 sm:p-6 space-y-4">

        {{-- ── Top bar ─────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 4h10M5 11h14M5 19h14M5 15h14"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800 leading-tight">Timetable</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Weekly class schedule</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Class selector --}}
                <form method="GET" action="{{ route('timetables.index') }}">
                    <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <select name="class_id" onchange="this.form.submit()"
                                class="pl-9 pr-9 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 font-medium focus:border-indigo-500 focus:ring-indigo-500 shadow-sm appearance-none">
                            <option value="">— Select class —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}@if($class->section) — {{ $class->section }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                @can('manage timetables')
                <a href="{{ route('timetables.create', ['class_id' => $classId, 'week_start' => $weekStart->toDateString()]) }}"
                   class="inline-flex items-center gap-1.5 bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition font-semibold text-sm shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Entry
                </a>
                @endcan
            </div>
        </div>

        {{-- Success --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── Empty states ─────────────────────────────────────── --}}
        @if(!$classId)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 flex flex-col items-center gap-4 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 4h10M5 11h14M5 19h14M5 15h14"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-700">No class selected</p>
                    <p class="text-sm text-slate-400 mt-1">Choose a class above to see its timetable</p>
                </div>
            </div>

        @elseif($entries->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 flex flex-col items-center gap-4 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 4h10M5 11h14M5 19h14M5 15h14"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-700">No entries yet</p>
                    <p class="text-sm text-slate-400 mt-1">This class has no timetable entries</p>
                </div>
                @can('manage timetables')
                <a href="{{ route('timetables.create', ['class_id' => $classId, 'week_start' => $weekStart->toDateString()]) }}"
                   class="inline-flex items-center gap-1.5 bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 transition font-semibold text-sm shadow-sm mt-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add First Entry
                </a>
                @endcan
            </div>

        @else
            @php
                $palette = [
                    '#6366f1','#8b5cf6','#0ea5e9','#10b981','#f59e0b',
                    '#ef4444','#ec4899','#14b8a6','#f97316','#84cc16',
                ];
                $subjectColors = [];
                $ci = 0;
                foreach ($entries as $e) {
                    if ($e->subject && !isset($subjectColors[$e->subject_id])) {
                        $subjectColors[$e->subject_id] = $palette[$ci % count($palette)];
                        $ci++;
                    }
                }

                $showDays = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
                if ($entries->where('day','Saturday')->isNotEmpty()) $showDays[] = 'Saturday';

                // Server-side dates per day
                $dayOffsets = ['Monday'=>0,'Tuesday'=>1,'Wednesday'=>2,'Thursday'=>3,'Friday'=>4,'Saturday'=>5,'Sunday'=>6];
                $todayDate  = \Carbon\Carbon::today();
                $weekDates  = [];
                foreach ($showDays as $d) {
                    $weekDates[$d] = $weekStart->copy()->addDays($dayOffsets[$d]);
                }
                $todayDayName = $todayDate->format('l'); // e.g. "Tuesday"

                $hourHeight = 100;
                $dayStart   = 7;
                $dayEnd     = 18;
                foreach ($entries as $e) {
                    $sh = (int) substr($e->start_time, 0, 2);
                    $eh = (int) substr($e->end_time,   0, 2);
                    if ($sh < $dayStart) $dayStart = $sh;
                    if ($eh > $dayEnd)   $dayEnd   = $eh + 1;
                }
                $totalHeight = ($dayEnd - $dayStart) * $hourHeight;
                $defaultDay  = in_array($todayDayName, $showDays) && $isCurrentWeek ? $todayDayName : $showDays[0];

                // Week label
                $ws = $weekStart;
                $we = $weekEnd;
                $weekLabel = $ws->format('M j') . ' – ' . ($ws->month === $we->month ? $we->format('j') : $we->format('M j')) . ', ' . $ws->format('Y');
            @endphp

            {{-- Alpine wrapper (modal + mobile day tabs only) --}}
            <div class="space-y-4"
                 x-data="{
                    show: false, entry: {},
                    activeDay: '{{ $defaultDay }}',
                    open(d){ this.entry = JSON.parse(d); this.show = true; },
                    close(){ this.show = false; }
                 }"
                 @keydown.escape.window="close()">

                {{-- ── Legend + mobile day tabs ──────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                    {{-- Legend chips --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($subjectColors as $sid => $color)
                            @php $subj = $entries->firstWhere('subject_id', $sid)?->subject @endphp
                            @if($subj)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm tracking-wide"
                                      style="background:{{ $color }}">
                                    {{ $subj->name }}
                                </span>
                            @endif
                        @endforeach
                        @if($entries->where('entry_type','exam')->isNotEmpty())
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm tracking-wide"
                                  style="background:#dc2626">
                                Exam
                            </span>
                        @endif
                        @if($entries->where('entry_type','break')->isNotEmpty())
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm tracking-wide"
                                  style="background:#92400e">
                                Break
                            </span>
                        @endif
                    </div>

                    {{-- Mobile day tabs --}}
                    <div class="tt-tabs md:hidden flex gap-2 overflow-x-auto pb-0.5">
                        @foreach($showDays as $day)
                            @php $isToday = $isCurrentWeek && $day === $todayDayName; @endphp
                            <button type="button"
                                    @click="activeDay = '{{ $day }}'"
                                    :class="activeDay === '{{ $day }}'
                                        ? 'bg-indigo-600 text-white shadow-sm'
                                        : '{{ $isToday ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'bg-white text-slate-500 border border-slate-200 hover:border-indigo-300 hover:text-indigo-600' }}'"
                                    class="relative flex flex-col items-center px-3.5 pt-2.5 pb-2 rounded-xl shrink-0 transition-all min-w-[3.5rem]">
                                <span class="text-xs font-bold uppercase tracking-wide">{{ substr($day, 0, 3) }}</span>
                                <span class="text-base font-bold leading-tight mt-0.5">{{ $weekDates[$day]->format('j') }}</span>
                                @if($isToday)
                                    <span class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-400 border border-white"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- ── Week / Month / Year navigator ─────────────── --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 px-5 py-5 flex flex-wrap items-center justify-between gap-4">

                    {{-- Prev controls --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('timetables.index', $prevYear) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition"
                           title="Previous year">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                            Year
                        </a>
                        <a href="{{ route('timetables.index', $prevMonth) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition"
                           title="Previous month">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Month
                        </a>
                        <a href="{{ route('timetables.index', $prevWeek) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition"
                           title="Previous week">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Week
                        </a>
                    </div>

                    {{-- Current range label --}}
                    <div class="text-center flex-1 min-w-[180px]">
                        <p class="text-base font-bold text-slate-800 leading-tight">{{ $weekLabel }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $weekStart->format('F Y') }}</p>
                        <a href="{{ route('timetables.index', $thisWeek) }}"
                           class="mt-2 inline-block px-4 py-1 rounded-full border text-xs font-semibold transition {{ $isCurrentWeek ? 'bg-indigo-100 text-indigo-600 border-indigo-200' : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300 hover:text-indigo-600' }}">
                            Today
                        </a>
                    </div>

                    {{-- Next controls --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('timetables.index', $nextWeek) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition"
                           title="Next week">
                            Week
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('timetables.index', $nextMonth) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition"
                           title="Next month">
                            Month
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('timetables.index', $nextYear) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300 transition"
                           title="Next year">
                            Year
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                {{-- ── Calendar grid ─────────────────────────────── --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">

                        {{-- Day headers --}}
                        <div class="flex border-b-2 border-slate-100">
                            <div class="w-12 md:w-20 shrink-0 bg-slate-50/70 border-r border-slate-100 flex flex-col items-center justify-end pb-3">
                                <span class="text-xs text-slate-300 font-medium hidden md:block">{{ $weekStart->format('Y') }}</span>
                            </div>
                            @foreach($showDays as $day)
                                @php $isToday = $isCurrentWeek && $day === $todayDayName; @endphp
                                <div class="tt-day-head flex-1 flex flex-col items-center justify-center py-4 border-l border-slate-100 {{ $isToday ? 'bg-indigo-50/70' : 'bg-slate-50/40' }}"
                                     x-show="activeDay === '{{ $day }}'">
                                    {{-- Day name --}}
                                    <span class="text-xs font-semibold uppercase tracking-widest {{ $isToday ? 'text-indigo-500' : 'text-slate-400' }}">
                                        <span class="md:hidden">{{ substr($day, 0, 3) }}</span>
                                        <span class="hidden md:inline">{{ $day }}</span>
                                    </span>
                                    {{-- Date circle --}}
                                    <div class="mt-1.5 w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors {{ $isToday ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-700' }}">
                                        {{ $weekDates[$day]->format('j') }}
                                    </div>
                                    {{-- Month label --}}
                                    <span class="text-xs mt-1 {{ $isToday ? 'text-indigo-400 font-semibold' : 'text-slate-400' }}">
                                        {{ $weekDates[$day]->format('M') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Body --}}
                        <div class="flex overflow-y-auto" style="max-height: 80vh;">

                            {{-- Time labels --}}
                            <div class="w-12 md:w-20 shrink-0 relative bg-slate-50/50 border-r border-slate-100"
                                 style="height: {{ $totalHeight }}px;">
                                @for($h = $dayStart; $h <= $dayEnd; $h++)
                                    <div class="absolute w-full text-right select-none"
                                         style="top: {{ ($h - $dayStart) * $hourHeight - 9 }}px; padding-right: 10px; line-height:1;">
                                        <span class="text-xs text-slate-400 font-medium">
                                            {{ $h < 12 ? $h.($h < 10 ? ':00' : ':00') : ($h === 12 ? '12' : ($h-12).':00') }}
                                        </span>
                                        <span class="text-xs text-slate-300 font-normal hidden md:inline">{{ $h < 12 ? 'AM' : 'PM' }}</span>
                                    </div>
                                @endfor
                            </div>

                            {{-- Day columns --}}
                            <div class="flex flex-1 relative" id="calendar-body">

                                {{-- Current time line --}}
                                <div id="time-line" class="absolute left-0 right-0 z-20 pointer-events-none hidden" style="top:0">
                                    <div class="flex items-center">
                                        <div class="w-2.5 h-2.5 rounded-full bg-red-500 ring-2 ring-red-200 -ml-1.5 shrink-0"></div>
                                        <div class="flex-1 h-0.5 bg-red-400/60"></div>
                                    </div>
                                </div>

                                @foreach($showDays as $day)
                                    @php $isDayToday = $isCurrentWeek && $day === $todayDayName; @endphp
                                    <div class="tt-day-col flex-1 relative border-l border-slate-100 min-w-0 {{ $isDayToday ? 'bg-indigo-50/20' : '' }}"
                                         x-show="activeDay === '{{ $day }}'"
                                         style="height: {{ $totalHeight }}px;">

                                        {{-- Hour lines --}}
                                        @for($h = $dayStart; $h <= $dayEnd; $h++)
                                            <div class="absolute left-0 right-0 border-t {{ $h === 12 ? 'border-slate-200' : 'border-slate-100' }}"
                                                 style="top: {{ ($h - $dayStart) * $hourHeight }}px;"></div>
                                        @endfor

                                        {{-- Half-hour lines --}}
                                        @for($h = $dayStart; $h < $dayEnd; $h++)
                                            <div class="absolute left-0 right-0 border-t border-dashed border-slate-100/70"
                                                 style="top: {{ ($h - $dayStart) * $hourHeight + $hourHeight / 2 }}px;"></div>
                                        @endfor

                                        {{-- Entries --}}
                                        @foreach($entries->where('day', $day)->sortBy('start_time') as $entry)
                                            @php
                                                [$sh, $sm] = array_pad(explode(':', substr($entry->start_time, 0, 5)), 2, '0');
                                                [$eh, $em] = array_pad(explode(':', substr($entry->end_time,   0, 5)), 2, '0');
                                                $top      = ((int)$sh - $dayStart) * $hourHeight + round((int)$sm * $hourHeight / 60);
                                                $height   = max(28, ((int)$eh - (int)$sh) * $hourHeight + round(((int)$em - (int)$sm) * $hourHeight / 60));
                                                $isExam   = $entry->entry_type === 'exam';
                                                $isBreak  = $entry->entry_type === 'break';
                                                $color    = $isExam ? '#dc2626' : ($isBreak ? '#92400e' : ($subjectColors[$entry->subject_id] ?? '#6366f1'));
                                                $examSubj = $entry->subject?->name;
                                                $label    = $isExam
                                                    ? (($entry->exam_type ?? 'Exam') . ($examSubj ? ': '.$examSubj : ''))
                                                    : ($isBreak ? $entry->title : ($entry->subject?->name ?? '—'));
                                                $startFmt = \Carbon\Carbon::parse($entry->start_time)->format('g:i');
                                                $endFmt   = \Carbon\Carbon::parse($entry->end_time)->format('g:i A');
                                                $roomName = $entry->schoolClass?->room ?? '';
                                                $teacherName = trim(($entry->teacher?->first_name ?? '') . ' ' . ($entry->teacher?->last_name ?? ''));
                                                $entryInfo = json_encode([
                                                    'label'   => $label,
                                                    'color'   => $color,
                                                    'teacher' => $teacherName,
                                                    'room'    => $roomName,
                                                    'start'   => $startFmt,
                                                    'end'     => $endFmt,
                                                    'day'     => $day,
                                                    'type'    => $isExam ? 'Exam' : ($isBreak ? 'Break' : 'Class'),
                                                    'isBreak' => (bool) $isBreak,
                                                    'editUrl' => route('timetables.edit', $entry),
                                                ]);
                                            @endphp

                                            <div class="tt-entry group text-white z-10 shadow-md"
                                                 style="position:absolute; top:{{ $top + 2 }}px; left:7px; right:7px; height:{{ $height - 4 }}px; background:{{ $color }}; border-radius:12px; overflow:hidden; cursor:pointer;"
                                                 data-info="{{ $entryInfo }}"
                                                 @click="open($el.dataset.info)">

                                                {{-- Left accent stripe --}}
                                                <div style="position:absolute; left:0; top:0; bottom:0; width:4px; background:rgba(255,255,255,0.35); border-radius:12px 0 0 12px;"></div>

                                                {{-- Content --}}
                                                <div style="position:absolute; inset:0; padding: {{ $height > 50 ? '9px 12px 9px 14px' : '6px 9px 6px 13px' }}; display:flex; flex-direction:column; overflow:hidden;">
                                                    {{-- Title row --}}
                                                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:5px;">
                                                        <p style="font-size:{{ $height > 50 ? '13' : '11.5' }}px; font-weight:700; line-height:1.3; overflow:hidden; display:-webkit-box; -webkit-line-clamp:{{ $height > 70 ? 2 : 1 }}; -webkit-box-orient:vertical; margin:0; flex:1; min-width:0;">{{ $label }}</p>
                                                        @if($height > 36)
                                                        <span style="font-size:10px; font-weight:600; white-space:nowrap; flex-shrink:0; background:rgba(0,0,0,0.2); border-radius:5px; padding:2px 6px; line-height:1.6; margin-top:1px;">{{ $startFmt }}–{{ $endFmt }}</span>
                                                        @endif
                                                    </div>
                                                    @if(!$isBreak && $height > 62 && $teacherName)
                                                        <p style="font-size:11px; opacity:0.9; margin:4px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                            <svg style="display:inline;vertical-align:-1px;margin-right:3px;" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                            {{ $teacherName }}
                                                        </p>
                                                    @endif
                                                    @if(!$isBreak && $height > 82 && $roomName)
                                                        <p style="font-size:10.5px; opacity:0.78; margin:3px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                            <svg style="display:inline;vertical-align:-1px;margin-right:3px;" width="9" height="9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                            {{ $roomName }}
                                                        </p>
                                                    @endif
                                                </div>

                                                {{-- Hover action buttons --}}
                                                @can('manage timetables')
                                                <div class="absolute top-1.5 right-1.5 hidden group-hover:flex gap-1" @click.stop>
                                                    <a href="{{ route('timetables.edit', $entry) }}"
                                                       class="w-6 h-6 rounded-lg bg-white/25 hover:bg-white/50 flex items-center justify-center transition backdrop-blur-sm"
                                                       title="Edit">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route('timetables.destroy', $entry) }}" method="POST"
                                                          data-swal-confirm
                                                          data-swal-title="Delete entry?"
                                                          data-swal-text="Remove this timetable entry? This cannot be undone."
                                                          data-swal-confirm-text="Yes, delete">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                                class="w-6 h-6 rounded-lg bg-white/25 hover:bg-red-500 flex items-center justify-center transition backdrop-blur-sm"
                                                                title="Delete">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                                @endcan
                                            </div>

                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── Detail Modal ─────────────────────────────────── --}}
                <div x-show="show" x-cloak
                     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-3 sm:p-4"
                     @click.self="close()">

                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

                    <div class="relative w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden bg-white"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
                         @click.stop>

                        {{-- Coloured header --}}
                        <div class="px-6 py-5 text-white relative overflow-hidden" :style="'background:' + (entry.color ?? '#6366f1')">
                            <div class="absolute inset-0 opacity-10" style="background:url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 80 80\"><circle cx=\"40\" cy=\"40\" r=\"35\" fill=\"none\" stroke=\"white\" stroke-width=\"1\"/></svg>') center/cover"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-widest opacity-75 mb-1.5" x-text="entry.type"></p>
                                    <h3 class="text-xl font-bold leading-snug" x-text="entry.label"></h3>
                                    <div class="flex items-center gap-1.5 mt-2 opacity-85">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 4h10M5 11h14M5 19h14M5 15h14"/>
                                        </svg>
                                        <p class="text-sm font-medium" x-text="entry.day"></p>
                                    </div>
                                </div>
                                <button @click="close()"
                                        class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors mt-0.5"
                                        style="background:rgba(0,0,0,0.15)" onmouseover="this.style.background='rgba(0,0,0,0.25)'" onmouseout="this.style.background='rgba(0,0,0,0.15)'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="px-6 py-4 space-y-2.5">
                            {{-- Time --}}
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :style="'background:' + (entry.color ?? '#6366f1') + '20'">
                                    <svg class="w-4 h-4" :style="'color:' + (entry.color ?? '#6366f1')" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Time</p>
                                    <p class="text-sm font-bold text-slate-800" x-text="entry.start + ' – ' + entry.end"></p>
                                </div>
                            </div>
                            {{-- Teacher --}}
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50" x-show="entry.teacher && !entry.isBreak">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :style="'background:' + (entry.color ?? '#6366f1') + '20'">
                                    <svg class="w-4 h-4" :style="'color:' + (entry.color ?? '#6366f1')" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Teacher</p>
                                    <p class="text-sm font-bold text-slate-800" x-text="entry.teacher"></p>
                                </div>
                            </div>
                            {{-- Room --}}
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50" x-show="entry.room && !entry.isBreak">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :style="'background:' + (entry.color ?? '#6366f1') + '20'">
                                    <svg class="w-4 h-4" :style="'color:' + (entry.color ?? '#6366f1')" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Room</p>
                                    <p class="text-sm font-bold text-slate-800" x-text="entry.room"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 pb-5 pt-2 flex items-center gap-2">
                            @can('manage timetables')
                            <a x-show="entry.editUrl" :href="entry.editUrl"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold text-white transition shadow-sm"
                               :style="'background:' + (entry.color ?? '#6366f1')">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Entry
                            </a>
                            @endcan
                            <button @click="close()"
                                    class="flex-1 rounded-xl border-2 border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                                Close
                            </button>
                        </div>

                    </div>
                </div>

            </div>{{-- /Alpine --}}
        @endif
    </div>

    <script>
    (function () {
        var dayStart   = {{ $dayStart ?? 7 }};
        var hourHeight = {{ $hourHeight ?? 100 }};
        var dayEnd     = {{ $dayEnd ?? 18 }};
        function tick() {
            var el = document.getElementById('time-line');
            if (!el) return;
            var now = new Date(), h = now.getHours(), m = now.getMinutes();
            if (h < dayStart || h >= dayEnd) { el.classList.add('hidden'); return; }
            el.style.top = ((h - dayStart) * hourHeight + Math.round(m * hourHeight / 60)) + 'px';
            el.classList.remove('hidden');
        }
        tick();
        setInterval(tick, 30000);
    })();
    </script>

</x-app-layout>
