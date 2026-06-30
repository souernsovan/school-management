<x-app-layout>

    <x-slot name="header">Class Detail</x-slot>

    <div class="p-4 sm:p-6 space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('school-classes.index') }}"
                   class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Classes
                </a>
                <h2 class="mt-3 text-2xl font-bold text-slate-900">
                    {{ $schoolClass->name }}
                    @if($schoolClass->section) <span class="text-slate-400">- {{ $schoolClass->section }}</span> @endif
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Class detail, enrolled students, timetable items, and exams in one view.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @canany(['manage students', 'create students'])
                    <a href="{{ route('students.create', ['class_id' => $schoolClass->id]) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v6m3-3h-6m-3 7H5a2 2 0 01-2-2v-3a4 4 0 014-4h2m0 0a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                        Add Student
                    </a>
                @endcanany

                @can('manage classes')
                    <a href="{{ route('school-classes.edit', $schoolClass) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Class
                    </a>
                @endcan
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Students</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $schoolClass->students_count }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Timetable Items</p>
                <p class="mt-2 text-2xl font-bold text-blue-600">{{ $schoolClass->timetables_count }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Exams</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $schoolClass->exams_count }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Section</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">
                    {{ $schoolClass->section ?: 'None' }}
                </p>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-5">
                <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50 px-5 py-4">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">About Class</h3>
                    </div>
                    <div class="p-5 space-y-4 text-sm">
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Name</p>
                            <p class="font-semibold text-slate-900">{{ $schoolClass->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Section</p>
                            <p class="font-semibold text-slate-900">{{ $schoolClass->section ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Description</p>
                            <p class="leading-6 text-slate-600">{{ $schoolClass->description ?: 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Students</h3>
                        <span class="text-xs text-slate-400">{{ $schoolClass->students_count }} total</span>
                    </div>

                    <div class="divide-y divide-slate-50">
                        @forelse($schoolClass->students as $student)
                            <a href="{{ route('students.show', $student) }}" class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-sm font-bold text-indigo-600">
                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 truncate">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ $student->email ?: 'No email linked' }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400">View</span>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-400">
                                No students assigned to this class yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Recent Timetable</h3>
                        <span class="text-xs text-slate-400">{{ $schoolClass->timetables_count }} items</span>
                    </div>

                    <div class="divide-y divide-slate-50">
                        @forelse($schoolClass->timetables as $entry)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $entry->title ?: ($entry->subject?->name ?? 'Untitled entry') }}</p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ $entry->day }} · {{ $entry->start_time }} - {{ $entry->end_time }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $entry->subject?->name ?: 'No subject' }}
                                            @if($entry->teacher)
                                                · {{ $entry->teacher->first_name }} {{ $entry->teacher->last_name }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                        {{ $entry->entry_type }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-400">
                                No timetable items yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Recent Exams</h3>
                        <span class="text-xs text-slate-400">{{ $schoolClass->exams_count }} exams</span>
                    </div>

                    <div class="divide-y divide-slate-50">
                        @forelse($schoolClass->exams as $exam)
                            <div class="px-5 py-4">
                                <p class="font-semibold text-slate-900">{{ $exam->subject?->name ?: 'Untitled exam' }}</p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $exam->type }} · {{ $exam->exam_date?->format('d M Y') }} · {{ $exam->total_marks }} marks
                                </p>
                                @if($exam->description)
                                    <p class="mt-2 text-sm text-slate-500 leading-6">{{ $exam->description }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-400">
                                No exams scheduled yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
