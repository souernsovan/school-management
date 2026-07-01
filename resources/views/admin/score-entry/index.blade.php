<x-app-layout>
    <x-slot name="header">Score Entry</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Score Entry Sheet</h2>
            <p class="text-sm text-slate-500 mt-0.5">Enter student scores for each month</p>
        </div>

        @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('score-entry.index') }}"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
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
            @if($classId)
            <div class="min-w-[140px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Month</label>
                <select name="month" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @forelse($availableMonths as $m)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                    @empty
                    <option value="">No exams</option>
                    @endforelse
                </select>
            </div>
            @endif
        </form>

        @if(!$classId)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium">Select a class to start entering scores</p>
        </div>

        @elseif($availableMonths->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium">No exams found for this class{{ $examType ? ' / '.$examType : '' }}</p>
            <a href="{{ route('exams.create') }}" class="text-xs text-blue-600 hover:underline">+ Create an exam</a>
        </div>

        @elseif($exams->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-medium">No exams in {{ date('F', mktime(0,0,0,(int)$month,1)) }}{{ $examType ? ' / '.$examType : '' }}</p>
            <a href="{{ route('exams.create') }}" class="text-xs text-blue-600 hover:underline">+ Create an exam</a>
        </div>

        @elseif($students->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center text-slate-400 text-sm">
            No students enrolled in this class.
        </div>

        @else
        {{-- Month navigation chips --}}
        <div class="flex flex-wrap gap-2">
            @foreach($availableMonths as $m)
            <a href="{{ route('score-entry.index', array_filter(['class_id' => $classId, 'type' => $examType, 'month' => $m])) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition
                      {{ $month === $m
                         ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                         : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                {{ date('M', mktime(0,0,0,$m,1)) }}
            </a>
            @endforeach
        </div>

        {{-- Score entry table --}}
        <form method="POST" action="{{ route('score-entry.store') }}">
            @csrf

            {{-- Preserve filters --}}
            <input type="hidden" name="class_id" value="{{ $classId }}">
            <input type="hidden" name="type"     value="{{ $examType }}">
            <input type="hidden" name="month"    value="{{ $month }}">

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                {{-- Table info bar --}}
                <div class="px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">
                            {{ date('F', mktime(0,0,0,(int)$month,1)) }} ·
                            {{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }} ·
                            {{ $exams->count() }} exam{{ $exams->count() !== 1 ? 's' : '' }}
                            @if($examType) · <span class="text-blue-600">{{ $examType }}</span>@endif
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Leave blank to clear a score. Max marks shown in column header.</p>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-xl hover:bg-blue-700 transition text-sm font-semibold shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save All Scores
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide border-r border-slate-200 min-w-[180px]">
                                    Student
                                </th>
                                @foreach($exams as $exam)
                                <th class="px-3 py-2 text-center min-w-[110px] border-r border-slate-100 last:border-0">
                                    <p class="font-semibold text-slate-700 text-xs truncate max-w-[100px] mx-auto" title="{{ $exam->subject->name ?? 'Unknown' }}">
                                        {{ $exam->subject->name ?? '—' }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M') : '' }}
                                    </p>
                                    <p class="text-[10px] font-bold text-blue-500 mt-0.5">/ {{ $exam->total_marks }}</p>
                                </th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide min-w-[90px]">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($students as $student)
                            @php
                                $studentResults = $results->get($student->id, collect());
                                $totalObtained  = 0;
                                $hasAnyScore    = false;
                                $totalPossible  = 0;
                            @endphp
                            <tr class="hover:bg-blue-50/30 transition-colors group">

                                {{-- Student name --}}
                                <td class="sticky left-0 z-10 bg-white group-hover:bg-blue-50/30 px-4 py-2.5 border-r border-slate-100 transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-slate-800 whitespace-nowrap">
                                            {{ $student->first_name }} {{ $student->last_name }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Score inputs per exam --}}
                                @foreach($exams as $exam)
                                @php
                                    $existing = $studentResults->get($exam->id);
                                    $val      = $existing ? $existing->marks_obtained : '';
                                    if ($val !== '') { $totalObtained += $val; $hasAnyScore = true; }
                                    $totalPossible += $exam->total_marks;
                                @endphp
                                <td class="px-2 py-2 text-center border-r border-slate-50 last:border-0">
                                    <input
                                        type="number"
                                        name="scores[{{ $student->id }}][{{ $exam->id }}]"
                                        value="{{ $val }}"
                                        min="0"
                                        max="{{ $exam->total_marks }}"
                                        step="0.5"
                                        placeholder="—"
                                        class="w-20 text-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-sm text-slate-800
                                               focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:bg-white
                                               placeholder:text-slate-300 transition
                                               [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                    >
                                </td>
                                @endforeach

                                {{-- Row total --}}
                                <td class="px-4 py-2 text-center">
                                    @if($hasAnyScore)
                                    @php $pct = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100) : 0; @endphp
                                    <p class="font-bold text-slate-700 text-sm">{{ $totalObtained }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $pct }}%</p>
                                    @else
                                    <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                        {{-- Column averages footer --}}
                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50">
                                <td class="sticky left-0 z-10 bg-slate-50 px-4 py-2.5 border-r border-slate-200">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Class Avg</span>
                                </td>
                                @foreach($exams as $exam)
                                @php
                                    $examResults = $results->map(fn($sr) => $sr->get($exam->id))->filter();
                                    $avg = $examResults->count() ? round($examResults->avg('marks_obtained'), 1) : null;
                                @endphp
                                <td class="px-2 py-2.5 text-center border-r border-slate-100 last:border-0">
                                    @if($avg !== null)
                                    <p class="font-semibold text-slate-600 text-sm">{{ $avg }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $exam->total_marks > 0 ? round($avg / $exam->total_marks * 100) : 0 }}%</p>
                                    @else
                                    <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                @endforeach
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Save button bottom --}}
                <div class="px-5 py-4 border-t border-slate-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 transition text-sm font-semibold shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save All Scores
                    </button>
                </div>

            </div>
        </form>
        @endif

    </div>
</x-app-layout>
