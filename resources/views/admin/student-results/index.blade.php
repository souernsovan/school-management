<x-app-layout>
    <x-slot name="header">Student Results</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('student-results.index') }}"
              class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">

            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Search Student</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Class</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                        {{ $class->name }}{{ $class->section ? ' – ' . $class->section : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[140px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Exam Type</label>
                <select name="type" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    @foreach($examTypes as $t)
                    <option value="{{ $t }}" @selected($examType === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-blue-600/20">
                Filter
            </button>

            @if(request()->hasAny(['search', 'class_id', 'type', 'month']))
            <a href="{{ route('student-results.index') }}"
               class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Clear
            </a>
            @endif
        </form>

        {{-- Month filter chips --}}
        @if($availableMonths->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('student-results.index', array_filter(['class_id' => request('class_id'), 'type' => $examType, 'search' => request('search')], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $month === 0 ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                All
            </a>
            @foreach($availableMonths as $m)
            <a href="{{ route('student-results.index', array_filter(['class_id' => request('class_id'), 'type' => $examType, 'month' => $m, 'search' => request('search')], fn($v) => $v !== '' && $v !== null)) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $month === $m ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                {{ date('M', mktime(0,0,0,$m,1)) }}
            </a>
            @endforeach
        </div>
        @endif

        @php
            $gradeColor = fn(?float $pct): string => match(true) {
                $pct === null => 'bg-slate-100 text-slate-500',
                $pct >= 90   => 'bg-emerald-100 text-emerald-700',
                $pct >= 80   => 'bg-blue-100 text-blue-700',
                $pct >= 70   => 'bg-teal-100 text-teal-700',
                $pct >= 60   => 'bg-yellow-100 text-yellow-700',
                $pct >= 50   => 'bg-orange-100 text-orange-700',
                default      => 'bg-red-100 text-red-700',
            };
        @endphp

        {{-- ── ALL MONTHS: grouped by month ──────────────────────────────────── --}}
        @if($monthGroups !== null)

            @if($monthGroups->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-10 h-10 text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
                <p class="text-slate-400 text-sm font-medium">No results found</p>
            </div>
            @else

            <div class="space-y-4">
                @foreach($monthGroups as $group)
                @php
                    $m          = $group['month'];
                    $mStudents  = $group['students'];
                    $monthLabel = date('F Y', mktime(0,0,0,$m,1));
                    $topPct     = $mStudents->first()?->summary['pct'];
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                    {{-- Month header --}}
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                <span class="text-violet-700 font-black text-xs">{{ date('M', mktime(0,0,0,$m,1)) }}</span>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">{{ $monthLabel }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $mStudents->count() }} student{{ $mStudents->count() !== 1 ? 's' : '' }}
                                    @if($examType) · {{ $examType }} @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('student-results.index', array_filter(['class_id' => request('class_id'), 'type' => $examType, 'month' => $m], fn($v) => $v !== '' && $v !== null)) }}"
                           class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition">
                            View only {{ date('F', mktime(0,0,0,$m,1)) }} →
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-8">#</th>
                                    <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                                    <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Exams</th>
                                    <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Marks</th>
                                    <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Overall %</th>
                                    <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Grade</th>
                                    <th class="px-5 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($mStudents as $rank => $s)
                                @php $sum = $s->summary; @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3 text-xs font-bold text-slate-400">{{ $rank + 1 }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($s->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800 leading-tight text-sm">{{ $s->first_name }} {{ $s->last_name }}</p>
                                                @if($s->email)
                                                <p class="text-xs text-slate-400">{{ $s->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center font-semibold text-slate-700">{{ $sum['count'] }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($sum['possible'] > 0)
                                        <span class="text-slate-700 text-sm">{{ number_format($sum['obtained'], 0) }} / {{ number_format($sum['possible'], 0) }}</span>
                                        @else
                                        <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if($sum['pct'] !== null)
                                        <span class="font-semibold {{ $sum['pct'] >= 50 ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ number_format($sum['pct'], 1) }}%
                                        </span>
                                        @else
                                        <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $gradeColor($sum['pct']) }}">
                                            {{ $sum['grade'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('student-results.show', $s) . '?' . http_build_query(array_filter(['class_id' => request('class_id'), 'type' => $examType, 'month' => $m, 'search' => request('search')], fn($v) => $v !== '' && $v !== null)) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            View
                                        </a>
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

        {{-- ── SINGLE MONTH (or no class selected): flat table ────────────────── --}}
        @else

        {{-- Count badge --}}
        <div class="flex flex-wrap items-center gap-2">
            <p class="text-sm text-slate-500">
                Showing <span class="font-semibold text-slate-800">{{ $students->count() }}</span> student{{ $students->count() != 1 ? 's' : '' }}
            </p>
            @if($examType)
            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">{{ $examType }}</span>
            @endif
            @if($month > 0)
            <span class="px-2.5 py-0.5 bg-violet-100 text-violet-700 rounded-full text-xs font-semibold">{{ date('F', mktime(0,0,0,$month,1)) }}</span>
            @endif
        </div>

        @if($students->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-10 h-10 text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/>
            </svg>
            <p class="text-slate-400 text-sm font-medium">No students found</p>
        </div>
        @else

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                            <th class="text-left px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Class</th>
                            <th class="text-center px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Exams</th>
                            <th class="text-center px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Marks</th>
                            <th class="text-center px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Overall %</th>
                            <th class="text-center px-5 py-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Grade</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($students as $s)
                        @php $sum = $s->summary; @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($s->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800 leading-tight">{{ $s->first_name }} {{ $s->last_name }}</p>
                                        @if($s->email)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $s->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-slate-600 text-sm">
                                    {{ $s->schoolClass?->name }}{{ $s->schoolClass?->section ? ' – ' . $s->schoolClass->section : '' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-semibold text-slate-700">{{ $sum['count'] }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($sum['possible'] > 0)
                                <span class="text-slate-700 text-sm">{{ number_format($sum['obtained'], 0) }} / {{ number_format($sum['possible'], 0) }}</span>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($sum['pct'] !== null)
                                <span class="font-semibold {{ $sum['pct'] >= 50 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ number_format($sum['pct'], 1) }}%
                                </span>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $gradeColor($sum['pct']) }}">
                                    {{ $sum['grade'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('student-results.show', $s) . '?' . http_build_query(array_filter(request()->only(['class_id','type','month','search']), fn($v) => $v !== '' && $v !== null)) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    View Results
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @endif
        @endif

    </div>
</x-app-layout>
