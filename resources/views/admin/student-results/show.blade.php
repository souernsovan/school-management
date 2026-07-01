<x-app-layout>
    <x-slot name="header">Student Results</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Back link + active filter badges --}}
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('student-results.index', array_filter(request()->only(['class_id','type','month','search']), fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to all students
            </a>
            @if($examType)
            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">{{ $examType }}</span>
            @endif
            @if($month > 0)
            <span class="px-2.5 py-0.5 bg-violet-100 text-violet-700 rounded-full text-xs font-semibold">{{ date('F', mktime(0,0,0,$month,1)) }}</span>
            @endif
        </div>

        {{-- Student profile banner --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-5 text-white shadow-sm">
            <div class="flex flex-wrap items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-2xl font-bold shrink-0">
                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold leading-tight">{{ $student->first_name }} {{ $student->last_name }}</h2>
                    <p class="text-blue-200 text-sm mt-0.5">
                        {{ $student->schoolClass?->name }}{{ $student->schoolClass?->section ? ' – ' . $student->schoolClass->section : '' }}
                        @if($student->email) &nbsp;·&nbsp; {{ $student->email }} @endif
                    </p>
                </div>
            </div>
        </div>

        @if($summary['total'] > 0)
        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $cards = [
                    ['label' => 'Exams Taken',   'value' => $summary['total'],                          'sub' => 'total results',                       'color' => 'text-blue-600'],
                    ['label' => 'Total Obtained', 'value' => number_format($summary['obtained'], 0),     'sub' => '/ ' . number_format($summary['possible'], 0) . ' marks', 'color' => 'text-indigo-600'],
                    ['label' => 'Overall %',      'value' => number_format($summary['pct'], 1) . '%',    'sub' => $summary['pct'] >= 50 ? 'Passing' : 'Below pass', 'color' => $summary['pct'] >= 50 ? 'text-emerald-600' : 'text-red-500'],
                    ['label' => 'Overall Grade',  'value' => $summary['gradeLabel'],                     'sub' => 'all subjects combined', 'color' => match(true) {
                        $summary['pct'] >= 80 => 'text-emerald-600',
                        $summary['pct'] >= 60 => 'text-blue-600',
                        $summary['pct'] >= 40 => 'text-amber-600',
                        default               => 'text-red-500',
                    }],
                ];
            @endphp
            @foreach($cards as $card)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold mt-1 {{ $card['color'] }}">{{ $card['value'] }}</p>
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
            <p class="text-slate-300 text-xs mt-1">No exam marks have been entered for this student.</p>
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

        @foreach($grouped as $examType => $results)
        @php
            $typeTotal    = $results->sum('marks_obtained');
            $typePossible = $results->sum(fn($r) => $r->exam?->total_marks ?? 0);
            $typePct      = $typePossible > 0 ? ($typeTotal / $typePossible) * 100 : 0;
            $tc = $typeColors[$examType] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700'];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
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
                            <td class="px-5 py-3.5 text-center text-slate-400">{{ $total }}</td>
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
    </div>
</x-app-layout>
