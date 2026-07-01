<x-app-layout>
    <x-slot name="header">Edit Exam</x-slot>

    <div class="p-4 sm:p-6">

        <div class="max-w-2xl mx-auto space-y-5">

            {{-- Page header --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('exams.show', $exam) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Edit Exam</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Update the details below</p>
                </div>
            </div>

            <form method="POST" action="{{ route('exams.update', $exam) }}" class="space-y-4">
                @csrf @method('PATCH')

                {{-- ── Section 1: Class & Subject ─────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Class & Subject</p>
                    </div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Class --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                                Class <span class="text-red-500 normal-case">*</span>
                            </label>
                            <select name="class_id" required
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition {{ $errors->has('class_id') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                                <option value="">— Select class —</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id', $exam->class_id) == $class->id)>
                                    {{ $class->name }}{{ $class->section ? ' – '.$class->section : '' }}
                                </option>
                                @endforeach
                            </select>
                            @error('class_id')
                            <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                                Subject <span class="text-red-500 normal-case">*</span>
                            </label>
                            <select name="subject_id" required
                                    class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition {{ $errors->has('subject_id') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                                <option value="">— Select subject —</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id', $exam->subject_id) == $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                            <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ── Section 2: Exam Type ────────────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Exam Type <span class="text-red-500">*</span></p>
                    </div>
                    <div class="p-5">
                        <select name="type" required
                                class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition {{ $errors->has('type') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                            <option value="">— Select type —</option>
                            @foreach(\App\Models\Exam::types() as $t)
                            <option value="{{ $t }}" @selected(old('type', $exam->type) === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                        @error('type')
                        <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>

                {{-- ── Section 3: Date & Marks ─────────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Schedule & Marks</p>
                    </div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Date --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                                Exam Date <span class="text-red-500 normal-case">*</span>
                            </label>
                            <input type="date" name="exam_date" value="{{ old('exam_date', $exam->exam_date->format('Y-m-d')) }}" required
                                   class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition {{ $errors->has('exam_date') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                            @error('exam_date')
                            <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Total Marks --}}
                        <div x-data="{ marks: {{ old('total_marks', $exam->total_marks) }} }">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                                Total Marks <span class="text-red-500 normal-case">*</span>
                            </label>
                            <input type="number" name="total_marks" x-model="marks"
                                   min="1" max="1000" required
                                   class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition {{ $errors->has('total_marks') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach([25, 50, 75, 100, 150, 200] as $preset)
                                <button type="button" @click="marks = {{ $preset }}"
                                        :class="marks == {{ $preset }} ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-500 border-slate-200 hover:border-blue-300 hover:text-blue-600'"
                                        class="px-2.5 py-0.5 rounded-lg border text-xs font-semibold transition">
                                    {{ $preset }}
                                </button>
                                @endforeach
                            </div>
                            @error('total_marks')
                            <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ── Section 4: Notes (optional) ─────────────────── --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Notes
                            <span class="text-slate-400 font-normal ml-1 text-xs">(optional)</span>
                        </p>
                    </div>
                    <div class="p-5">
                        <textarea name="description" rows="3"
                                  placeholder="Any notes or instructions about this exam..."
                                  class="w-full rounded-xl border px-3 py-2.5 text-sm text-slate-800 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition resize-none {{ $errors->has('description') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">{{ old('description', $exam->description) }}</textarea>
                        @error('description')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ── Actions ─────────────────────────────────────── --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm shadow-blue-600/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                    <a href="{{ route('exams.show', $exam) }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>
</x-app-layout>
