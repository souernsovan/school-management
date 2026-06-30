<x-app-layout>

    <x-slot name="header">Timetable Management</x-slot>

    <script>
    window.__examsByClass = {!! $exams->groupBy('class_id')->toJson() !!};
    window.__subjects     = {!! $subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toJson() !!};
    </script>

    <div class="p-4 sm:p-6">
        <div class="max-w-2xl mx-auto">

            @php
                $currentType = old('entry_type',
                    $timetable->entry_type ?? ($timetable->exam_id ? 'exam' : ($timetable->title ? 'break' : 'class'))
                );
            @endphp

            <form method="POST" action="{{ route('timetables.update', $timetable) }}"
                  class="bg-white rounded-2xl shadow-lg overflow-hidden"
                  x-data="timetableForm()">
                @csrf
                @method('PUT')

                <div class="bg-slate-50 px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">Edit Timetable Entry</h2>
                    <p class="text-sm text-blue-500 mt-0.5">Update this schedule entry</p>
                </div>

                <div class="p-4 sm:p-6 space-y-5">

                    {{-- Entry Type --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700">Entry Type</label>
                        <div class="mt-2 grid grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="entry_type" value="class" class="sr-only peer"
                                       {{ $currentType === 'class' ? 'checked' : '' }}
                                       x-model="entryType">
                                <div class="peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                            border-2 border-slate-200 rounded-xl py-3 text-center text-sm font-semibold
                                            text-slate-500 hover:border-blue-400 transition select-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    Class
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="entry_type" value="exam" class="sr-only peer"
                                       {{ $currentType === 'exam' ? 'checked' : '' }}
                                       x-model="entryType">
                                <div class="peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600
                                            border-2 border-slate-200 rounded-xl py-3 text-center text-sm font-semibold
                                            text-slate-500 hover:border-red-400 transition select-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    Exam
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="entry_type" value="break" class="sr-only peer"
                                       {{ $currentType === 'break' ? 'checked' : '' }}
                                       x-model="entryType">
                                <div class="peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500
                                            border-2 border-slate-200 rounded-xl py-3 text-center text-sm font-semibold
                                            text-slate-500 hover:border-amber-400 transition select-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                    Break
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Class --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700">Class <span class="text-red-500">*</span></label>
                        <select name="class_id" x-model="classId" @change="examTypeFilter = ''; examId = ''"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select a class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                        {{ old('class_id', $timetable->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}@if($class->section) — {{ $class->section }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- EXAM fields --}}
                    <div x-show="entryType === 'exam'" x-cloak class="space-y-3">

                        {{-- Exam Type (stored directly on timetable) --}}
                        <div>
                            <label class="text-sm font-medium text-slate-700">Exam Type <span class="text-red-500">*</span></label>
                            <select name="exam_type" x-model="examTypeFilter"
                                    :disabled="entryType !== 'exam'"
                                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500">
                                <option value="">Select exam type</option>
                                @foreach(\App\Models\Exam::types() as $t)
                                    <option value="{{ $t }}" {{ old('exam_type', $timetable->exam_type) === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                            @error('exam_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Subject (stored directly on timetable) --}}
                        <div>
                            <label class="text-sm font-medium text-slate-700">Subject</label>
                            <select name="subject_id" x-model="subjectFilter"
                                    :disabled="entryType !== 'exam'"
                                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500">
                                <option value="">Select subject</option>
                                @foreach($subjects as $subj)
                                    <option value="{{ $subj->id }}" {{ old('subject_id', $timetable->subject_id) == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Total Marks --}}
                        <div>
                            <label class="text-sm font-medium text-slate-700">Total Marks <span class="text-red-500">*</span></label>
                            <input type="number" name="total_marks"
                                   value="{{ old('total_marks', $timetable->exam?->total_marks ?? 100) }}"
                                   min="1" max="1000"
                                   :disabled="entryType !== 'exam'"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-red-500 focus:ring-red-500">
                            @error('total_marks')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <input type="hidden" name="exam_id" value="{{ $timetable->exam_id }}" :disabled="entryType !== 'exam'">

                    </div>

                    {{-- BREAK fields --}}
                    <div x-show="entryType === 'break'" x-cloak>
                        <label class="text-sm font-medium text-slate-700">Break Label <span class="text-red-500">*</span></label>
                        <input type="text" name="title"
                               value="{{ old('title', $timetable->title ?? 'Break') }}"
                               :disabled="entryType !== 'break'"
                               placeholder="e.g. Break, Lunch, Free Period"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- CLASS fields --}}
                    <div x-show="entryType === 'class'" x-cloak class="space-y-5">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Subject <span class="text-red-500">*</span></label>
                            <select name="subject_id"
                                    :disabled="entryType !== 'class'"
                                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select a subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}"
                                            {{ old('subject_id', $timetable->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Teacher</label>
                            <select name="teacher_id"
                                    :disabled="entryType !== 'class'"
                                    class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select a teacher</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                            {{ old('teacher_id', $timetable->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->first_name . ' ' . $teacher->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Day --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700">Day <span class="text-red-500">*</span></label>
                        <select name="day"
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select a day</option>
                            @foreach($days as $day)
                                <option value="{{ $day }}"
                                        {{ old('day', $timetable->day) == $day ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                        @error('day')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Time --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Start Time <span class="text-red-500">*</span></label>
                            <input type="time" name="start_time"
                                   value="{{ old('start_time', substr($timetable->start_time, 0, 5)) }}"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('start_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">End Time <span class="text-red-500">*</span></label>
                            <input type="time" name="end_time"
                                   value="{{ old('end_time', substr($timetable->end_time, 0, 5)) }}"
                                   class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('end_time')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Room (not for exam) --}}
                    <div x-show="entryType !== 'exam'">
                        <label class="text-sm font-medium text-slate-700">Room</label>
                        <input type="text" name="room"
                               value="{{ old('room', $timetable->room) }}"
                               placeholder="e.g. Room 101"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                </div>

                <div class="bg-slate-50 px-6 py-4 border-t flex justify-end gap-3">
                    <a href="{{ route('timetables.index') }}"
                       class="px-4 py-2 rounded-xl border hover:bg-slate-100 text-sm">Cancel</a>
                    <button type="submit"
                            class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow text-sm font-medium">
                        Save Changes
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
    function timetableForm() {
        return {
            entryType:      '{{ $currentType }}',
            classId:        '{{ old('class_id', $timetable->class_id) }}',
            examTypeFilter: '{{ old('exam_type', $timetable->exam_type ?? '') }}',
            subjectFilter:  '{{ old('subject_id', $timetable->subject_id ?? '') }}',
            examId:         '{{ old('exam_id', $timetable->exam_id ?? '') }}',

            get _classExams() {
                if (!this.classId) return [];
                return (window.__examsByClass || {})[this.classId] || [];
            },

            get availableSubjects() {
                return window.__subjects || [];
            },

            get filteredExams() {
                var exams = this._classExams;
                if (this.examTypeFilter) {
                    var t = this.examTypeFilter;
                    exams = exams.filter(function(e) { return e.type === t; });
                }
                if (this.subjectFilter) {
                    var s = String(this.subjectFilter);
                    exams = exams.filter(function(e) { return e.subject && String(e.subject.id) === s; });
                }
                return exams;
            },
        };
    }
    </script>

</x-app-layout>
