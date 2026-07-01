<x-app-layout>
    <x-slot name="header">Result Report</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        <!-- Hero card: title + filter + export -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

            <!-- Title + export bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shrink-0 shadow-sm shadow-blue-600/25">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800 text-sm leading-tight">Result Report</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Aggregate scores per student across all subjects</p>
                    </div>
                </div>
                @if(isset($students) && $students->isNotEmpty() && isset($exams) && $exams->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('reports.export.csv', request()->only('class_id','type','month')) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        </svg>
                        Excel / CSV
                    </a>
                    <a href="{{ route('reports.export.pdf', request()->only('class_id','type','month')) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Export PDF
                    </a>
                    <button onclick="window.print()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded-xl hover:bg-slate-200 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print
                    </button>
                </div>
                @endif
            </div>

            <!-- Filter row -->
            <form method="GET" class="px-5 py-4 bg-slate-50/50">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[180px]">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5 block">Class</label>
                        <select name="class_id" onchange="this.form.submit()"
                                class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">— Select a class —</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}" @selected(isset($class) && $class->id == $cls->id)>
                                    {{ $cls->name }} – {{ $cls->section }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[160px]">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5 block">Exam Type</label>
                        <select name="type" onchange="this.form.submit()"
                                class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($examTypes as $t)
                                <option value="{{ $t }}" @selected(isset($examType) && $examType === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(isset($availableMonths) && $availableMonths->isNotEmpty())
                    <div class="min-w-[130px]">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5 block">Month</label>
                        <select name="month" onchange="this.form.submit()"
                                class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($availableMonths as $m)
                            <option value="{{ $m }}" {{ isset($month) && $month === $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generate
                    </button>
                </div>
            </form>
        </div>

        @if(!isset($class))
        <!-- Prompt state -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-12 h-12 text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-slate-400 text-sm">Select a class and exam type to generate the report.</p>
        </div>

        @elseif(!isset($exams) || $exams->isEmpty())
        <!-- No exams -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-slate-500 font-medium text-sm">No <strong>{{ $examType }}</strong> exams found for <strong>{{ $class->name }} – {{ $class->section }}</strong>.</p>
            <p class="text-slate-400 text-xs mt-1">Create exams first, then enter marks before generating this report.</p>
        </div>

        @elseif($students->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-12 text-center">
            <p class="text-slate-400 text-sm">No students enrolled in {{ $class->name }} – {{ $class->section }}.</p>
        </div>

        @else
        @php
            // Grade helper
            $gradeInfo = function(float $pct): array {
                return match(true) {
                    $pct >= 95 => ['label' => 'A+', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
                    $pct >= 90 => ['label' => 'A',  'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
                    $pct >= 85 => ['label' => 'B+', 'bg' => 'bg-blue-100',    'text' => 'text-blue-700'],
                    $pct >= 80 => ['label' => 'B',  'bg' => 'bg-blue-100',    'text' => 'text-blue-700'],
                    $pct >= 70 => ['label' => 'C',  'bg' => 'bg-teal-100',    'text' => 'text-teal-700'],
                    $pct >= 60 => ['label' => 'D',  'bg' => 'bg-yellow-100',  'text' => 'text-yellow-700'],
                    $pct >= 50 => ['label' => 'E',  'bg' => 'bg-orange-100',  'text' => 'text-orange-700'],
                    default    => ['label' => 'F',  'bg' => 'bg-red-100',     'text' => 'text-red-700'],
                };
            };

            $grandTotal = $exams->sum('total_marks');
        @endphp

        <!-- Stat cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $statCards = [
                    ['label' => 'Class',       'value' => $class->name . ' – ' . $class->section, 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'bg-blue-50 text-blue-600'],
                    ['label' => 'Exam Type',   'value' => $examType, 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'bg-purple-50 text-purple-600'],
                    ['label' => 'Month',       'value' => ($month > 0 ? date('F', mktime(0,0,0,$month,1)) : 'All Months'), 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'bg-violet-50 text-violet-600'],
                    ['label' => 'Subjects',    'value' => $exams->count(), 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'bg-teal-50 text-teal-600'],
                    ['label' => 'Total Marks', 'value' => $grandTotal, 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2h-2', 'color' => 'bg-amber-50 text-amber-600'],
                    ['label' => 'Students',    'value' => $students->count(), 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', 'color' => 'bg-indigo-50 text-indigo-600'],
                ];
            @endphp
            @foreach($statCards as $card)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $card['color'] }} flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" style="width:1.125rem;height:1.125rem" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">{{ $card['label'] }}</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">{{ $card['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Marksheet table -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width: {{ 280 + $exams->count() * 110 }}px">
                    <thead>
                        <tr class="bg-slate-50/60 border-b border-slate-100">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-8 shrink-0">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider min-w-[160px]">Student</th>
                            @foreach($exams as $exam)
                            <th class="text-center px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <div class="leading-tight">{{ $exam->subject->name ?? '—' }}</div>
                                <div class="text-slate-400 font-normal text-[10px] mt-0.5">/ {{ number_format($exam->total_marks, 0) }}</div>
                            </th>
                            @endforeach
                            <th class="text-center px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-100/60">Total</th>
                            <th class="text-center px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-100/60">%</th>
                            <th class="text-center px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-100/60">Grade</th>
                            <th class="text-center px-3 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-100/60">Rank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            // Pre-compute totals for ranking
                            $studentTotals = $students->map(function($student) use ($exams, $allResults) {
                                $obtained = 0;
                                foreach ($exams as $exam) {
                                    $result = $allResults->get($student->id)?->get($exam->id);
                                    if ($result) $obtained += $result->marks_obtained;
                                }
                                return ['student_id' => $student->id, 'obtained' => $obtained];
                            })->sortByDesc('obtained')->values();

                            $rankMap = $studentTotals->mapWithKeys(function($row, $idx) {
                                return [$row['student_id'] => $idx + 1];
                            });
                        @endphp

                        @foreach($students->sortBy(fn($s) => $rankMap[$s->id] ?? 999) as $i => $student)
                        @php
                            $obtained = 0;
                            $possible = 0;
                            $allEntered = true;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors {{ $i % 2 === 0 ? '' : 'bg-slate-50/30' }}">
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $rankMap[$student->id] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800 text-sm leading-tight">{{ $student->first_name }} {{ $student->last_name }}</p>
                                    </div>
                                </div>
                            </td>

                            @foreach($exams as $exam)
                            @php
                                $result = $allResults->get($student->id)?->get($exam->id);
                                if ($result) {
                                    $obtained += $result->marks_obtained;
                                    $possible += $exam->total_marks;
                                } else {
                                    $allEntered = false;
                                }
                            @endphp
                            <td class="px-3 py-3 text-center">
                                @if($result)
                                @php
                                    $subPct = $exam->total_marks > 0 ? ($result->marks_obtained / $exam->total_marks) * 100 : 0;
                                    $subGrade = $gradeInfo($subPct);
                                @endphp
                                <div class="font-semibold text-slate-800">{{ number_format($result->marks_obtained, 0) }}</div>
                                <div class="text-[10px] {{ $subGrade['text'] }} font-semibold mt-0.5">{{ $subGrade['label'] }}</div>
                                @else
                                <span class="text-slate-300 text-lg leading-none">—</span>
                                @endif
                            </td>
                            @endforeach

                            @php
                                $totalPossible = $exams->sum('total_marks');
                                $pct = $totalPossible > 0 ? ($obtained / $totalPossible) * 100 : 0;
                                $gi  = $gradeInfo($pct);
                            @endphp
                            <td class="px-3 py-3 text-center font-bold text-slate-800 bg-slate-50">
                                {{ $obtained > 0 ? number_format($obtained, 0) : '—' }}
                                @if($obtained > 0)
                                <div class="text-[10px] text-slate-400 font-normal">/ {{ $totalPossible }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center font-semibold bg-slate-50">
                                @if($obtained > 0)
                                <span class="{{ $pct >= 50 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ number_format($pct, 1) }}%
                                </span>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center bg-slate-50">
                                @if($obtained > 0)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $gi['bg'] }} {{ $gi['text'] }}">
                                    {{ $gi['label'] }}
                                </span>
                                @if(!$allEntered)
                                <div class="text-[10px] text-amber-500 mt-0.5">partial</div>
                                @endif
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center font-bold bg-slate-50/60">
                                @if($obtained > 0)
                                <span class="text-slate-600">#{{ $rankMap[$student->id] ?? '—' }}</span>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Summary footer -->
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50/60">
                            <td colspan="2" class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Class Average</td>
                            @foreach($exams as $exam)
                            @php
                                $examIds = collect([$exam->id]);
                                $avgMarks = \App\Models\ExamResult::whereIn('exam_id', $examIds)->avg('marks_obtained');
                            @endphp
                            <td class="px-3 py-3 text-center">
                                @if($avgMarks !== null)
                                <div class="font-semibold text-slate-600">{{ number_format($avgMarks, 1) }}</div>
                                @php $avgPct = $exam->total_marks > 0 ? ($avgMarks / $exam->total_marks) * 100 : 0; @endphp
                                <div class="text-[10px] text-slate-400">{{ number_format($avgPct, 0) }}%</div>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            @endforeach
                            <td colspan="4" class="px-3 py-3 text-center text-xs text-slate-400">
                                @php
                                    $allObtained = $allResults->flatten()->sum('marks_obtained');
                                    $enteredCount = $allResults->flatten()->count();
                                @endphp
                                @if($enteredCount > 0)
                                <span class="font-semibold text-slate-600">{{ number_format($allObtained / $students->count(), 1) }}</span>
                                <span class="text-slate-400"> avg total</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
            <span class="font-semibold">Grade:</span>
            @foreach([['A+/A','bg-emerald-100 text-emerald-700','≥80%'],['B+/B','bg-blue-100 text-blue-700','≥60%'],['C','bg-yellow-100 text-yellow-700','≥50%'],['D','bg-orange-100 text-orange-700','≥40%'],['F','bg-red-100 text-red-700','<40%']] as [$g,$cls,$pct])
            <span class="inline-flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $cls }}">{{ $g }}</span>
                <span class="text-slate-400">{{ $pct }}</span>
            </span>
            @endforeach
            <span class="text-amber-500 ml-2">⚠ "partial" = not all subjects entered</span>
        </div>
        @endif

    </div>

    <style>
        @media print {
            aside, header, form, button, .no-print { display: none !important; }
            main { overflow: visible !important; }
            body { background: white !important; }
            .bg-white { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        }
    </style>

</x-app-layout>
