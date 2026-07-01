<x-student-layout>

    <div class="min-h-[calc(100vh-3.5rem)] bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1800px] space-y-5">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">My Timetable</h2>
            <p class="text-sm text-slate-500 mt-0.5">View your weekly schedule and upcoming exams.</p>
        </div>


        {{-- Controls bar --}}
        <div class="rounded-2xl border border-white/70 bg-white p-4 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
            <form method="GET" action="{{ route('student.timetable') }}" id="class-form">
                <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">
                <div class="max-w-none">
                    <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 mb-2">Class</label>
                    <select name="class_id" onchange="document.getElementById('class-form').submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— Select a class —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}@if($class->section) — {{ $class->section }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- Week navigator --}}
        @if($classId)
        @php
            $wsLabel = $weekStart->format('M j') . ' – ' . ($weekStart->month === $weekEnd->month ? $weekEnd->format('j') : $weekEnd->format('M j')) . ', ' . $weekStart->format('Y');
        @endphp
        <div class="rounded-[1.5rem] bg-blue-600 px-4 py-3 text-white shadow-[0_14px_30px_rgba(37,99,235,0.28)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('student.timetable', $prevMonth) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Month
                    </a>
                    <a href="{{ route('student.timetable', $prevWeek) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Week
                    </a>
                </div>

                <div class="flex-1 text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.35em] text-white/55">Current Week</p>
                    <p class="mt-0.5 text-xl font-bold leading-tight">{{ $wsLabel }}</p>
                    <p class="text-xs text-white/60">{{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('student.timetable', $nextWeek) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20">
                        Week
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('student.timetable', $nextMonth) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20">
                        Month
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== CLASS SCHEDULE ===== --}}
        <div>

            @if(!$classId)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 flex flex-col items-center gap-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 11h14M5 19h14M5 15h14"/>
                    </svg>
                    <p class="font-medium">Please select a class to view the schedule</p>
                </div>

            @elseif($entries->isEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 flex flex-col items-center gap-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 11h14M5 19h14M5 15h14"/>
                    </svg>
                    <p class="font-medium">No timetable entries for this class yet</p>
                </div>

            @else
                @php
                    $palette = ['#6366f1','#8b5cf6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#14b8a6','#f97316','#84cc16'];
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
                    $todayDayName = \Carbon\Carbon::today()->format('l');
                    $dayOffsets = ['Monday'=>0,'Tuesday'=>1,'Wednesday'=>2,'Thursday'=>3,'Friday'=>4,'Saturday'=>5,'Sunday'=>6];
                    $weekDates = [];
                    foreach ($showDays as $d) {
                        $weekDates[$d] = $weekStart->copy()->addDays($dayOffsets[$d]);
                    }

                    $hourHeight = 100;
                    $dayStart = 7;
                    $dayEnd   = 18;
                    foreach ($entries as $e) {
                        $sh = (int) substr($e->start_time, 0, 2);
                        $eh = (int) substr($e->end_time,   0, 2);
                        if ($sh < $dayStart) $dayStart = $sh;
                        if ($eh > $dayEnd)   $dayEnd   = $eh + 1;
                    }
                    $totalHeight = ($dayEnd - $dayStart) * $hourHeight;
                @endphp

                {{-- Modal + grid wrapper --}}
                <div x-data="{ show: false, entry: {}, open(d){ this.entry = JSON.parse(d); this.show = true; }, close(){ this.show = false; } }"
                     @keydown.escape.window="close()">

                {{-- Legend --}}
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 px-1">
                    @foreach($subjectColors as $sid => $color)
                        @php $subj = $entries->firstWhere('subject_id', $sid)?->subject @endphp
                        @if($subj)
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background:{{ $color }}"></span>
                                <span class="text-sm text-slate-600">{{ $subj->name }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if($entries->where('entry_type', 'exam')->isNotEmpty())
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full shrink-0" style="background:#dc2626"></span>
                            <span class="text-sm text-slate-600">Exam</span>
                        </div>
                    @endif
                    @if($entries->where('entry_type', 'break')->isNotEmpty())
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full shrink-0" style="background:#b45309"></span>
                            <span class="text-sm text-slate-600">Break</span>
                        </div>
                    @endif
                </div>

                {{-- Calendar --}}
                <div class="rounded-2xl border border-white/70 bg-white shadow-[0_10px_30px_rgba(15,23,42,0.06)] overflow-hidden">

                    {{-- Day headers --}}
                    <div class="flex border-b border-slate-100">
                        <div class="w-20 shrink-0"></div>
                        @foreach($showDays as $day)
                            @php $isDayToday = $isCurrentWeek && $day === $todayDayName; @endphp
                            <div class="flex-1 text-center py-2.5 border-l border-slate-100 {{ $isDayToday ? 'bg-blue-50/60 border-b-2 border-b-blue-500' : '' }}">
                                <div class="text-xs font-semibold uppercase tracking-wider {{ $isDayToday ? 'text-blue-500' : 'text-slate-400' }}">
                                    <span class="hidden sm:inline">{{ $day }}</span>
                                    <span class="sm:hidden">{{ substr($day, 0, 3) }}</span>
                                </div>
                                <div class="mt-1 mx-auto w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $isDayToday ? 'bg-blue-600 text-white' : 'text-slate-700' }}">
                                    {{ $weekDates[$day]->format('j') }}
                                </div>
                                <div class="text-xs mt-0.5 {{ $isDayToday ? 'text-blue-400 font-medium' : 'text-slate-400' }}">
                                    {{ $weekDates[$day]->format('M') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Grid body --}}
                    <div class="flex overflow-y-auto" style="max-height: 900px;">

                        {{-- Time labels --}}
                        <div class="w-20 shrink-0 relative" style="height: {{ $totalHeight }}px;">
                            @for($h = $dayStart; $h <= $dayEnd; $h++)
                                <div class="absolute right-3 text-xs text-slate-400 select-none"
                                     style="top:{{ ($h - $dayStart) * $hourHeight - 8 }}px; line-height:1;">
                                    @if($h < 12) {{ $h }}AM
                                    @elseif($h === 12) 12PM
                                    @else {{ $h - 12 }}PM
                                    @endif
                                </div>
                            @endfor
                        </div>

                        {{-- Day columns --}}
                        <div class="flex flex-1 relative" id="calendar-body">

                            {{-- Current time line --}}
                            <div id="time-line" class="absolute left-0 right-0 z-20 pointer-events-none hidden" style="top:0px;">
                                <div class="flex items-center">
                                    <div class="w-2.5 h-2.5 rounded-full bg-red-500 -ml-1.5 shrink-0"></div>
                                    <div class="flex-1 border-t-2 border-red-500"></div>
                                </div>
                            </div>

                            @foreach($showDays as $day)
                                <div class="flex-1 relative border-l border-slate-100 min-w-0" style="height:{{ $totalHeight }}px;">

                                    @for($h = $dayStart; $h <= $dayEnd; $h++)
                                        <div class="absolute left-0 right-0 border-t border-slate-100" style="top:{{ ($h - $dayStart) * $hourHeight }}px;"></div>
                                    @endfor
                                    @for($h = $dayStart; $h < $dayEnd; $h++)
                                        <div class="absolute left-0 right-0 border-t border-dashed border-slate-50" style="top:{{ ($h - $dayStart) * $hourHeight + $hourHeight / 2 }}px;"></div>
                                    @endfor

                                    @foreach($entries->where('day', $day)->sortBy('start_time') as $entry)
                                        @php
                                            [$sh, $sm] = array_pad(explode(':', substr($entry->start_time, 0, 5)), 2, '0');
                                            [$eh, $em] = array_pad(explode(':', substr($entry->end_time,   0, 5)), 2, '0');
                                            $top    = ((int)$sh - $dayStart) * $hourHeight + round((int)$sm * $hourHeight / 60);
                                            $height = max(32, ((int)$eh - (int)$sh) * $hourHeight + round(((int)$em - (int)$sm) * $hourHeight / 60));
                                            $isExam  = $entry->entry_type === 'exam';
                                            $isBreak = $entry->entry_type === 'break';
                                            $color   = $isExam ? '#dc2626' : ($isBreak ? '#b45309' : ($subjectColors[$entry->subject_id] ?? '#6366f1'));
                                            $examSubj = $entry->subject?->name;
                                            $label   = $isExam
                                                ? (($entry->exam_type ?? 'Exam') . ($examSubj ? ': ' . $examSubj : ''))
                                                : ($isBreak ? $entry->title : ($entry->subject?->name ?? '—'));
                                            $startFmt = \Carbon\Carbon::parse($entry->start_time)->format('g:i');
                                            $endFmt   = \Carbon\Carbon::parse($entry->end_time)->format('g:i A');
                                            $roomName = $entry->schoolClass?->room ?? '';
                                            $entryInfo = json_encode([
                                                'label'   => $label,
                                                'color'   => $color,
                                                'teacher' => trim(($entry->teacher?->first_name ?? '') . ' ' . ($entry->teacher?->last_name ?? '')),
                                                'room'    => $roomName,
                                                'start'   => $startFmt,
                                                'end'     => $endFmt,
                                                'day'     => $day,
                                                'type'    => $isExam ? 'Exam' : ($isBreak ? 'Break' : 'Class'),
                                                'isBreak' => (bool) $isBreak,
                                            ]);
                                        @endphp
                                        <div style="position:absolute; top:{{ $top }}px; left:6px; right:6px; height:{{ $height }}px; background:{{ $color }}; border-radius:10px; overflow:hidden; cursor:pointer;"
                                             class="text-white z-10 group shadow-md transition hover:brightness-110"
                                             data-info="{{ $entryInfo }}"
                                             @click="open($el.dataset.info)"
                                             title="Click for details">
                                            <div style="position:absolute; inset:0; padding:8px 12px 10px; display:flex; flex-direction:column; overflow:hidden;">
                                                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:6px;">
                                                    <p style="font-size:13px; font-weight:700; line-height:1.35; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; margin:0; flex:1; min-width:0;">{{ $label }}</p>
                                                    <span style="font-size:10px; font-weight:600; opacity:0.95; white-space:nowrap; flex-shrink:0; background:rgba(0,0,0,0.18); border-radius:6px; padding:2px 6px; line-height:1.6;">{{ $startFmt }}–{{ $endFmt }}</span>
                                                </div>
                                                @if(!$isBreak && $height > 60)
                                                    <p style="font-size:11px; opacity:0.92; margin:4px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $entry->teacher?->first_name ?? '' }} {{ $entry->teacher?->last_name ?? '' }}</p>
                                                @endif
                                                @if(!$isBreak && $height > 75 && $roomName)
                                                    <p style="font-size:11px; opacity:0.80; margin:2px 0 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $roomName }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ── Detail Modal ───────────────────────────────────────── --}}
                <div x-show="show" x-cloak
                     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
                     @click.self="close()">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="close()"></div>
                    <div class="relative w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden bg-white"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                         @click.stop>

                        <div class="px-6 py-5 text-white" :style="'background:' + (entry.color ?? '#6366f1')">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="opacity:.8" x-text="entry.type"></p>
                                    <h3 class="text-xl font-bold leading-snug break-words" x-text="entry.label"></h3>
                                    <p class="mt-1 text-sm font-medium" style="opacity:.85" x-text="entry.day"></p>
                                </div>
                                <button @click="close()"
                                        class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5 transition"
                                        style="background:rgba(255,255,255,.2)" onmouseover="this.style.background='rgba(255,255,255,.35)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="px-6 py-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Time</p>
                                    <p class="text-sm font-semibold text-slate-800" x-text="(entry.start ?? '') + ' – ' + (entry.end ?? '')"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3" x-show="entry.teacher && !entry.isBreak">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Teacher</p>
                                    <p class="text-sm font-semibold text-slate-800" x-text="entry.teacher"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3" x-show="entry.room && !entry.isBreak">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Room</p>
                                    <p class="text-sm font-semibold text-slate-800" x-text="entry.room"></p>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 pb-5 flex justify-end border-t border-slate-100 pt-4">
                            <button @click="close()"
                                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                                Close
                            </button>
                        </div>
                    </div>
                </div>

                </div>{{-- /Alpine wrapper --}}
            @endif
        </div>


        </div>
    </div>

    <script>
    (function () {
        var dayStart   = {{ $dayStart ?? 7 }};
        var dayEnd     = {{ $dayEnd   ?? 18 }};
        var hourHeight = {{ $hourHeight ?? 100 }};
        function update() {
            var line = document.getElementById('time-line');
            if (!line) return;
            var now = new Date(), h = now.getHours(), m = now.getMinutes();
            if (h < dayStart || h >= dayEnd) { line.classList.add('hidden'); return; }
            line.style.top = ((h - dayStart) * hourHeight + Math.round(m * hourHeight / 60)) + 'px';
            line.classList.remove('hidden');
        }
        update();
        setInterval(update, 30000);
    })();
    </script>

</x-student-layout>
