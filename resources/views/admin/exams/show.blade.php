<x-app-layout>
    <x-slot name="header">Exam — {{ $exam->subject->name ?? '' }} · {{ $exam->type }}</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        <!-- Back + actions -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('exams.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                All Exams
            </a>
            @can('manage exams')
            <a href="{{ route('exams.edit', $exam) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Exam
            </a>
            @endcan
        </div>

        @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        <!-- Exam details + stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <!-- Exam Info -->
            <div class="md:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $exam->type }}</h2>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $exam->subject->name ?? '—' }} &middot; {{ $exam->schoolClass->name ?? '—' }}</p>
                    </div>
                </div>
                <dl class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Date</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-700">{{ $exam->exam_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Marks</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-700">{{ number_format($exam->total_marks, 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Class</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-700">{{ $exam->schoolClass->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-700">{{ $students->count() }}</dd>
                    </div>
                </dl>
                @if($exam->description)
                <p class="mt-4 text-sm text-slate-500 border-t border-slate-100 pt-4">{{ $exam->description }}</p>
                @endif
            </div>

            <!-- Stats -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-slate-700">Result Summary</h3>
                <div class="space-y-2.5">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Marks Entered</span>
                        <span class="font-semibold text-slate-800">{{ $stats['entered'] }} / {{ $stats['total'] }}</span>
                    </div>
                    @if($stats['entered'] > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Average</span>
                        <span class="font-semibold text-slate-800">{{ number_format($stats['average'], 1) }} / {{ number_format($exam->total_marks, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Highest</span>
                        <span class="font-semibold text-emerald-600">{{ number_format($stats['highest'], 1) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Lowest</span>
                        <span class="font-semibold text-red-500">{{ number_format($stats['lowest'], 1) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Passed (≥40%)</span>
                        <span class="font-semibold text-slate-800">{{ $stats['passed'] }} / {{ $stats['entered'] }}</span>
                    </div>
                    @else
                    <p class="text-xs text-slate-400">No marks entered yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mark entry form -->
        @if($students->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
            <p class="text-slate-400 text-sm">No students found in this class.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Student Marks</h3>
                <span class="text-xs text-slate-400">Out of {{ number_format($exam->total_marks, 0) }}</span>
            </div>

            @can('manage exams')
            <form method="POST" action="{{ route('exams.results.save', $exam) }}">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-8">#</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-40">Marks Obtained</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="resultsBody">
                            @foreach($students as $i => $student)
                            @php $result = $resultsMap->get($student->id); @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                                <td class="px-5 py-3">
                                    <input type="hidden" name="results[{{ $i }}][student_id]" value="{{ $student->id }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</p>
                                            @if($student->email)
                                            <p class="text-xs text-slate-400">{{ $student->email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="number"
                                           name="results[{{ $i }}][marks_obtained]"
                                           value="{{ $result?->marks_obtained }}"
                                           min="0" max="{{ $exam->total_marks }}" step="0.5"
                                           placeholder="—"
                                           class="marks-input w-28 text-center rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500"
                                           data-total="{{ $exam->total_marks }}"
                                           oninput="updateGrade(this)">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($result)
                                    @php
                                        $pct = $exam->total_marks > 0 ? ($result->marks_obtained / $exam->total_marks) * 100 : 0;
                                        $gradeLabel = match(true) {
                                            $pct >= 95 => 'A+',
                                            $pct >= 90 => 'A',
                                            $pct >= 85 => 'B+',
                                            $pct >= 80 => 'B',
                                            $pct >= 70 => 'C',
                                            $pct >= 60 => 'D',
                                            $pct >= 50 => 'E',
                                            default    => 'F',
                                        };
                                        $gradeColor = match(true) {
                                            $pct >= 90 => 'bg-emerald-100 text-emerald-700',
                                            $pct >= 80 => 'bg-blue-100 text-blue-700',
                                            $pct >= 70 => 'bg-teal-100 text-teal-700',
                                            $pct >= 60 => 'bg-yellow-100 text-yellow-700',
                                            $pct >= 50 => 'bg-orange-100 text-orange-700',
                                            default    => 'bg-red-100 text-red-700',
                                        };
                                    @endphp
                                    <span class="grade-badge inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $gradeColor }}">{{ $gradeLabel }}</span>
                                    @else
                                    <span class="grade-badge inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-100">
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm">
                        Save All Marks
                    </button>
                </div>
            </form>
            @else
            {{-- Read-only view --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/60 border-b border-slate-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Marks</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $i => $student)
                        @php $result = $resultsMap->get($student->id); @endphp
                        <tr>
                            <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td class="px-5 py-3.5 text-center text-slate-700">
                                {{ $result ? number_format($result->marks_obtained, 1) : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($result)
                                @php
                                    $pct = $exam->total_marks > 0 ? ($result->marks_obtained / $exam->total_marks) * 100 : 0;
                                    $gradeLabel = match(true) {
                                        $pct >= 95 => 'A+',
                                        $pct >= 90 => 'A',
                                        $pct >= 85 => 'B+',
                                        $pct >= 80 => 'B',
                                        $pct >= 70 => 'C',
                                        $pct >= 60 => 'D',
                                        $pct >= 50 => 'E',
                                        default    => 'F',
                                    };
                                    $gradeColor = match(true) {
                                        $pct >= 90 => 'bg-emerald-100 text-emerald-700',
                                        $pct >= 80 => 'bg-blue-100 text-blue-700',
                                        $pct >= 70 => 'bg-teal-100 text-teal-700',
                                        $pct >= 60 => 'bg-yellow-100 text-yellow-700',
                                        $pct >= 50 => 'bg-orange-100 text-orange-700',
                                        default    => 'bg-red-100 text-red-700',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $gradeColor }}">{{ $gradeLabel }}</span>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endcan
        </div>
        @endif

    </div>

<script>
function gradeFromPct(pct) {
    if (pct >= 95) return { label: 'A+', cls: 'bg-emerald-100 text-emerald-700' };
    if (pct >= 90) return { label: 'A',  cls: 'bg-emerald-100 text-emerald-700' };
    if (pct >= 85) return { label: 'B+', cls: 'bg-blue-100 text-blue-700' };
    if (pct >= 80) return { label: 'B',  cls: 'bg-blue-100 text-blue-700' };
    if (pct >= 70) return { label: 'C',  cls: 'bg-teal-100 text-teal-700' };
    if (pct >= 60) return { label: 'D',  cls: 'bg-yellow-100 text-yellow-700' };
    if (pct >= 50) return { label: 'E',  cls: 'bg-orange-100 text-orange-700' };
    return { label: 'F', cls: 'bg-red-100 text-red-700' };
}

function updateGrade(input) {
    const badge  = input.closest('tr').querySelector('.grade-badge');
    const val    = input.value.trim();
    const total  = parseFloat(input.dataset.total) || 100;
    const base   = 'grade-badge inline-block px-2.5 py-0.5 rounded-full text-xs font-bold';

    if (val === '' || isNaN(parseFloat(val))) {
        badge.textContent = '—';
        badge.className   = base + ' bg-slate-100 text-slate-400';
        input.classList.remove('border-red-400', 'bg-red-50', 'ring-red-200');
        return;
    }

    const marks = parseFloat(val);
    if (marks > total) {
        badge.textContent = '!';
        badge.className   = base + ' bg-red-100 text-red-600';
        input.classList.add('border-red-400', 'bg-red-50');
        input.classList.remove('border-slate-200');
        return;
    }

    input.classList.remove('border-red-400', 'bg-red-50');
    input.classList.add('border-slate-200');
    const pct  = (marks / total) * 100;
    const info = gradeFromPct(pct);
    badge.textContent = info.label;
    badge.className   = base + ' ' + info.cls;
}
</script>
</x-app-layout>
