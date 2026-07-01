<x-app-layout>
    <x-slot name="header">Monthly Exam</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Monthly Exam Summary</h2>
            <p class="text-sm text-slate-500 mt-0.5">View exam results grouped by month for each class</p>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('monthly-exam.index') }}"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Class</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select Class —</option>
                    @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>
                        {{ $cls->name }}{{ $cls->section ? ' — '.$cls->section : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[110px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Year</label>
                <select name="year" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Exam Type</label>
                <select name="type" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach($examTypes as $t)
                    <option value="{{ $t }}" {{ $examType === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            @if($classId && $examType)
            <a href="{{ route('monthly-exam.index', ['class_id' => $classId, 'year' => $year]) }}"
               class="px-4 py-2 text-sm text-slate-500 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Clear type
            </a>
            @endif
        </form>

        @if(!$classId)
        {{-- Empty state --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-medium">Select a class to view monthly exam results</p>
        </div>

        @elseif($months->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-medium">No exams found for {{ $year }}{{ $examType ? ' · '.$examType : '' }}</p>
            <a href="{{ route('exams.create') }}" class="text-xs text-blue-600 hover:underline">+ Create an exam</a>
        </div>

        @else

        {{-- Month filter chips --}}
        @if($availableMonths->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('monthly-exam.index', array_filter(['class_id' => $classId, 'year' => $year, 'type' => $examType], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $selectedMonth === 0 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                All
            </a>
            @foreach($availableMonths as $m)
            <a href="{{ route('monthly-exam.index', array_filter(['class_id' => $classId, 'year' => $year, 'type' => $examType, 'month' => $m], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $selectedMonth === $m ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                {{ date('M', mktime(0,0,0,$m,1)) }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- Month sections --}}
        <div class="space-y-4" x-data="{}">
            @foreach($months as $monthData)
            @php
                $avgColor = $monthData['avgPct'] >= 80 ? 'emerald' : ($monthData['avgPct'] >= 60 ? 'blue' : ($monthData['avgPct'] >= 40 ? 'amber' : 'red'));
            @endphp

            <div id="month-{{ $monthData['month'] }}"
                 x-data="{ open: true }"
                 class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden scroll-mt-5">

                {{-- Month header --}}
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-{{ $avgColor }}-100 flex items-center justify-center shrink-0">
                            <span class="text-{{ $avgColor }}-700 font-black text-sm">{{ $monthData['short'] }}</span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">{{ $monthData['name'] }} {{ $year }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $monthData['exams']->count() }} exam{{ $monthData['exams']->count() !== 1 ? 's' : '' }}
                                · Total marks: <strong>{{ $monthData['maxTotal'] }}</strong>
                                · Class avg: <strong class="text-{{ $avgColor }}-600">{{ $monthData['avgPct'] }}%</strong>
                                · Pass: {{ $monthData['passCount'] }}/{{ $students->count() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Avg badge --}}
                        <span class="hidden sm:inline-flex px-3 py-1 rounded-full text-xs font-bold bg-{{ $avgColor }}-50 text-{{ $avgColor }}-700 border border-{{ $avgColor }}-200">
                            Avg {{ $monthData['avgPct'] }}%
                        </span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Exam subject tags --}}
                <div x-show="open" x-collapse>
                    <div class="px-5 pb-3 flex flex-wrap gap-2 border-b border-slate-100">
                        @foreach($monthData['exams'] as $exam)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ $exam->subject->name ?? '—' }}
                            <span class="text-slate-400">/ {{ $exam->total_marks }}</span>
                            <span class="text-slate-400">· {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M') }}</span>
                        </span>
                        @endforeach
                    </div>

                    {{-- Student results table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50/60 border-b border-slate-100">
                                    <th class="px-5 py-2.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide w-8">#</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Student</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wide">Score</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wide">%</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wide">Grade</th>
                                    <th class="px-5 py-2.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($monthData['studentRows'] as $i => $row)
                                @php
                                    $gradeColors = [
                                        'A+' => 'emerald', 'A' => 'emerald',
                                        'B+' => 'blue',    'B' => 'blue',
                                        'C'  => 'teal',    'D' => 'amber',
                                        'E'  => 'orange',  'F' => 'red',
                                    ];
                                    $gc = $gradeColors[$row['grade']] ?? 'slate';
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3 text-xs text-slate-400 font-semibold">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($row['student']->first_name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-slate-800">
                                                {{ $row['student']->first_name }} {{ $row['student']->last_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($row['obtained'] !== null)
                                        <span class="font-bold text-slate-800">{{ $row['obtained'] }}</span>
                                        <span class="text-xs text-slate-400"> / {{ $monthData['maxTotal'] }}</span>
                                        @else
                                        <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($row['pct'] !== null)
                                        <span class="font-semibold text-sm {{ $row['pct'] >= 50 ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ $row['pct'] }}%
                                        </span>
                                        @else
                                        <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $gc }}-100 text-{{ $gc }}-700">
                                            {{ $row['grade'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($row['pct'] !== null)
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-{{ $gc }}-400 rounded-full transition-all"
                                                     style="width: {{ min($row['pct'], 100) }}%"></div>
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        @endif
    </div>
</x-app-layout>
