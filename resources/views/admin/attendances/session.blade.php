<x-app-layout>
    <x-slot name="header">Attendance</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Banner --}}
        <div class="rounded-2xl border border-slate-100 bg-white px-6 py-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Attendance Session</p>
                    <h2 class="mt-1 text-2xl font-bold text-slate-900">
                        {{ $class->name }}{{ $class->section ? ' — ' . $class->section : '' }}
                    </h2>
                    <p class="mt-0.5 text-sm text-slate-500">
                        {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }} · {{ $students->count() }} students
                    </p>
                </div>
                @if($stats['total'] > 0)
                <div class="flex items-center gap-3 text-sm">
                    <span class="flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1.5 font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> {{ $stats['present'] }} Present
                    </span>
                    <span class="flex items-center gap-1.5 rounded-full bg-red-50 border border-red-200 px-3 py-1.5 font-semibold text-red-700">
                        <span class="h-2 w-2 rounded-full bg-red-500"></span> {{ $stats['absent'] }} Absent
                    </span>
                    @if($stats['late'] + $stats['permission'] > 0)
                    <span class="flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-3 py-1.5 font-semibold text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span> {{ $stats['late'] + $stats['permission'] }} Other
                    </span>
                    @endif
                </div>
                @endif
            </div>

            @if($stats['total'] > 0)
            @php $total = max($stats['total'], 1); @endphp
            <div class="mt-4 flex h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="bg-emerald-500 transition-all" style="width:{{ round($stats['present']/$total*100) }}%"></div>
                <div class="bg-red-500 transition-all"     style="width:{{ round($stats['absent']/$total*100) }}%"></div>
                <div class="bg-amber-400 transition-all"   style="width:{{ round(($stats['late']+$stats['permission'])/$total*100) }}%"></div>
            </div>
            @endif
        </div>

        @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-semibold">Please fix the following:</p>
            <ul class="mt-1 list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('attendances.store') }}">
            @csrf
            <input type="hidden" name="class_id"        value="{{ $class->id }}">
            <input type="hidden" name="attendance_date" value="{{ $date }}">

            <div class="grid gap-5 lg:grid-cols-[300px_minmax(0,1fr)]">

                {{-- Sidebar --}}
                <aside class="space-y-4">
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-4">
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Session Details</h3>

                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Teacher <span class="text-red-500 normal-case">*</span></label>
                            <select name="teacher_id"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select teacher</option>
                                @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ old('teacher_id', $first?->teacher_id) == $t->id ? 'selected' : '' }}>
                                    {{ $t->first_name }} {{ $t->last_name }}
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
                                @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id', $first?->subject_id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Delete session --}}
                    @can('manage attendance')
                    <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-semibold text-red-700 uppercase tracking-wide">Danger Zone</p>
                        <p class="mt-1 text-xs text-red-500">Delete all {{ $students->count() }} attendance records for this session.</p>
                        <form method="POST" action="{{ route('attendances.session.destroy', [$class->id, $date]) }}"
                              class="mt-3" onsubmit="return confirm('Delete this entire session? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full rounded-xl border border-red-200 bg-white px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-600 hover:text-white transition">
                                Delete Session
                            </button>
                        </form>
                    </div>
                    @endcan

                    <a href="{{ route('attendances.index') }}"
                       class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        ← Back to Sessions
                    </a>
                </aside>

                {{-- Student table --}}
                <section class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Mark Students</p>
                            <p class="mt-0.5 text-xs text-slate-400">Changes are saved when you click Save Session.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs text-slate-400">Mark all:</span>
                            @foreach(['Present','Absent','Late','Permission'] as $s)
                            <button type="button"
                                    onclick="window.dispatchEvent(new CustomEvent('mark-all',{detail:'{{ $s }}' }))"
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

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50 text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-5 py-3 text-left font-semibold w-[220px]">Student</th>
                                    <th class="px-5 py-3 text-left font-semibold">Status</th>
                                    <th class="px-5 py-3 text-left font-semibold w-[200px]">Remark</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($students as $i => $student)
                                @php $rec = $existing->get($student->id); @endphp
                                <tr x-data="{ status: '{{ $rec?->status ?? 'Present' }}' }"
                                    @mark-all.window="status = $event.detail"
                                    class="hover:bg-slate-50/60 transition">

                                    <input type="hidden" name="rows[{{ $i }}][student_id]" value="{{ $student->id }}">
                                    <input type="hidden" :name="'rows[{{ $i }}][status]'" :value="status">

                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-xs font-bold text-indigo-600">
                                                {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach(['Present','Absent','Late','Permission'] as $s)
                                            <button type="button" @click="status = '{{ $s }}'"
                                                    :class="status === '{{ $s }}'
                                                        ? '{{ $s==='Present' ? 'bg-emerald-600 text-white border-emerald-600'
                                                           : ($s==='Absent'  ? 'bg-red-600 text-white border-red-600'
                                                           : ($s==='Late'    ? 'bg-amber-500 text-white border-amber-500'
                                                           :                   'bg-blue-600 text-white border-blue-600')) }}'
                                                        : '{{ $s==='Present' ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                                                           : ($s==='Absent'  ? 'border-red-200 text-red-700 hover:bg-red-50'
                                                           : ($s==='Late'    ? 'border-amber-200 text-amber-700 hover:bg-amber-50'
                                                           :                   'border-blue-200 text-blue-700 hover:bg-blue-50')) }}'"
                                                    class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition">
                                                {{ $s }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td class="px-5 py-3.5">
                                        <input type="text"
                                               name="rows[{{ $i }}][remark]"
                                               value="{{ old('rows.'.$i.'.remark', $rec?->remark) }}"
                                               placeholder="Optional remark"
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 px-5 py-4 flex items-center justify-between gap-3">
                        <p class="text-xs text-slate-400">{{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }}</p>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 hover:bg-blue-700 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Session
                        </button>
                    </div>
                </section>

            </div>
        </form>
    </div>
</x-app-layout>
