<x-app-layout>
    <x-slot name="header">Attendance</x-slot>

    <div class="p-4 sm:p-6 space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Take Attendance</h2>
                <p class="mt-1 text-sm text-slate-500">Select the class and date, then mark each student.</p>
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
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('attendances.store') }}" id="attendance-form" class="space-y-5">
            @csrf

            <div class="grid gap-5 lg:grid-cols-[300px_minmax(0,1fr)]">
                <aside class="space-y-4">
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-4">
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Session Details</h3>

                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Class <span class="text-red-500 normal-case">*</span></label>
                            <select name="class_id" id="class_id"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a class</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}{{ $class->section ? ' — '.$class->section : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('class_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Date <span class="text-red-500 normal-case">*</span></label>
                            <input type="date" name="attendance_date" id="attendance_date"
                                   value="{{ old('attendance_date', date('Y-m-d')) }}"
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('attendance_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Teacher <span class="text-red-500 normal-case">*</span></label>
                            <select name="teacher_id"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Subject</label>
                            <select name="subject_id"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Optional</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </aside>

                <section class="space-y-4">
                    {{-- Duplicate session warning (hidden by default) --}}
                    <div id="duplicate-warning" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Session already exists</p>
                                <p class="mt-0.5 text-xs text-amber-700">Attendance was already taken for this class on this date. Saving will overwrite existing records.</p>
                                <a id="edit-session-link" href="#"
                                   class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 underline hover:text-amber-900 transition">
                                    Edit existing session instead →
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50 px-5 py-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Mark Students</p>
                                    <p class="mt-0.5 text-xs text-slate-400">Default is Present for all.</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-slate-400">Mark all:</span>
                                    @foreach(['Present','Absent','Late','Permission'] as $s)
                                    <button type="button" onclick="markAll('{{ $s }}')"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                                                {{ $s==='Present'    ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                                                 : ($s==='Absent'    ? 'border-red-200 text-red-700 hover:bg-red-50'
                                                 : ($s==='Late'      ? 'border-amber-200 text-amber-700 hover:bg-amber-50'
                                                 :                     'border-blue-200 text-blue-700 hover:bg-blue-50')) }}">
                                        {{ $s }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div id="student-list">
                            <div class="px-6 py-16 text-center text-sm text-slate-400">
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
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Attendance
                        </button>
                    </div>
                </section>
            </div>
        </form>
    </div>

    <script>
    var existingSessions = @json($existingSessions);
    var statuses = ['Present','Absent','Late','Permission'];
    var classStudentsCache = {};

    function statusClass(status, active) {
        if (status === 'Present')    return active ? 'bg-emerald-600 text-white border-emerald-600' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50';
        if (status === 'Absent')     return active ? 'bg-red-600 text-white border-red-600'         : 'border-red-200 text-red-700 hover:bg-red-50';
        if (status === 'Late')       return active ? 'bg-amber-500 text-white border-amber-500'     : 'border-amber-200 text-amber-700 hover:bg-amber-50';
        return active ? 'bg-blue-600 text-white border-blue-600' : 'border-blue-200 text-blue-700 hover:bg-blue-50';
    }

    function checkDuplicate() {
        var cid  = document.getElementById('class_id').value;
        var date = document.getElementById('attendance_date').value;
        var warn = document.getElementById('duplicate-warning');
        var link = document.getElementById('edit-session-link');

        if (cid && date && existingSessions[cid] && existingSessions[cid].indexOf(date) !== -1) {
            warn.classList.remove('hidden');
            link.href = '/admin/attendances/' + cid + '/' + date + '/edit';
        } else {
            warn.classList.add('hidden');
        }
    }

    function loadStudents() {
        var cid = document.getElementById('class_id').value;
        var list = document.getElementById('student-list');
        var btn  = document.getElementById('submit-btn');

        checkDuplicate();

        if (!cid) {
            list.innerHTML = '<div class="px-6 py-16 text-center text-sm text-slate-400">Select a class to load students</div>';
            btn.disabled = true;
            return;
        }

        if (classStudentsCache[cid]) {
            renderStudents(classStudentsCache[cid]);
            return;
        }

        list.innerHTML = '<div class="px-6 py-10 text-center text-sm text-slate-400">Loading students…</div>';

        fetch('/admin/attendances/students?class_id=' + cid, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(students) {
                classStudentsCache[cid] = students;
                renderStudents(students);
            });
    }

    function renderStudents(students) {
        var list = document.getElementById('student-list');
        var btn  = document.getElementById('submit-btn');

        if (!students.length) {
            list.innerHTML = '<div class="px-6 py-16 text-center text-sm text-slate-400">No students in this class</div>';
            btn.disabled = true;
            return;
        }

        var rows = students.map(function(s, i) {
            var btns = statuses.map(function(st) {
                return '<button type="button" onclick="setStatus(' + i + ',\'' + st + '\')" ' +
                    'class="status-btn rounded-lg border px-3 py-1.5 text-xs font-semibold transition ' +
                    statusClass(st, st === 'Present') + '" ' +
                    'data-status="' + st + '" data-idx="' + i + '">' + st + '</button>';
            }).join('');

            return '<input type="hidden" name="rows[' + i + '][student_id]" value="' + s.id + '">' +
                   '<input type="hidden" name="rows[' + i + '][status]" id="status_' + i + '" value="Present">' +
                   '<tr class="border-b border-slate-50 hover:bg-slate-50/70 transition">' +
                   '<td class="px-5 py-3.5">' +
                       '<div class="flex items-center gap-3">' +
                           '<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-xs font-bold text-indigo-600">' + s.init + '</div>' +
                           '<span class="font-medium text-slate-800">' + s.name + '</span>' +
                       '</div>' +
                   '</td>' +
                   '<td class="px-5 py-3.5"><div class="flex flex-wrap gap-1.5">' + btns + '</div></td>' +
                   '<td class="px-5 py-3.5">' +
                       '<input type="text" name="rows[' + i + '][remark]" placeholder="Optional remark" ' +
                       'class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-400">' +
                   '</td>' +
                   '</tr>';
        }).join('');

        list.innerHTML = '<div class="overflow-x-auto"><table class="w-full text-sm">' +
            '<thead><tr class="border-b border-slate-100 bg-slate-50/50 text-xs uppercase tracking-wide text-slate-400">' +
            '<th class="px-5 py-3 font-semibold w-[220px] text-left">Student</th>' +
            '<th class="px-5 py-3 font-semibold text-left">Status</th>' +
            '<th class="px-5 py-3 font-semibold w-[200px] text-left">Remark</th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table></div>';

        btn.disabled = false;
    }

    function setStatus(idx, status) {
        document.getElementById('status_' + idx).value = status;
        document.querySelectorAll('[data-idx="' + idx + '"]').forEach(function(btn) {
            var s = btn.dataset.status;
            btn.className = 'status-btn rounded-lg border px-3 py-1.5 text-xs font-semibold transition ' + statusClass(s, s === status);
        });
    }

    function markAll(status) {
        document.querySelectorAll('.status-btn[data-status="' + status + '"]').forEach(function(btn) {
            setStatus(btn.dataset.idx, status);
        });
    }

    document.getElementById('class_id').addEventListener('change', loadStudents);
    document.getElementById('attendance_date').addEventListener('change', checkDuplicate);

    if (document.getElementById('class_id').value) loadStudents();
    </script>

</x-app-layout>
