<x-student-layout>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">My Timetable</h2>
            <p class="text-sm text-slate-500 mt-0.5">View your weekly schedule and upcoming exams.</p>
        </div>

        {{-- Controls bar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Class</label>
                <form method="GET" action="{{ route('student.timetable') }}" id="class-form">
                    <select name="class_id" onchange="document.getElementById('class-form').submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— Select a class —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}@if($class->section) — {{ $class->section }}@endif
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

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
                    $today = now()->format('l');

                    $hourHeight = 64;
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
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                    {{-- Day headers --}}
                    <div class="flex border-b border-slate-100">
                        <div class="w-16 shrink-0"></div>
                        @foreach($showDays as $day)
                            <div class="flex-1 text-center py-3 border-l border-slate-100 text-sm tracking-wide
                                        {{ $day === $today ? 'text-emerald-600 font-bold border-b-2 border-b-emerald-500' : 'text-slate-500 font-semibold' }}">
                                <span class="hidden sm:inline">{{ $day }}</span>
                                <span class="sm:hidden">{{ substr($day, 0, 3) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Grid body --}}
                    <div class="flex overflow-y-auto" style="max-height: 680px;">

                        {{-- Time labels --}}
                        <div class="w-16 shrink-0 relative" style="height: {{ $totalHeight }}px;">
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
                                        @endphp
                                        <div style="position:absolute; top:{{ $top }}px; left:4px; right:4px; height:{{ $height }}px; background:{{ $color }}; border-radius:10px;"
                                             class="text-white overflow-hidden z-10 shadow-sm group">
                                            <div class="px-2 py-1 h-full flex flex-col justify-between overflow-hidden">
                                                <div>
                                                    <p class="font-bold leading-tight truncate" style="font-size:11px;">{{ $label }}</p>
                                                    @if(!$isBreak && $height > 40)
                                                        <p class="opacity-80 truncate" style="font-size:10px;">{{ $entry->teacher?->first_name ?? '' }}</p>
                                                    @endif
                                                </div>
                                                {{-- Always show time --}}
                                                <p class="opacity-75 font-medium truncate mt-0.5" style="font-size:10px;">
                                                    {{ $startFmt }}–{{ $endFmt }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>


    </div>

    <script>
    (function () {
        var dayStart   = {{ $dayStart ?? 7 }};
        var dayEnd     = {{ $dayEnd   ?? 18 }};
        var hourHeight = {{ $hourHeight ?? 64 }};
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
