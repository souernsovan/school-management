<x-app-layout>

    <x-slot name="header">Teacher Management</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Teachers</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $teachers->total() }} teacher{{ $teachers->total() !== 1 ? 's' : '' }} found</p>
            </div>
            @can('manage teachers')
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('teachers.export', request()->only('search')) }}"
                   class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2.5 rounded-xl hover:bg-emerald-100 transition font-medium text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                    Export CSV
                </a>
                <a href="{{ route('teachers.create') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Teacher
                </a>
            </div>
            @endcan
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <form method="GET" action="{{ route('teachers.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50">
                </div>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition font-medium shadow-sm">Search</button>
                @if(request('search'))
                    <a href="{{ route('teachers.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-50 transition">Clear</a>
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
                        <th class="px-5 py-3.5 font-semibold">Teacher</th>
                        <th class="px-5 py-3.5 font-semibold">Email</th>
                        <th class="px-5 py-3.5 font-semibold">Phone</th>
                        <th class="px-5 py-3.5 font-semibold">Gender</th>
                        <th class="px-5 py-3.5 font-semibold">Date of Birth</th>
                        <th class="px-5 py-3.5 font-semibold">Qualification</th>
                        <th class="px-5 py-3.5 font-semibold">Hire Date</th>
                        <th class="px-5 py-3.5 font-semibold">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-slate-50/60 transition">

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-teal-100 text-teal-600 font-bold flex items-center justify-center text-sm shrink-0">{{ strtoupper(substr($teacher->first_name, 0, 1)) }}</div>
                                    <a href="{{ route('teachers.show', $teacher) }}" class="font-semibold text-slate-800 hover:text-blue-600 transition">{{ $teacher->first_name }} {{ $teacher->last_name }}</a>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-slate-600">{{ $teacher->email ?? '—' }}</td>

                            <td class="px-5 py-4 text-slate-600">{{ $teacher->phone ?? '—' }}</td>

                            <td class="px-5 py-4">
                                @if($teacher->gender === 'Male')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-sky-50 text-sky-600 font-medium text-xs">Male</span>
                                @elseif($teacher->gender === 'Female')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-pink-50 text-pink-600 font-medium text-xs">Female</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $teacher->dob ? \Carbon\Carbon::parse($teacher->dob)->format('d M Y') : '—' }}
                            </td>

                            <td class="px-5 py-4 text-slate-600">{{ $teacher->qualification ?? '—' }}</td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $teacher->hire_date ? \Carbon\Carbon::parse($teacher->hire_date)->format('d M Y') : '—' }}
                            </td>

                            <td class="px-5 py-4">
                                @if($teacher->status === 'Active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-green-50 text-green-700 font-medium text-xs">Active</span>
                                @elseif($teacher->status === 'Inactive')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-red-50 text-red-700 font-medium text-xs">Inactive</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('teachers.show', $teacher) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" title="View Profile">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @can('manage teachers')
                                    <a href="{{ route('teachers.edit', $teacher) }}" class="p-1.5 rounded-lg text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('teachers.destroy', $teacher) }}" method="POST"
                                          data-swal-confirm
                                          data-swal-title="Delete teacher?"
                                          data-swal-text="Delete {{ $teacher->first_name }} {{ $teacher->last_name }}? This cannot be undone."
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
                            <td colspan="9" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <p class="font-medium text-sm">No teachers found</p>
                                    <p class="text-xs">Try adjusting your search</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
          </div>
        </div>

        @if($teachers->hasPages())
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Showing {{ $teachers->firstItem() }}–{{ $teachers->lastItem() }} of {{ $teachers->total() }} teachers</p>
                <div>{{ $teachers->links() }}</div>
            </div>
        @endif

    </div>

</x-app-layout>
