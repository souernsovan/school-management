<x-app-layout>

    <x-slot name="header">Attendance Management</x-slot>

    <div class="p-4 sm:p-6 space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-bold text-slate-900">Take Attendance</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">
                    Pick the session details first, then mark each student in a simple row-by-row flow.
                </p>
            </div>

            <a href="{{ route('attendances.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                ← Back
            </a>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('attendances.store') }}" id="attendance-form" class="space-y-5">
            @csrf

            <div class="grid gap-5 lg:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="space-y-5">
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Step 1</p>
                                <h3 class="mt-1 text-lg font-bold text-slate-900">Session Details</h3>
                            </div>
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">Required</span>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Class <span class="text-red-500">*</span></label>
                                <select name="class_id" id="class_id"
                                        class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}@if($class->section) - {{ $class->section }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="attendance_date"
                                       value="{{ old('attendance_date', date('Y-m-d')) }}"
                                       class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                                @error('attendance_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700">Teacher <span class="text-red-500">*</span></label>
                                <select name="teacher_id"
                                        class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a teacher</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-700">Subject</label>
                                <select name="subject_id" id="subject_id"
                                        class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Optional</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">How it works</p>
                        <ol class="mt-4 space-y-3 text-sm text-slate-600">
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">1</span>
                                <span>Select a class, date, and teacher.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">2</span>
                                <span>Review the student list that appears below.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">3</span>
                                <span>Use status buttons or “mark all” to speed things up.</span>
                            </li>
                        </ol>
                    </div>
                </aside>

                <section class="space-y-5">
                    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden" id="student-section">
                        <div class="border-b border-slate-100 bg-slate-50 px-5 py-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Step 2</p>
                                    <h3 class="mt-1 text-lg font-bold text-slate-900">Mark Students</h3>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-slate-400">Mark all as:</span>
                                    @foreach(['Present','Absent','Late','Permission'] as $s)
                                        <button type="button" onclick="markAll('{{ $s }}')"
                                                class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                                                       @if($s=='Present') border-emerald-200 text-emerald-700 hover:bg-emerald-50
                                                       @elseif($s=='Absent') border-red-200 text-red-700 hover:bg-red-50
                                                       @elseif($s=='Late') border-amber-200 text-amber-700 hover:bg-amber-50
                                                       @else border-blue-200 text-blue-700 hover:bg-blue-50 @endif">
                                            {{ $s }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div id="student-list">
                            <div class="px-6 py-16 text-center text-sm text-slate-400" id="empty-msg">
                                Select a class to load students
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('attendances.index') }}"
                           class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </a>
                        <button type="submit" id="submit-btn" disabled
                                class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">
                            Save Attendance
                        </button>
                    </div>
                </section>
            </div>
        </form>
    </div>

    @php
        $studentsByClass = [];
        foreach($students as $student) {
            $studentsByClass[$student->class_id][] = [
                'id'   => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'init' => strtoupper(substr($student->first_name, 0, 1)),
            ];
        }
    @endphp

    <script>
    var studentsByClass = @json($studentsByClass);
    var statuses = ['Present','Absent','Late','Permission'];

    document.getElementById('class_id').addEventListener('change', loadStudents);

    function statusClass(status, active) {
        if (status === 'Present') return active ? 'bg-emerald-600 text-white border-emerald-600' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50';
        if (status === 'Absent') return active ? 'bg-red-600 text-white border-red-600' : 'border-red-200 text-red-700 hover:bg-red-50';
        if (status === 'Late') return active ? 'bg-amber-500 text-white border-amber-500' : 'border-amber-200 text-amber-700 hover:bg-amber-50';
        return active ? 'bg-blue-600 text-white border-blue-600' : 'border-blue-200 text-blue-700 hover:bg-blue-50';
    }

    function loadStudents() {
        var cid = document.getElementById('class_id').value;
        var list = document.getElementById('student-list');
        var btn = document.getElementById('submit-btn');

        if (!cid || !studentsByClass[cid] || !studentsByClass[cid].length) {
            list.innerHTML = '<div class="px-6 py-16 text-center text-sm text-slate-400">' +
                (cid ? 'No students in this class' : 'Select a class to load students') + '</div>';
            btn.disabled = true;
            return;
        }

        var students = studentsByClass[cid];
        var rows = students.map(function(s, i) {
            var btns = statuses.map(function(st) {
                return '<button type="button" onclick="setStatus(' + i + ', \'' + st + '\')" ' +
                    'class="status-btn rounded-lg border px-3 py-1.5 text-xs font-semibold transition ' +
                    statusClass(st, st === 'Present') + '" ' +
                    'data-status="' + st + '" data-idx="' + i + '">' + st + '</button>';
            }).join('');

            return '<input type="hidden" name="rows[' + i + '][student_id]" value="' + s.id + '">' +
                   '<input type="hidden" name="rows[' + i + '][status]" id="status_' + i + '" value="Present">' +
                   '<tr class="border-b border-slate-50 hover:bg-slate-50/70">' +
                   '<td class="px-5 py-4">' +
                       '<div class="flex items-center gap-3">' +
                           '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-sm font-bold text-indigo-600">' + s.init + '</div>' +
                           '<div>' +
                               '<p class="font-semibold text-slate-900">' + s.name + '</p>' +
                               '<p class="text-xs text-slate-400">Select one status for this student</p>' +
                           '</div>' +
                       '</div>' +
                   '</td>' +
                   '<td class="px-5 py-4">' +
                       '<div class="flex flex-wrap gap-1.5">' + btns + '</div>' +
                   '</td>' +
                   '<td class="px-5 py-4">' +
                       '<input type="text" name="rows[' + i + '][remark]" placeholder="Optional remark"' +
                       ' class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 focus:border-blue-500 focus:ring-blue-500">' +
                   '</td>' +
                   '</tr>';
        }).join('');

        list.innerHTML = '<div class="overflow-x-auto"><table class="w-full text-sm">' +
            '<thead><tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400">' +
            '<th class="px-5 py-3 font-semibold w-1/3">Student</th>' +
            '<th class="px-5 py-3 font-semibold">Status</th>' +
            '<th class="px-5 py-3 font-semibold w-1/4">Remark</th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table></div>';

        btn.disabled = false;
    }

    function setStatus(idx, status) {
        document.getElementById('status_' + idx).value = status;
        var btns = document.querySelectorAll('[data-idx="' + idx + '"]');

        btns.forEach(function(btn) {
            var s = btn.dataset.status;
            var active = s === status;
            btn.className = 'status-btn rounded-lg border px-3 py-1.5 text-xs font-semibold transition ' + statusClass(s, active);
        });
    }

    function markAll(status) {
        document.querySelectorAll('.status-btn[data-status="' + status + '"]').forEach(function(btn) {
            setStatus(btn.dataset.idx, status);
        });
    }

    if (document.getElementById('class_id').value) loadStudents();
    </script>

</x-app-layout>
