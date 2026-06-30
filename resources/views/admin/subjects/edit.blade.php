<x-app-layout>

    <x-slot name="header">Subject Management</x-slot>

    <div class="p-4 sm:p-6">
        <div class="max-w-2xl mx-auto">
            <form method="POST" action="{{ route('subjects.update', $subject) }}"
                  class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                @csrf
                @method('PUT')

                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-800">Edit Subject</h2>
                    <p class="text-sm text-slate-500">Update subject information</p>
                </div>

                <div class="p-4 sm:p-6 space-y-5">

                    <div>
                        <label class="text-sm font-medium text-slate-700">Subject Name <span class="text-red-500">*</span></label>
                        <input name="name" type="text" value="{{ old('name', $subject->name) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="e.g. Mathematics">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Subject Code</label>
                        <input name="code" type="text" value="{{ old('code', $subject->code) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                               placeholder="e.g. MATH101">
                        @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Credit</label>
                        <input name="credit" type="number" min="1" value="{{ old('credit', $subject->credit) }}"
                               class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        @error('credit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="{{ route('subjects.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-sm">Cancel</a>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium shadow-sm">Update Subject</button>
                </div>

            </form>
        </div>
    </div>

</x-app-layout>
