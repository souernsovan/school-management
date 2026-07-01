<x-app-layout>
    <x-slot name="header">Rankings</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Filter card --}}
        <form method="GET" action="{{ route('reports.rankings') }}"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex flex-wrap gap-4 items-end">

                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Class</label>
                    <select name="class_id" required onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Class</option>
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" @selected((string) request('class_id') === (string) $cls->id)>
                            {{ $cls->name }}{{ $cls->section ? ' – ' . $cls->section : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Exam Type</label>
                    <select name="type" onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        @foreach($examTypes as $t)
                        <option value="{{ $t }}" @selected(request('type', '') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-blue-600/20">
                    Generate
                </button>
            </div>
        </form>

        {{-- Month filter chips --}}
        @isset($availableMonths)
        @if($availableMonths->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.rankings', array_filter(['class_id' => request('class_id'), 'type' => request('type'), 'month' => 0], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ isset($month) && $month === 0 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                All
            </a>
            @foreach($availableMonths as $m)
            <a href="{{ route('reports.rankings', array_filter(['class_id' => request('class_id'), 'type' => request('type'), 'month' => $m], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ isset($month) && $month === $m ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                {{ date('M', mktime(0,0,0,$m,1)) }}
            </a>
            @endforeach
        </div>
        @endif
        @endisset

        @php
            $gradeColor = fn(string $g): string => match($g) {
                'A+', 'A' => 'bg-emerald-100 text-emerald-700',
                'B+', 'B' => 'bg-blue-100 text-blue-700',
                'C'       => 'bg-yellow-100 text-yellow-700',
                'D'       => 'bg-orange-100 text-orange-700',
                default   => 'bg-red-100 text-red-700',
            };
            $gradeLabel = fn(float $pct): string => match(true) {
                $pct >= 90 => 'A+', $pct >= 80 => 'A',
                $pct >= 70 => 'B+', $pct >= 60 => 'B',
                $pct >= 50 => 'C',  $pct >= 40 => 'D',
                default    => 'F',
            };
        @endphp

        @if(!isset($rows) && !isset($monthGroups))
        {{-- Empty state --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
            <div class="mx-auto w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="text-slate-600 font-semibold text-sm">Select a class to see the ranking</p>
            <p class="text-slate-400 text-xs mt-1">Optionally filter by exam type</p>
        </div>

        {{-- ── ALL MONTHS: one ranking card per month ───────────────────── --}}
        @elseif(isset($monthGroups))

        @if($monthGroups->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <p class="text-slate-400 text-sm font-medium">No results found.</p>
        </div>
        @else

        <div class="space-y-4">
            @foreach($monthGroups as $group)
            @php $m = $group['month']; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                {{-- Month header --}}
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                            <span class="text-violet-700 font-black text-xs">{{ date('M', mktime(0,0,0,$m,1)) }}</span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">{{ date('F Y', mktime(0,0,0,$m,1)) }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $group['rows']->count() }} students
                                @if($examType) · {{ $examType }} @endif
                                · Max {{ number_format($group['maxTotal'], 0) }} marks
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('reports.rankings', array_filter(['class_id' => request('class_id'), 'type' => request('type'), 'month' => $m], fn($v) => $v !== '' && $v !== null)) }}"
                       class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition">
                        View only {{ date('F', mktime(0,0,0,$m,1)) }} →
                    </a>
                </div>

                {{-- Ranking table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-center px-4 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-16">Rank</th>
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                                <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total</th>
                                <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">%</th>
                                <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($group['rows'] as $row)
                            <tr class="hover:bg-slate-50/60 transition-colors {{ $row['rank'] <= 3 ? 'bg-yellow-50/30' : '' }}">
                                <td class="px-4 py-3 text-center">
                                    @if($row['rank'] === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-yellow-400 text-white font-black text-xs shadow-sm">#1</span>
                                    @elseif($row['rank'] === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-400 text-white font-black text-xs shadow-sm">#2</span>
                                    @elseif($row['rank'] === 3)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600 text-white font-black text-xs shadow-sm">#3</span>
                                    @else
                                        <span class="text-slate-400 font-semibold text-sm">#{{ $row['rank'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0
                                            {{ $row['rank'] === 1 ? 'bg-yellow-100 text-yellow-700' :
                                               ($row['rank'] === 2 ? 'bg-slate-100 text-slate-600' :
                                               ($row['rank'] === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600')) }}">
                                            {{ strtoupper(substr($row['student']->first_name, 0, 1)) }}
                                        </div>
                                        <span class="font-semibold text-slate-800 text-sm">{{ $row['student']->first_name }} {{ $row['student']->last_name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-bold text-slate-800">{{ number_format($row['obtained'], 0) }}</span>
                                    <span class="text-xs text-slate-400 ml-0.5">/ {{ number_format($group['maxTotal'], 0) }}</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-semibold {{ $row['pct'] >= 50 ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($row['pct'], 1) }}%</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $gradeColor($row['grade']) }}">{{ $row['grade'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200 bg-slate-50/80">
                                <td colspan="2" class="px-5 py-2.5 text-xs font-semibold text-slate-500 text-right">Class Average</td>
                                <td class="px-5 py-2.5 text-center">
                                    <span class="font-bold text-slate-700">{{ number_format($group['avgTotal'], 1) }}</span>
                                    <span class="text-xs text-slate-400 ml-0.5">/ {{ number_format($group['maxTotal'], 0) }}</span>
                                </td>
                                <td class="px-5 py-2.5 text-center">
                                    @if($group['maxTotal'] > 0)
                                    <span class="font-semibold text-slate-600">{{ number_format(($group['avgTotal'] / $group['maxTotal']) * 100, 1) }}%</span>
                                    @endif
                                </td>
                                <td class="px-5 py-2.5 text-center">
                                    @if($group['maxTotal'] > 0)
                                    @php $ap = ($group['avgTotal'] / $group['maxTotal']) * 100; @endphp
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $gradeColor($gradeLabel($ap)) }}">{{ $gradeLabel($ap) }}</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── SINGLE MONTH: one flat table ─────────────────────────────── --}}
        @elseif(isset($rows))

        @if($rows->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <p class="text-slate-400 text-sm font-medium">No students or results found for this selection.</p>
        </div>
        @else

        {{-- Info header --}}
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-base font-bold text-slate-800">
                {{ $class->name }}{{ $class->section ? ' – ' . $class->section : '' }}
            </h2>
            @if($examType)
            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">{{ $examType }}</span>
            @endif
            @if($month > 0)
            <span class="px-2.5 py-0.5 bg-violet-100 text-violet-700 rounded-full text-xs font-semibold">{{ date('F', mktime(0,0,0,$month,1)) }}</span>
            @endif
            <span class="text-xs text-slate-400">{{ $rows->count() }} students</span>
        </div>

        {{-- Rankings table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="text-center px-4 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-16">Rank</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                            <th class="text-center px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total</th>
                            <th class="text-center px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">%</th>
                            <th class="text-center px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($rows as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $row['rank'] <= 3 ? 'bg-yellow-50/30' : '' }}">
                            <td class="px-4 py-4 text-center">
                                @if($row['rank'] === 1)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-400 text-white font-black text-sm shadow-sm">#1</span>
                                @elseif($row['rank'] === 2)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-400 text-white font-black text-sm shadow-sm">#2</span>
                                @elseif($row['rank'] === 3)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-600 text-white font-black text-sm shadow-sm">#3</span>
                                @else
                                    <span class="text-slate-400 font-semibold text-sm">#{{ $row['rank'] }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0
                                        {{ $row['rank'] === 1 ? 'bg-yellow-100 text-yellow-700' :
                                           ($row['rank'] === 2 ? 'bg-slate-100 text-slate-600' :
                                           ($row['rank'] === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600')) }}">
                                        {{ strtoupper(substr($row['student']->first_name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800">{{ $row['student']->first_name }} {{ $row['student']->last_name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-lg font-bold text-slate-800">{{ number_format($row['obtained'], 0) }}</span>
                                <span class="text-xs text-slate-400 ml-0.5">/ {{ number_format($maxTotal, 0) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="font-semibold {{ $row['pct'] >= 50 ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($row['pct'], 1) }}%</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-bold {{ $gradeColor($row['grade']) }}">{{ $row['grade'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50/80">
                            <td colspan="2" class="px-5 py-3 text-xs font-semibold text-slate-500 text-right">Class Average</td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-bold text-slate-700">{{ number_format($avgTotal, 1) }}</span>
                                <span class="text-xs text-slate-400 ml-0.5">/ {{ number_format($maxTotal, 0) }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($maxTotal > 0)
                                <span class="font-semibold text-slate-600">{{ number_format(($avgTotal / $maxTotal) * 100, 1) }}%</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($maxTotal > 0)
                                @php $avgPct = ($avgTotal / $maxTotal) * 100; @endphp
                                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-bold {{ $gradeColor($gradeLabel($avgPct)) }}">{{ $gradeLabel($avgPct) }}</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @endif
        @endif
    </div>
</x-app-layout>
