<x-app-layout>

    <x-slot name="header">
        Class Management
    </x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        <!-- Top Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Classes</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $classes->total() }} class{{ $classes->total() !== 1 ? 'es' : '' }} found</p>
            </div>
            @canany(['manage classes', 'create classes'])
            <a href="{{ route('school-classes.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Class
            </a>
            @endcanany
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <form method="GET" action="{{ route('school-classes.index') }}" class="flex items-center gap-3">

                <div class="relative flex-1 max-w-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by class name or section..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50">
                </div>

                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition font-medium shadow-sm">
                    Search
                </button>

                @if(request('search'))
                    <a href="{{ route('school-classes.index') }}"
                       class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                        Clear
                    </a>
                @endif

                <div class="flex items-center gap-2 ml-auto">
                    <label class="text-sm text-slate-500 whitespace-nowrap">Show</label>
                    <select name="per_page"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 py-2 pl-3 pr-8">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-sm text-slate-500">per page</span>
                </div>

            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">

                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-xs tracking-wide">
                        <th class="px-5 py-3.5 font-semibold">Class Name</th>
                        <th class="px-5 py-3.5 font-semibold">Section</th>
                        <th class="px-5 py-3.5 font-semibold">Students</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">

                    @forelse($classes as $class)
                        <tr class="hover:bg-slate-50/60 transition">

                            <!-- Class Name -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-sm shrink-0">
                                        {{ strtoupper(substr($class->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800">{{ $class->name }}</span>
                                </div>
                            </td>

                            <!-- Section -->
                            <td class="px-5 py-4">
                                @if($class->section)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-semibold text-xs">
                                        {{ $class->section }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Student Count -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-600 font-semibold text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5.916-3.519M9 20H4v-2a4 4 0 015.916-3.519M15 7a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zm-18 0a3 3 0 116 0 3 3 0 01-6 0z"/>
                                    </svg>
                                    {{ $class->students_count }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('school-classes.show', $class) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                                       title="View Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z"/>
                                        </svg>
                                        View Detail
                                    </a>

                                    @canany(['manage students', 'create students'])
                                    <a href="{{ route('students.create', ['class_id' => $class->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition"
                                       title="Add Student">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v6m3-3h-6m-3 7H5a2 2 0 01-2-2v-3a4 4 0 014-4h2m0 0a4 4 0 100-8 4 4 0 000 8zm9 3a4 4 0 01-4 4h-1"/>
                                        </svg>
                                        Add Student
                                    </a>
                                    @endcanany

                                    @can('manage classes')
                                    <a href="{{ route('school-classes.edit', $class) }}"
                                       class="p-1.5 rounded-lg text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('school-classes.destroy', $class) }}"
                                          method="POST"
                                          data-swal-confirm
                                          data-swal-title="Delete class?"
                                          data-swal-text="Delete class {{ $class->name }}? This cannot be undone."
                                          data-swal-confirm-text="Yes, delete it">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-red-400 hover:text-red-700 hover:bg-red-50 transition" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <p class="font-medium text-sm">No classes found</p>
                                    <p class="text-xs">Try adjusting your search</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
          </div>
        </div>

        <!-- Pagination -->
        @if($classes->hasPages())
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Showing {{ $classes->firstItem() }}–{{ $classes->lastItem() }} of {{ $classes->total() }} classes
                </p>
                <div>
                    {{ $classes->links() }}
                </div>
            </div>
        @endif

    </div>

</x-app-layout>
