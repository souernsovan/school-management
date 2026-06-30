<x-app-layout>

    <x-slot name="header">Subject Management</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Subjects</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $subjects->total() }} subject{{ $subjects->total() !== 1 ? 's' : '' }} found</p>
            </div>
            @canany(['manage subjects', 'create subjects'])
            <a href="{{ route('subjects.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Subject
            </a>
            @endcanany
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <form method="GET" action="{{ route('subjects.index') }}" class="flex flex-wrap items-center gap-3">

                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50">
                </div>

                <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition font-medium shadow-sm">Search</button>

                @if(request('search'))
                    <a href="{{ route('subjects.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-50 transition">Clear</a>
                @endif

                <div class="flex items-center gap-2 ml-auto">
                    <label class="text-sm text-slate-500 whitespace-nowrap">Show</label>
                    <select name="per_page" onchange="this.form.submit()" class="rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 py-2 pl-3 pr-8">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-slate-500">per page</span>
                </div>

            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-xs tracking-wide">
                        <th class="px-5 py-3.5 font-semibold">Subject</th>
                        <th class="px-5 py-3.5 font-semibold">Code</th>
                        <th class="px-5 py-3.5 font-semibold">Credit</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($subjects as $subject)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center text-sm shrink-0">{{ strtoupper(substr($subject->name, 0, 1)) }}</div>
                                    <span class="font-semibold text-slate-800">{{ $subject->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($subject->code)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-mono font-semibold text-xs">{{ $subject->code }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-600 font-semibold text-xs">{{ $subject->credit }} cr</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @can('manage subjects')
                                    <a href="{{ route('subjects.edit', $subject) }}" class="p-1.5 rounded-lg text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('subjects.destroy', $subject) }}" method="POST"
                                          data-swal-confirm
                                          data-swal-title="Delete subject?"
                                          data-swal-text="Delete {{ $subject->name }}? This cannot be undone."
                                          data-swal-confirm-text="Yes, delete it">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-red-400 hover:text-red-700 hover:bg-red-50 transition" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <p class="font-medium text-sm">No subjects found</p>
                                    <p class="text-xs">Try adjusting your search</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
          </div>
        </div>

        @if($subjects->hasPages())
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Showing {{ $subjects->firstItem() }}–{{ $subjects->lastItem() }} of {{ $subjects->total() }} subjects</p>
                <div>{{ $subjects->links() }}</div>
            </div>
        @endif

    </div>

</x-app-layout>
