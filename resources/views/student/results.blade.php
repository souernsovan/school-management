<x-student-layout>

    <div class="p-4 sm:p-6 space-y-5">

        @if(!$student)
        {{-- No student record linked to this account --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <div class="mx-auto w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="text-slate-700 font-semibold text-sm">No student record found</p>
            <p class="text-slate-400 text-xs mt-1">Your account email does not match any student. Please contact your administrator.</p>
        </div>

        @else

        {{-- Student profile banner --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex flex-wrap items-center gap-4">
                <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-2xl font-bold text-white shrink-0">
                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-slate-800 leading-tight">{{ $student->first_name }} {{ $student->last_name }}</h2>
                    <p class="text-slate-400 text-sm mt-0.5">
                        {{ $student->schoolClass?->name }} – {{ $student->schoolClass?->section }}
                        @if($student->email) &nbsp;·&nbsp; {{ $student->email }} @endif
                    </p>
                </div>
            </div>
        </div>

        @if($summary['total'] > 0)
        {{-- Overall summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $summaryCards = [
                    ['label' => 'Exams Taken',    'value' => $summary['total'],                           'sub' => 'total results',       'color' => 'bg-blue-50 text-blue-600'],
                    ['label' => 'Total Obtained',  'value' => number_format($summary['obtained'], 0),      'sub' => '/ ' . number_format($summary['possible'], 0) . ' marks', 'color' => 'bg-indigo-50 text-indigo-600'],
                    ['label' => 'Overall %',       'value' => number_format($summary['pct'], 1) . '%',     'sub' => $summary['pct'] >= 50 ? 'Passing' : 'Below pass', 'color' => $summary['pct'] >= 50 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'],
                    ['label' => 'Overall Grade',   'value' => $summary['gradeLabel'],                     'sub' => 'all subjects combined', 'color' => match(true) {
                        $summary['pct'] >= 80 => 'bg-emerald-50 text-emerald-600',
                        $summary['pct'] >= 60 => 'bg-blue-50 text-blue-600',
                        $summary['pct'] >= 40 => 'bg-amber-50 text-amber-600',
                        default               => 'bg-red-50 text-red-600',
                    }],
                ];
            @endphp
            @foreach($summaryCards as $card)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold mt-1 {{ explode(' ', $card['color'])[1] }}">{{ $card['value'] }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $card['sub'] }}</p>
            </div>
            @endforeach
        </div>
        @endif

        @if($grouped->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-10 h-10 text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-slate-400 text-sm font-medium">No results yet</p>
            <p class="text-slate-300 text-xs mt-1">Your exam marks haven't been entered yet.</p>
        </div>

        @else

        @php
            $typeColors = \App\Models\ExamType::tailwindMap();

            $gradeInfo = fn(float $pct): array => match(true) {
                $pct >= 95 => ['label' => 'A+', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
                $pct >= 90 => ['label' => 'A',  'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
                $pct >= 85 => ['label' => 'B+', 'bg' => 'bg-blue-100',    'text' => 'text-blue-700'],
                $pct >= 80 => ['label' => 'B',  'bg' => 'bg-blue-100',    'text' => 'text-blue-700'],
                $pct >= 70 => ['label' => 'C',  'bg' => 'bg-teal-100',    'text' => 'text-teal-700'],
                $pct >= 60 => ['label' => 'D',  'bg' => 'bg-yellow-100',  'text' => 'text-yellow-700'],
                $pct >= 50 => ['label' => 'E',  'bg' => 'bg-orange-100',  'text' => 'text-orange-700'],
                default    => ['label' => 'F',  'bg' => 'bg-red-100',     'text' => 'text-red-700'],
            };
        @endphp

        {{-- Results grouped by exam type --}}
        @foreach($grouped as $examType => $results)
        @php
            $typeTotal    = $results->sum('marks_obtained');
            $typePossible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
            $typePct      = $typePossible > 0 ? ($typeTotal / $typePossible) * 100 : 0;
            $tc = $typeColors[$examType] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700'];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

            {{-- Group header --}}
            <div class="flex items-center justify-between gap-4 px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-2.5">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $tc['bg'] }} {{ $tc['text'] }}">
                        {{ $examType }}
                    </span>
                    <span class="text-xs text-slate-400">{{ $results->count() }} subject{{ $results->count() != 1 ? 's' : '' }}</span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold text-slate-700">{{ number_format($typeTotal, 0) }} / {{ number_format($typePossible, 0) }}</span>
                    <span class="text-xs {{ $typePct >= 50 ? 'text-emerald-600' : 'text-red-500' }} ml-1.5">({{ number_format($typePct, 1) }}%)</span>
                </div>
            </div>

            {{-- Results table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Subject</th>
                            <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Marks</th>
                            <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Out of</th>
                            <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">%</th>
                            <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($results->sortByDesc(fn($r) => $r->exam?->exam_date) as $result)
                        @php
                            $total = $result->exam?->total_marks ?? 0;
                            $pct   = $total > 0 ? ($result->marks_obtained / $total) * 100 : 0;
                            $gi    = $gradeInfo($pct);
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-slate-800">{{ $result->exam?->subject?->name ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-center text-slate-500 text-xs">
                                {{ $result->exam?->exam_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-base font-bold text-slate-800">{{ number_format($result->marks_obtained, 0) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center text-slate-400 text-sm">{{ $total }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-semibold text-sm {{ $pct >= 50 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ number_format($pct, 1) }}%
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $gi['bg'] }} {{ $gi['text'] }}">
                                    {{ $gi['label'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        @endif
        @endif

    </div>
</x-student-layout>
