<x-app-layout>

    <x-slot name="header">Timetable Management</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Timetable</h2>
                <p class="text-sm text-slate-500 mt-0.5">Select a class to view its weekly schedule.</p>
            </div>
            @can('manage timetables')
            <a href="{{ route('timetables.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Entry
            </a>
            @endcan
        </div>

        {{-- Success alert --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Class selector --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <form method="GET" action="{{ route('timetables.index') }}">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-slate-600 whitespace-nowrap">Class</label>
                    <select name="class_id" onchange="this.form.submit()"
                            class="rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 py-2 pl-3 pr-10 min-w-[180px]">
                        <option value="">— Select a class —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} — {{ $class->section }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if(!$classId)
            {{-- No classes exist yet --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 flex flex-col items-center gap-3 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 7V3m8 4V3m-9 4h10M5 11h14M5 19h14M5 15h14"/>
                </svg>
                <p class="font-medium">No classes found</p>
                <p class="text-xs">Add a class first before creating a timetable</p>
            </div>

        @elseif($entries->isEmpty())
            {{-- Class selected but no entries --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-16 flex flex-col items-center gap-3 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 7V3m8 4V3m-9 4h10M5 11h14M5 19h14M5 15h14"/>
                </svg>
                <p class="font-medium">No timetable entries for this class</p>
                <a href="{{ route('timetables.create') }}" class="text-sm text-blue-600 hover:underline">Add the first entry</a>
            </div>

        @else
            @php
                // --- Palette ---
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

                // --- Active days (only days that have entries, in order) ---
                $orderedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                $activeDays  = array_values(array_filter($orderedDays, fn($d) => $entries->where('day', $d)->isNotEmpty()));
                // Show all weekdays even if empty, Saturday only if used
                $showDays = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
                if ($entries->where('day','Saturday')->isNotEmpty()) $showDays[] = 'Saturday';
                $today = now()->format('l'); // e.g. "Monday"

                // --- Time bounds ---
                $hourHeight = 64; // px per hour
                $dayStart   = 7;  // 7 AM
                $dayEnd     = 18; // 6 PM
                foreach ($entries as $e) {
                    $sh = (int) substr($e->start_time, 0, 2);
                    $eh = (int) substr($e->end_time,   0, 2);
                    if ($sh < $dayStart) $dayStart = $sh;
                    if ($eh > $dayEnd)   $dayEnd   = $eh + 1;
                }
                $totalHeight = ($dayEnd - $dayStart) * $hourHeight;
            @endphp

            {{-- Subject legend --}}
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

            {{-- Calendar grid --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
              <div class="overflow-x-auto min-w-0">
                {{-- Day header row --}}
                <div class="flex border-b border-slate-100" style="min-width: 480px;">
                    <div class="w-16 shrink-0"></div>
                    @foreach($showDays as $day)
                        <div class="flex-1 text-center py-3 border-l border-slate-100
                                    {{ $day === $today ? 'text-emerald-600 font-bold border-b-2 border-b-emerald-500' : 'text-slate-500 font-semibold' }}
                                    text-sm tracking-wide">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                {{-- Body: time labels + day columns --}}
                <div class="flex overflow-y-auto" style="max-height: 680px; min-width: 480px;">

                    {{-- Time labels --}}
                    <div class="w-16 shrink-0 relative" style="height: {{ $totalHeight }}px;">
                        @for($h = $dayStart; $h <= $dayEnd; $h++)
                            <div class="absolute right-3 text-xs text-slate-400 select-none"
                                 style="top: {{ ($h - $dayStart) * $hourHeight - 8 }}px; line-height:1;">
                                @if($h < 12) {{ $h }} AM
                                @elseif($h === 12) 12 PM
                                @else {{ $h - 12 }} PM
                                @endif
                            </div>
                        @endfor
                    </div>

                    {{-- Day columns with current-time wrapper --}}
                    <div class="flex flex-1 relative" id="calendar-body">

                        {{-- Current time line (spans all day columns) --}}
                        <div id="time-line"
                             class="absolute left-0 right-0 z-20 pointer-events-none hidden"
                             style="top: 0px;">
                            <div class="flex items-center">
                                <div class="w-2.5 h-2.5 rounded-full bg-red-500 -ml-1.5 shrink-0"></div>
                                <div class="flex-1 border-t-2 border-red-500"></div>
                            </div>
                        </div>

                        @foreach($showDays as $day)
                            <div class="flex-1 relative border-l border-slate-100 min-w-0"
                                 style="height: {{ $totalHeight }}px;">

                                {{-- Hour grid lines --}}
                                @for($h = $dayStart; $h <= $dayEnd; $h++)
                                    <div class="absolute left-0 right-0 border-t border-slate-100"
                                         style="top: {{ ($h - $dayStart) * $hourHeight }}px;"></div>
                                @endfor

                                {{-- Half-hour lines --}}
                                @for($h = $dayStart; $h < $dayEnd; $h++)
                                    <div class="absolute left-0 right-0 border-t border-dashed border-slate-50"
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
                                        $color    = $isExam ? '#dc2626' : ($isBreak ? '#b45309' : ($subjectColors[$entry->subject_id] ?? '#6366f1'));
                                        $examSubj = $entry->subject?->name;
                                        $label    = $isExam
                                            ? (($entry->exam_type ?? 'Exam') . ($examSubj ? ': ' . $examSubj : ''))
                                            : ($isBreak ? $entry->title : ($entry->subject?->name ?? '—'));
                                        $startFmt = \Carbon\Carbon::parse($entry->start_time)->format('g:i');
                                        $endFmt   = \Carbon\Carbon::parse($entry->end_time)->format('g:i A');
                                    @endphp
                                    <div style="position:absolute; top:{{ $top }}px; left:4px; right:4px; height:{{ $height }}px; background:{{ $color }}; border-radius:10px;"
                                         class="text-white overflow-hidden group z-10 shadow-sm transition hover:brightness-110">
                                        <div class="px-2 py-1 h-full flex flex-col justify-between overflow-hidden">
                                            <div>
                                                <p class="font-bold leading-tight truncate" style="font-size:11px;">{{ $label }}</p>
                                                @if(!$isBreak && $height > 40)
                                                    <p class="opacity-80 truncate" style="font-size:10px;">{{ $entry->teacher?->first_name ?? '' }}</p>
                                                @endif
                                            </div>
                                            <p class="opacity-75 font-medium truncate mt-0.5" style="font-size:10px;">{{ $startFmt }}–{{ $endFmt }}</p>
                                        </div>
                                        {{-- Hover actions --}}
                                        @can('manage timetables')
                                        <div class="absolute top-1 right-1 hidden group-hover:flex gap-0.5">
                                            <a href="{{ route('timetables.edit', $entry) }}"
                                               class="w-6 h-6 rounded-md bg-white/20 hover:bg-white/40 flex items-center justify-center transition"
                                               title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('timetables.destroy', $entry) }}" method="POST"
                                                  data-swal-confirm
                                                  data-swal-title="Delete timetable entry?"
                                                  data-swal-text="Delete this entry? This cannot be undone."
                                                  data-swal-confirm-text="Yes, delete it">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="w-6 h-6 rounded-md bg-white/20 hover:bg-red-500/70 flex items-center justify-center transition"
                                                        title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/>
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
        @endif

    </div>

    <script>
    (function () {
        var dayStart = {{ $dayStart ?? 7 }};
        var dayEnd   = {{ $dayEnd ?? 18 }};
        var hourHeight = {{ $hourHeight ?? 64 }};

        function updateTimeLine() {
            var line = document.getElementById('time-line');
            if (!line) return;
            var now   = new Date();
            var h     = now.getHours();
            var m     = now.getMinutes();
            if (h < dayStart || h >= dayEnd) { line.classList.add('hidden'); return; }
            var top = (h - dayStart) * hourHeight + Math.round(m * hourHeight / 60);
            line.style.top = top + 'px';
            line.classList.remove('hidden');
        }
        updateTimeLine();
        setInterval(updateTimeLine, 30000);
    })();
    </script>

</x-app-layout>
