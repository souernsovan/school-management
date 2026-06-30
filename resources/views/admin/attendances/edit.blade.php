<x-app-layout>

    <x-slot name="header">Attendance Management</x-slot>

    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-3xl space-y-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Edit record</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">Update Attendance</h2>
                    <p class="mt-1 text-sm text-slate-500">Change the student status, date, teacher, or remark in one place.</p>
                </div>

                <a href="{{ route('attendances.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                    ← Back
                </a>
            </div>

            <form method="POST" action="{{ route('attendances.update', $attendance) }}" class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                @csrf
                @method('PUT')

                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">
                                {{ $attendance->student?->first_name }} {{ $attendance->student?->last_name }}
                            </h3>
                            <p class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                            {{ $attendance->status === 'Present' ? 'bg-emerald-50 text-emerald-700' :
                               ($attendance->status === 'Absent' ? 'bg-red-50 text-red-700' :
                               ($attendance->status === 'Late' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700')) }}">
                            {{ $attendance->status }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Student <span class="text-red-500">*</span></label>
                            <select name="student_id" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id', $attendance->student_id) == $student->id ? 'selected' : '' }}>
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Class <span class="text-red-500">*</span></label>
                            <select name="class_id" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id', $attendance->class_id) == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}@if($class->section) - {{ $class->section }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="attendance_date"
                                   value="{{ old('attendance_date', $attendance->attendance_date) }}"
                                   class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('attendance_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Teacher <span class="text-red-500">*</span></label>
                            <select name="teacher_id" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $attendance->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->first_name }} {{ $teacher->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Subject</label>
                            <select name="subject_id" class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Optional</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $attendance->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-700">Status <span class="text-red-500">*</span></p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach(['Present','Absent','Late','Permission'] as $s)
                                @php
                                    $current = old('status', $attendance->status);
                                    $active = $current === $s;
                                    $base = [
                                        'Present' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-600 text-white border-emerald-600'],
                                        'Absent' => ['bg-red-50 text-red-700 border-red-200', 'bg-red-600 text-white border-red-600'],
                                        'Late' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500 text-white border-amber-500'],
                                        'Permission' => ['bg-blue-50 text-blue-700 border-blue-200', 'bg-blue-600 text-white border-blue-600'],
                                    ];
                                @endphp
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $s }}" class="sr-only peer" {{ $active ? 'checked' : '' }}>
                                    <span class="inline-flex rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $base[$s][$active ? 1 : 0] }}">
                                        {{ $s }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Remark</label>
                        <textarea name="remark" rows="3"
                                  class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Optional remark...">{{ old('remark', $attendance->remark) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <a href="{{ route('attendances.index') }}"
                       class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
