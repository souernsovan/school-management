<x-app-layout>
    <x-slot name="header">Quick Score Entry</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Quick Score Entry</h2>
            <p class="text-sm text-slate-500 mt-0.5">Enter scores for all subjects at once — exams are created automatically</p>
        </div>

        @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('quick-score.index') }}"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5">
            <div class="flex flex-wrap items-end gap-3">

                {{-- Class --}}
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Class <span class="text-red-500 normal-case">*</span></label>
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

                {{-- Exam Type --}}
                <div class="min-w-[140px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Exam Type <span class="text-red-500 normal-case">*</span></label>
                    <select name="type" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Select Type —</option>
                        @foreach($examTypes as $t)
                        <option value="{{ $t }}" {{ $examType === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month --}}
                <div class="min-w-[150px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Month <span class="text-red-500 normal-case">*</span></label>
                    <select name="month" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Select Month —</option>
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(now()->year, $m, 1)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>

                {{-- Total Marks --}}
                <div class="min-w-[120px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Total Marks <span class="text-red-500 normal-case">*</span></label>
                    <input type="number" name="total_marks" value="{{ $totalMarks ?: 100 }}" min="1" max="1000"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Load button --}}
                <div class="shrink-0">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Load Sheet
                    </button>
                </div>
            </div>

            {{-- Info strip when filters are active --}}
            @if($ready)
            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ \Carbon\Carbon::createFromDate(now()->year, $month, 1)->format('F Y') }}
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ $examType }}
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    {{ $totalMarks }} marks per subject
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Exams auto-created on save
                </span>
            </div>
            @endif
        </form>

        @if(!$classId)
        {{-- No class selected --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-sm font-medium">Select a class to begin</p>
        </div>

        @elseif($subjects->isEmpty())
        {{-- No subjects --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-sm font-medium">No subjects found</p>
            <p class="text-xs text-slate-300">Add subjects in the Subjects section first</p>
        </div>

        @elseif(!$ready)
        {{-- Class selected but filters incomplete --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-14 flex flex-col items-center gap-3 text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <p class="text-sm font-medium">Select exam type, month and total marks, then click Load Sheet</p>
            <p class="text-xs text-slate-300">{{ $subjects->count() }} subject{{ $subjects->count() != 1 ? 's' : '' }} and {{ $students->count() }} student{{ $students->count() != 1 ? 's' : '' }} ready</p>
        </div>

        @elseif($students->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center text-slate-400 text-sm">
            No students enrolled in this class.
        </div>

        @else
        {{-- ── Score entry table ── --}}
        <form method="POST" action="{{ route('quick-score.store') }}">
            @csrf
            <input type="hidden" name="class_id"    value="{{ $classId }}">
            <input type="hidden" name="type"        value="{{ $examType }}">
            <input type="hidden" name="month"       value="{{ $month }}">
            <input type="hidden" name="exam_date"   value="{{ $examDate }}">
            <input type="hidden" name="total_marks" value="{{ $totalMarks }}">

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                {{-- Table info bar --}}
                <div class="px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800 text-sm">
                            {{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }} ·
                            {{ $subjects->count() }} subject{{ $subjects->count() !== 1 ? 's' : '' }} ·
                            <span class="text-purple-600">{{ $examType }}</span> ·
                            {{ \Carbon\Carbon::parse($examDate)->format('d M Y') }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Leave blank to skip a subject. Max <strong>{{ $totalMarks }}</strong> marks per subject.</p>
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
                                @foreach($subjects as $subject)
                                @php $subjectMax = $examMarks->get($subject->id, $totalMarks); @endphp
                                <th class="px-3 py-3 text-center min-w-[110px] border-r border-slate-100 last:border-0">
                                    <p class="font-semibold text-slate-700 text-xs truncate max-w-[100px] mx-auto" title="{{ $subject->name }}">
                                        {{ $subject->name }}
                                    </p>
                                    <div class="flex items-center justify-center gap-0.5 mt-1">
                                        <span class="text-[10px] text-slate-400">/</span>
                                        <input type="number"
                                               name="subject_marks[{{ $subject->id }}]"
                                               value="{{ $subjectMax }}"
                                               min="1" max="1000"
                                               data-marks-for="{{ $subject->id }}"
                                               oninput="updateSubjectMax({{ $subject->id }}, this.value)"
                                               class="w-14 text-center text-[11px] font-bold text-blue-600 bg-blue-50 border border-blue-100 rounded-md px-1 py-0.5
                                                      focus:outline-none focus:ring-1 focus:ring-blue-400 focus:border-blue-400 focus:bg-white transition
                                                      [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                    </div>
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
                                $studentResults = $results->get((string) $student->id, collect());
                                $totalObtained  = 0;
                                $enteredCount   = 0;
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

                                {{-- Score inputs per subject --}}
                                @foreach($subjects as $subject)
                                @php
                                    // Find existing result via exam lookup
                                    $existingVal = '';
                                    foreach ($studentResults as $examResult) {
                                        if ($examResult->exam?->subject_id == $subject->id) {
                                            $existingVal = $examResult->marks_obtained;
                                            break;
                                        }
                                    }
                                    if ($existingVal !== '') { $totalObtained += $existingVal; $enteredCount++; }
                                @endphp
                                <td class="px-2 py-2 text-center border-r border-slate-50 last:border-0">
                                    <input
                                        type="number"
                                        name="scores[{{ $subject->id }}][{{ $student->id }}]"
                                        value="{{ $existingVal }}"
                                        min="0"
                                        max="{{ $examMarks->get($subject->id, $totalMarks) }}"
                                        step="0.5"
                                        placeholder="—"
                                        data-score-for="{{ $subject->id }}"
                                        class="w-20 text-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-sm text-slate-800
                                               focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:bg-white
                                               placeholder:text-slate-300 transition
                                               [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                    >
                                </td>
                                @endforeach

                                {{-- Row total --}}
                                <td class="px-4 py-2 text-center">
                                    @if($enteredCount > 0)
                                    @php $totalPossible = $enteredCount * $totalMarks; $pct = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100) : 0; @endphp
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
                                @foreach($subjects as $subject)
                                @php
                                    // Collect all scores for this subject
                                    $subjectScores = collect();
                                    foreach ($results as $studentResult) {
                                        foreach ($studentResult as $examResult) {
                                            if ($examResult->exam?->subject_id == $subject->id) {
                                                $subjectScores->push($examResult->marks_obtained);
                                            }
                                        }
                                    }
                                    $avg = $subjectScores->count() ? round($subjectScores->avg(), 1) : null;
                                @endphp
                                <td class="px-2 py-2.5 text-center border-r border-slate-100 last:border-0">
                                    @if($avg !== null)
                                    <p class="font-semibold text-slate-600 text-sm">{{ $avg }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $totalMarks > 0 ? round($avg / $totalMarks * 100) : 0 }}%</p>
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

    <script>
        function updateSubjectMax(subjectId, val) {
            const max = parseFloat(val) || 1000;
            document.querySelectorAll('[data-score-for="' + subjectId + '"]').forEach(function(input) {
                input.max = max;
                // Clamp existing value if it exceeds the new max
                if (input.value !== '' && parseFloat(input.value) > max) {
                    input.value = max;
                }
            });
        }
    </script>
</x-app-layout>
