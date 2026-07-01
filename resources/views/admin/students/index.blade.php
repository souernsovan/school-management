<x-app-layout>

    <x-slot name="header">
        Students Management
    </x-slot>

    @php
        $linkedEmails = \App\Models\User::role('Student')->pluck('email')->toArray();
    @endphp

    {{-- Link-to-User modal --}}
    <div x-data="{ open: false, studentId: null, studentName: '', currentEmail: '' }"
         @open-link-modal.window="open = true; studentId = $event.detail.id; studentName = $event.detail.name; currentEmail = $event.detail.email"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>

        {{-- Panel --}}
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4" @click.stop>

            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Link to User Account</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Student: ' + studentName"></p>
                </div>
                <button @click="open = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" :action="'/link-accounts/student/' + studentId + '/link'">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Select User Account</label>
                        <select name="user_id" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Choose a user…</option>
                            @foreach($studentUsers as $u)
                            <option value="{{ $u->id }}"
                                    :selected="currentEmail === '{{ $u->email }}'">
                                {{ $u->name }} — {{ $u->email }}
                                @if(in_array($u->email, $linkedEmails) && \App\Models\Student::where('email', $u->email)->exists())
                                ✓ linked
                                @endif
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">The student's email will be updated to match the selected user.</p>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" @click="open = false"
                                class="flex-1 px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-sm">
                            Link Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="p-4 sm:p-6 space-y-5">

        <!-- Top Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Students</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $students->total() }} student{{ $students->total() !== 1 ? 's' : '' }} found</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('manage students')
                {{-- Export popover --}}
                <div x-data="{ open: false, cls: '' }" class="relative">
                    <button @click="open = !open" @keydown.escape.window="open = false"
                            class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2.5 rounded-xl hover:bg-emerald-100 transition font-medium text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                        Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute left-0 top-full mt-2 z-30 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 space-y-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Export Students</p>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Select Class</label>
                            <select x-model="cls" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}{{ $class->section ? ' — '.$class->section : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a :href="cls ? '{{ route('students.export') }}?class_id=' + cls : '{{ route('students.export') }}'"
                           @click="open = false"
                           class="flex items-center justify-center gap-2 w-full bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-emerald-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                            Download Excel
                        </a>
                    </div>
                </div>
                {{-- Import trigger --}}
                <button type="button" @click="$dispatch('open-import')"
                        class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 border border-amber-200 px-4 py-2.5 rounded-xl hover:bg-amber-100 transition font-medium text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 6l5-5 5 5M12 1v14"/></svg>
                    Import CSV
                </button>
                @endcan
                @canany(['manage students', 'create students'])
                <a href="{{ route('students.create') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Student
                </a>
                @endcanany
            </div>
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

        <!-- Filter Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <form method="GET" action="{{ route('students.index') }}" class="flex flex-wrap items-center gap-3">

                <!-- Search -->
                <div class="relative flex-1 max-w-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by name or email..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50">
                </div>

                <!-- Class Filter -->
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    <select name="class_id"
                            class="pl-9 pr-8 py-2 rounded-xl border border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50 appearance-none">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} — {{ $class->section }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition font-medium shadow-sm">
                    Search
                </button>

                @if(request('search') || request('class_id'))
                    <a href="{{ route('students.index') }}"
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

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">

                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-xs tracking-wide">
                        <th class="px-5 py-3.5 font-semibold">Student</th>
                        <th class="px-5 py-3.5 font-semibold">Email</th>
                        <th class="px-5 py-3.5 font-semibold">Class</th>
                        <th class="px-5 py-3.5 font-semibold">Section</th>
                        <th class="px-5 py-3.5 font-semibold">Date of Birth</th>
                        <th class="px-5 py-3.5 font-semibold">Gender</th>
                        <th class="px-5 py-3.5 font-semibold">Phone</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">

                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/60 transition">

                            <!-- Name -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-sm shrink-0">
                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </span>
                                </div>
                            </td>

                            <!-- Email + link status -->
                            <td class="px-5 py-4">
                                <div class="text-slate-600 text-sm">{{ $student->email ?? '—' }}</div>
                                @if($student->email && in_array($student->email, $linkedEmails))
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Linked
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-500 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Not linked
                                </span>
                                @endif
                            </td>

                            <!-- Class -->
                            <td class="px-5 py-4">
                                @if($student->schoolClass)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 font-semibold text-xs">
                                        {{ $student->schoolClass->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Section -->
                            <td class="px-5 py-4">
                                @if($student->schoolClass)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-semibold text-xs">
                                        {{ $student->schoolClass->section }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- DOB -->
                            <td class="px-5 py-4 text-slate-600">
                                {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M Y') : '—' }}
                            </td>

                            <!-- Gender -->
                            <td class="px-5 py-4">
                                @if($student->gender === 'Male')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-sky-50 text-sky-600 font-medium text-xs">Male</span>
                                @elseif($student->gender === 'Female')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-pink-50 text-pink-600 font-medium text-xs">Female</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Phone -->
                            <td class="px-5 py-4 text-slate-600">
                                {{ $student->phone ?? '—' }}
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">

                                    @can('manage students')
                                    <button type="button"
                                            title="Link to user account"
                                            @click="$dispatch('open-link-modal', { id: {{ $student->id }}, name: '{{ addslashes($student->first_name . ' ' . $student->last_name) }}', email: '{{ $student->email }}' })"
                                            class="p-1.5 rounded-lg transition
                                                {{ ($student->email && in_array($student->email, $linkedEmails)) ? 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50' : 'text-amber-400 hover:text-amber-600 hover:bg-amber-50' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                    </button>
                                    @endcan

                                    <a href="{{ route('students.show', $student->id) }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @can('manage students')
                                    <a href="{{ route('students.edit', $student->id) }}"
                                       class="p-1.5 rounded-lg text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('students.destroy', $student->id) }}"
                                          data-swal-confirm
                                          data-swal-title="Delete student?"
                                          data-swal-text="Delete {{ $student->first_name }}? This cannot be undone."
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
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a4 4 0 00-5.916-3.519M9 20H4v-2a4 4 0 015.916-3.519M15 7a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zm-18 0a3 3 0 116 0 3 3 0 01-6 0z"/>
                                    </svg>
                                    <p class="font-medium text-sm">No students found</p>
                                    <p class="text-xs">Try adjusting your search or filter</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
          </div>
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }} students
                </p>
                <div>
                    {{ $students->links() }}
                </div>
            </div>
        @endif

    </div>

    {{-- Import CSV Modal --}}
    @can('manage students')
    <div x-data="{ open: false }" @open-import.window="open = true">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
             @keydown.escape.window="open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4" @click.outside="open = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Import Students from CSV</h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-sm text-slate-500">Upload a <strong>.csv</strong> file. The first row must be a header. Columns (in order):</p>
                <div class="bg-slate-50 rounded-xl p-3 text-xs font-mono text-slate-600 overflow-x-auto">
                    First Name, Last Name, Email, Date of Birth, Gender, Phone, Address, Class
                </div>
                <ul class="text-xs text-slate-500 space-y-1 list-disc list-inside">
                    <li>First Name and Last Name are required</li>
                    <li>Rows with duplicate emails are skipped</li>
                    <li>Class must match an existing class name exactly</li>
                    <li>Date of Birth format: YYYY-MM-DD</li>
                </ul>
                <form method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1">
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

</x-app-layout>
