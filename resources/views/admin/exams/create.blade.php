<x-app-layout>
    <x-slot name="header">Add Exam</x-slot>

    <div class="p-4 sm:p-6">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 max-w-3xl mx-auto p-4 sm:p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-5">Create New Exam</h2>

            <form method="POST" action="{{ route('exams.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Class -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Class <span class="text-red-500">*</span></label>
                        <select name="class_id" required
                                class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 @error('class_id') border-red-400 @enderror">
                            <option value="">Select class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }} - {{ $class->section }}</option>
                            @endforeach
                        </select>
                        @error('class_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Subject <span class="text-red-500">*</span></label>
                        <select name="subject_id" required
                                class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 @error('subject_id') border-red-400 @enderror">
                            <option value="">Select subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam Type <span class="text-red-500">*</span></label>
                        <select name="type" required
                                class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-400 @enderror">
                            <option value="">Select type</option>
                            @foreach(\App\Models\Exam::types() as $t)
                                <option value="{{ $t }}" @selected(old('type') == $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam Date <span class="text-red-500">*</span></label>
                        <input type="date" name="exam_date" value="{{ old('exam_date') }}" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 @error('exam_date') border-red-400 @enderror">
                        @error('exam_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Total Marks -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Marks <span class="text-red-500">*</span></label>
                        <input type="number" name="total_marks" value="{{ old('total_marks', 100) }}" min="1" max="1000" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 @error('total_marks') border-red-400 @enderror">
                        @error('total_marks')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Any notes about this exam..."
                              class="w-full rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm">
                        Create Exam
                    </button>
                    <a href="{{ route('exams.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
