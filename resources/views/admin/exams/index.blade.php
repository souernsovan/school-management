<x-app-layout>
    <x-slot name="header">Exams</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        <!-- Header row -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Exams</h2>
                <p class="text-sm text-slate-500 mt-0.5">Manage exams and enter student marks</p>
            </div>
            @can('manage exams')
            <a href="{{ route('exams.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Exam
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

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <div class="flex flex-wrap items-end gap-3">

                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Class</label>
                    <select name="class_id" onchange="this.form.submit()"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Classes</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" @selected($classId == $cls->id)>{{ $cls->name }} – {{ $cls->section }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[140px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Type</label>
                    <select name="type" onchange="this.form.submit()"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        @foreach(\App\Models\Exam::types() as $t)
                            <option value="{{ $t }}" @selected($examType === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                @if($availableMonths->isNotEmpty())
                <div class="min-w-[130px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Month</label>
                    <select name="month" onchange="this.form.submit()"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Months</option>
                        @foreach($availableMonths as $m)
                        <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="min-w-[130px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Status</label>
                    <select name="status" onchange="this.form.submit()"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="upcoming" @selected($status === 'upcoming')>Upcoming</option>
                        <option value="past"     @selected($status === 'past')>Past</option>
                    </select>
                </div>

                @if($classId || $examType || $month || $status || request('subject_id'))
                <div class="self-end">
                    <a href="{{ route('exams.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                </div>
                @endif
            </div>
        </form>

        <!-- Month chips — always visible when a class is selected -->
        @if($classId && $availableMonths->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('exams.index', array_filter(['class_id' => $classId, 'type' => $examType, 'status' => $status], fn($v) => $v !== '')) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $month === '' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                All
            </a>
            @foreach($availableMonths as $m)
            @php $active = (int)$month === $m; @endphp
            <a href="{{ route('exams.index', array_filter(['class_id' => $classId, 'type' => $examType, 'month' => $m, 'status' => $status], fn($v) => $v !== '')) }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold border transition
                      {{ $active ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:bg-blue-50' }}">
                {{ date('M', mktime(0,0,0,$m,1)) }}
            </a>
            @endforeach
        </div>
        @endif

        <!-- Empty state -->
        @if($exams->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-12 h-12 text-slate-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-slate-400 text-sm font-medium">No exams found</p>
            @if($month)
            <p class="text-slate-400 text-xs mt-1">for {{ date('F', mktime(0,0,0,(int)$month,1)) }}{{ $examType ? ' · '.$examType : '' }}</p>
            @endif
            @can('manage exams')
            <a href="{{ route('exams.create') }}" class="mt-3 inline-block text-xs text-blue-600 hover:underline">+ Add an exam</a>
            @endcan
        </div>

        @else

        @php
            // Group the current page's exams by "Month Year"
            $grouped = $exams->getCollection()->groupBy(fn($e) => $e->exam_date->format('Y-m'));
        @endphp

        <div class="space-y-4">
            @foreach($grouped as $yearMonth => $monthExams)
            @php
                $firstDate  = $monthExams->first()->exam_date;
                $monthLabel = $firstDate->format('F Y');
                $hasUpcoming = $monthExams->where('exam_date', '>=', now()->toDateString())->isNotEmpty();
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                <!-- Month header -->
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                            <span class="text-blue-700 font-black text-xs">{{ $firstDate->format('M') }}</span>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">{{ $monthLabel }}</p>
                            <p class="text-xs text-slate-400">{{ $monthExams->count() }} exam{{ $monthExams->count() !== 1 ? 's' : '' }}
                                · {{ $monthExams->sum('total_marks') }} total marks</p>
                        </div>
                    </div>
                    @if($hasUpcoming)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Has Upcoming</span>
                    @endif
                </div>

                <!-- Exams table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Type</th>
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Subject</th>
                                @if(!$classId)
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Class</th>
                                @endif
                                <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Date</th>
                                <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Marks</th>
                                <th class="text-center px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Results</th>
                                <th class="text-right px-5 py-2.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($monthExams->sortByDesc('exam_date') as $exam)
                            @php
                                $isToday  = $exam->exam_date->isToday();
                                $isPast   = $exam->exam_date->isPast() && !$isToday;
                                $needsMark = $isPast && $exam->results_count === 0;
                            @endphp
                            <tr class="{{ $needsMark ? 'bg-amber-50/40' : 'hover:bg-slate-50/40' }} transition-colors">
                                <td class="px-5 py-3">
                                    @php $typeColor = (\App\Models\ExamType::twFor($exam->type))['single']; @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeColor }}">
                                        {{ $exam->type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $exam->subject->name ?? '—' }}</td>
                                @if(!$classId)
                                <td class="px-5 py-3 text-slate-500 text-xs">
                                    {{ $exam->schoolClass->name ?? '—' }}{{ $exam->schoolClass?->section ? ' – '.$exam->schoolClass->section : '' }}
                                </td>
                                @endif
                                <td class="px-5 py-3">
                                    <p class="text-slate-700 font-medium text-sm">{{ $exam->exam_date->format('d M Y') }}</p>
                                    @if($isToday)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 mt-0.5">Today</span>
                                    @elseif($isPast)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 mt-0.5">Past</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 mt-0.5">Upcoming</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center text-slate-600 font-medium">{{ number_format($exam->total_marks, 0) }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if($exam->results_count > 0)
                                    <span class="font-semibold text-emerald-600">{{ $exam->results_count }}</span>
                                    <span class="text-xs text-slate-400"> entered</span>
                                    @elseif($isPast)
                                    <span class="text-xs text-amber-500 font-semibold">No scores</span>
                                    @else
                                    <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('exams.show', $exam) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Marks
                                        </a>
                                        @can('manage exams')
                                        <a href="{{ route('exams.edit', $exam) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('exams.destroy', $exam) }}"
                                              data-swal-confirm
                                              data-swal-title="Delete exam?"
                                              data-swal-text="Delete this exam and all its results? This cannot be undone."
                                              data-swal-confirm-text="Yes, delete it">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                                Delete
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        @if($exams->hasPages())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            {{ $exams->links() }}
        </div>
        @endif

        @endif

    </div>
</x-app-layout>
