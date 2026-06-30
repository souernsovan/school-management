<x-app-layout>

    <x-slot name="header">Exam Type Management</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Exam Types</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $types->count() }} type{{ $types->count() !== 1 ? 's' : '' }} defined</p>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Left: Type List --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-slate-700">All Exam Types</h3>
                </div>

                @if($types->isEmpty())
                    <div class="p-12 flex flex-col items-center gap-3 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="font-medium text-sm">No exam types yet</p>
                        <p class="text-xs">Add your first exam type using the form.</p>
                    </div>
                @else
                    <ul class="divide-y divide-slate-50">
                        @foreach($types as $type)
                            <li x-data="{ editing: false, name: '{{ addslashes($type->name) }}' }"
                                class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50/60 transition group">

                                {{-- Color dot --}}
                                @php
                                    $dotColors = ['#6366f1','#0ea5e9','#f59e0b','#ef4444','#10b981','#ec4899','#8b5cf6','#14b8a6','#f97316','#84cc16'];
                                    $dot = $dotColors[($loop->index) % count($dotColors)];
                                @endphp
                                <span class="w-3 h-3 rounded-full shrink-0" style="background:{{ $dot }}"></span>

                                {{-- View mode --}}
                                <span x-show="!editing" class="flex-1 text-sm font-semibold text-slate-800">
                                    {{ $type->name }}
                                </span>

                                {{-- Edit mode --}}
                                <template x-if="editing">
                                    <form method="POST" action="{{ route('exam-types.update', $type) }}"
                                          class="flex flex-1 items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" x-model="name"
                                               class="flex-1 rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-1.5 px-3"
                                               required>
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition">
                                            Save
                                        </button>
                                        <button type="button" @click="editing = false; name = '{{ addslashes($type->name) }}'"
                                                class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 text-xs hover:bg-slate-50 transition">
                                            Cancel
                                        </button>
                                    </form>
                                </template>

                                {{-- Usage badge --}}
                                <span x-show="!editing"
                                      class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                             {{ $type->exams_count > 0 ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $type->exams_count }} exam{{ $type->exams_count !== 1 ? 's' : '' }}
                                </span>

                                {{-- Actions --}}
                                <div x-show="!editing" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <button type="button" @click="editing = true"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition"
                                            title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    @if($type->exams_count === 0)
                                        <form method="POST" action="{{ route('exam-types.destroy', $type) }}"
                                              data-swal-confirm
                                              data-swal-title="Delete exam type?"
                                              data-swal-text="Delete exam type \"{{ addslashes($type->name) }}\"? This cannot be undone."
                                              data-swal-confirm-text="Yes, delete it">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition"
                                                    title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-200 cursor-not-allowed" title="In use — cannot delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Right: Add New Type form --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 h-fit">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">Add New Type</h3>
                </div>

                <form method="POST" action="{{ route('exam-types.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Type Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               placeholder="e.g. Semester Exam"
                               class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                               required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                        Add Exam Type
                    </button>
                </form>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Exam types are used when creating exams and scheduling exam timetable entries. Types with existing exams cannot be deleted.
                    </p>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
