<x-student-layout>

    <div class="p-4 sm:p-6 space-y-5">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">Class Rankings</h2>
            <p class="text-sm text-slate-500 mt-0.5">
                See how you rank among your classmates.
            </p>
        </div>

        @if(!$me)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <div class="mx-auto w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="text-slate-700 font-semibold text-sm">No student record found</p>
            <p class="text-slate-400 text-xs mt-1">Please contact your administrator.</p>
        </div>

        @else

        {{-- Filters --}}
        <form method="GET" action="{{ route('student.rankings') }}"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Exam Type</label>
                <select name="type" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Exam Types</option>
                    @foreach($examTypes as $t)
                    <option value="{{ $t }}" @selected($examType === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-blue-600/20">
                Filter
            </button>
        </form>

        {{-- Month filter chips --}}
        @if($availableMonths->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('student.rankings', array_filter(['type' => $examType, 'month' => 0], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $month === 0 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                All
            </a>
            @foreach($availableMonths as $m)
            <a href="{{ route('student.rankings', array_filter(['type' => $examType, 'month' => $m], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $month === $m ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                {{ date('M', mktime(0,0,0,$m,1)) }}
            </a>
            @endforeach
        </div>
        @endif

        @php
            $gradeColor = fn(string $g): string => match($g) {
                'A+','A' => 'bg-emerald-100 text-emerald-700',
                'B+','B' => 'bg-blue-100 text-blue-700',
                'C'      => 'bg-teal-100 text-teal-700',
                'D'      => 'bg-yellow-100 text-yellow-700',
                'E'      => 'bg-orange-100 text-orange-700',
                default  => 'bg-red-100 text-red-700',
            };
        @endphp

        {{-- ── ALL MONTHS: one card per month ─────────────────────────────── --}}
        @if(isset($monthGroups))

        @if($monthGroups->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-12 text-center">
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
                            <p class="text-xs text-slate-400">{{ $group['rows']->count() }} students · Max {{ number_format($group['maxTotal'], 0) }} marks</p>
                        </div>
                    </div>
                    <a href="{{ route('student.rankings', array_filter(['type' => $examType, 'month' => $m], fn($v) => $v !== '' && $v !== null)) }}"
                       class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition">
                        View only {{ date('F', mktime(0,0,0,$m,1)) }} →
                    </a>
                </div>
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
                            @php $isMe = $row['student']->id === $me->id; @endphp
                            <tr class="{{ $isMe ? 'bg-blue-50/60' : ($row['rank'] <= 3 ? 'bg-yellow-50/30' : 'hover:bg-slate-50/60') }} transition-colors">
                                <td class="px-4 py-3 text-center">
                                    @if($row['rank'] === 1) <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-yellow-400 text-white font-black text-xs shadow-sm">#1</span>
                                    @elseif($row['rank'] === 2) <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-400 text-white font-black text-xs shadow-sm">#2</span>
                                    @elseif($row['rank'] === 3) <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600 text-white font-black text-xs shadow-sm">#3</span>
                                    @else <span class="text-slate-400 font-semibold text-sm">#{{ $row['rank'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0
                                            {{ $isMe ? 'bg-blue-600 text-white' : ($row['rank'] === 1 ? 'bg-yellow-100 text-yellow-700' : ($row['rank'] === 2 ? 'bg-slate-100 text-slate-600' : ($row['rank'] === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600'))) }}">
                                            {{ strtoupper(substr($row['student']->first_name, 0, 1)) }}
                                        </div>
                                        <span class="font-semibold {{ $isMe ? 'text-blue-700' : 'text-slate-800' }} text-sm">
                                            {{ $row['student']->first_name }} {{ $row['student']->last_name }}
                                            @if($isMe) <span class="ml-1.5 text-[10px] font-bold text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded-full">You</span> @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-bold {{ $isMe ? 'text-blue-700' : 'text-slate-800' }}">{{ number_format($row['obtained'], 0) }}</span>
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
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── SINGLE MONTH ─────────────────────────────────────────────── --}}
        @else
        @php $myRow = isset($rows) ? $rows->first(fn($r) => $r['student']->id === $me->id) : null; @endphp

        {{-- My position card --}}
        @if(isset($myRow) && $myRow)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wide mb-3">
                Your Position
                @if($month > 0)
                <span class="ml-2 text-violet-500 normal-case font-semibold">· {{ date('F', mktime(0,0,0,$month,1)) }}</span>
                @endif
            </p>
            <div class="flex flex-wrap items-center gap-5">
                <div class="text-center">
                    <p class="text-4xl font-black text-slate-800">#{{ $myRow['rank'] }}</p>
                    <p class="text-slate-400 text-xs mt-0.5">Rank</p>
                </div>
                <div class="w-px h-10 bg-slate-200 hidden sm:block"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($myRow['obtained'], 0) }} <span class="text-base font-normal text-slate-400">/ {{ number_format($maxTotal, 0) }}</span></p>
                    <p class="text-slate-400 text-xs mt-0.5">Total Marks</p>
                </div>
                <div class="w-px h-10 bg-slate-200 hidden sm:block"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($myRow['pct'], 1) }}%</p>
                    <p class="text-slate-400 text-xs mt-0.5">Percentage</p>
                </div>
                <div class="w-px h-10 bg-slate-200 hidden sm:block"></div>
                <div class="text-center">
                    <span class="{{ $gradeColor($myRow['grade']) }} inline-block px-3 py-1 rounded-full text-sm font-bold">{{ $myRow['grade'] }}</span>
                    <p class="text-slate-400 text-xs mt-0.5">Grade</p>
                </div>
                <div class="flex-1 min-w-[120px]">
                    <p class="text-slate-400 text-xs mb-1">{{ number_format($myRow['pct'], 1) }}% of {{ number_format($maxTotal, 0) }}</p>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ min($myRow['pct'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(!isset($rows) || $rows->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-12 text-center">
            <p class="text-slate-400 text-sm font-medium">No results found for this selection.</p>
        </div>
        @else
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
                        @php $isMe = $row['student']->id === $me->id; @endphp
                        <tr class="{{ $isMe ? 'bg-blue-50/60' : ($row['rank'] <= 3 ? 'bg-yellow-50/30' : 'hover:bg-slate-50/60') }} transition-colors">
                            <td class="px-4 py-3.5 text-center">
                                @if($row['rank'] === 1) <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-400 text-white font-black text-sm shadow-sm">#1</span>
                                @elseif($row['rank'] === 2) <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-400 text-white font-black text-sm shadow-sm">#2</span>
                                @elseif($row['rank'] === 3) <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-600 text-white font-black text-sm shadow-sm">#3</span>
                                @else <span class="text-slate-400 font-semibold text-sm">#{{ $row['rank'] }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0
                                        {{ $isMe ? 'bg-blue-600 text-white' : ($row['rank'] === 1 ? 'bg-yellow-100 text-yellow-700' : ($row['rank'] === 2 ? 'bg-slate-100 text-slate-600' : ($row['rank'] === 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-50 text-blue-600'))) }}">
                                        {{ strtoupper(substr($row['student']->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold {{ $isMe ? 'text-blue-700' : 'text-slate-800' }}">{{ $row['student']->first_name }} {{ $row['student']->last_name }}</span>
                                        @if($isMe) <span class="ml-2 text-[10px] font-bold text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded-full">You</span> @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-lg font-bold {{ $isMe ? 'text-blue-700' : 'text-slate-800' }}">{{ number_format($row['obtained'], 0) }}</span>
                                <span class="text-xs text-slate-400 ml-0.5">/ {{ number_format($maxTotal, 0) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-semibold text-sm {{ $row['pct'] >= 50 ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($row['pct'], 1) }}%</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-bold {{ $gradeColor($row['grade']) }}">{{ $row['grade'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($maxTotal > 0)
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50/80">
                            <td colspan="2" class="px-5 py-3 text-xs font-semibold text-slate-500 text-right">Class Average</td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-bold text-slate-700">{{ number_format($avgTotal, 1) }}</span>
                                <span class="text-xs text-slate-400 ml-0.5">/ {{ number_format($maxTotal, 0) }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-semibold text-slate-600">{{ number_format(($avgTotal / $maxTotal) * 100, 1) }}%</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @endif
        @endif
        @endif
    </div>

</x-student-layout>
