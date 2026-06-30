<div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="flex h-screen bg-slate-100">

    <!-- MOBILE BACKDROP -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" x-cloak></div>

    <!-- SIDEBAR -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col
                  transform transition-transform duration-300 ease-in-out
                  lg:static lg:translate-x-0 lg:z-auto">

        <!-- Logo -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-8 rounded-lg">
                <div>
                    <div class="font-bold text-slate-800 text-sm leading-tight">Admin Panel</div>
                    <div class="text-xs text-slate-400">Management System</div>
                </div>
            </div>
            <!-- Close (mobile only) -->
            <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        @php
            // Top-level item styles
            $link = fn($active) => $active
                ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors';

            // Group toggle button styles (active = a child route is open)
            $group = fn($active) => $active
                ? 'flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-semibold text-blue-700 bg-blue-50'
                : 'flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors';

            // Sub-link styles (indented)
            $sub = fn($active) => $active
                ? 'flex items-center gap-3 pl-4 pr-3 py-2 rounded-lg text-sm font-semibold text-blue-700 bg-blue-50'
                : 'flex items-center gap-3 pl-4 pr-3 py-2 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors';

            // Pre-compute which groups should start open
            $academicActive    = request()->routeIs('students.*', 'school-classes.*', 'subjects.*', 'teachers.*', 'exam-types.*');
            $managementActive  = request()->routeIs('timetables.*', 'attendances.*', 'exams.*', 'reports.*', 'student.results', 'student-results.*', 'reports.rankings');
            $adminActive       = request()->routeIs('users.*', 'link-accounts.*');
        @endphp

        <!-- MENU -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="{{ $link(request()->routeIs('dashboard')) }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <!-- ===== ACADEMIC GROUP ===== -->
            @canany(['manage students','view students','manage classes','view classes','manage subjects','view subjects','manage teachers','view teachers'])
            <div x-data="{ open: @json($academicActive) }" class="pt-1">

                <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Academic</p>

                <button type="button" @click="open = !open" :aria-expanded="open"
                    class="{{ $group($academicActive) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z"/>
                    </svg>
                    <span class="flex-1 text-left">Academic</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-collapse
                     class="mt-1 ml-4 pl-3 border-l border-slate-200 space-y-0.5">

                    @canany(['manage students', 'view students', 'create students'])
                    <a href="{{ route('students.index') }}" class="{{ $sub(request()->routeIs('students.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                        Students
                    </a>
                    @endcanany

                    @canany(['manage classes', 'view classes', 'create classes'])
                    <a href="{{ route('school-classes.index') }}" class="{{ $sub(request()->routeIs('school-classes.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Classes
                    </a>
                    @endcanany

                    @canany(['manage subjects', 'view subjects', 'create subjects'])
                    <a href="{{ route('subjects.index') }}" class="{{ $sub(request()->routeIs('subjects.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Subjects
                    </a>
                    @endcanany

                    @canany(['manage teachers', 'view teachers'])
                    <a href="{{ route('teachers.index') }}" class="{{ $sub(request()->routeIs('teachers.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5.916-3.519M9 20H4v-2a4 4 0 015.916-3.519M15 7a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zm-18 0a3 3 0 116 0 3 3 0 01-6 0z"/>
                        </svg>
                        Teachers
                    </a>
                    @endcanany

                    @can('manage exams')
                    <a href="{{ route('exam-types.index') }}" class="{{ $sub(request()->routeIs('exam-types.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Exam Types
                    </a>
                    @endcan

                </div>
            </div>
            @endcanany

            <!-- ===== MANAGEMENT GROUP ===== -->
            @canany(['manage timetables','view timetable','manage attendance','manage exams','view results'])
            <div x-data="{ open: @json($managementActive) }" class="pt-1">

                <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Management</p>

                <button type="button" @click="open = !open" :aria-expanded="open"
                    class="{{ $group($managementActive) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="flex-1 text-left">Management</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-collapse
                     class="mt-1 ml-4 pl-3 border-l border-slate-200 space-y-0.5">

                    @canany(['manage timetables', 'view timetable'])
                    <a href="{{ route('timetables.index') }}" class="{{ $sub(request()->routeIs('timetables.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 11h14M5 19h14M5 15h14"/>
                        </svg>
                        Time Table
                    </a>
                    @endcanany

                    @can('manage attendance')
                    <a href="{{ route('attendances.index') }}" class="{{ $sub(request()->routeIs('attendances.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Attendance
                    </a>
                    @endcan

                    @canany(['manage exams', 'view results'])
                    @unless(auth()->user()->hasRole('Student'))
                    <a href="{{ route('student-results.index') }}" class="{{ $sub(request()->routeIs('student-results.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Student Results
                    </a>
                    @endunless
                    @endcanany

                    @can('manage exams')
                    <a href="{{ route('exams.index') }}" class="{{ $sub(request()->routeIs('exams.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Exams
                    </a>
                    <a href="{{ route('reports.index') }}" class="{{ $sub(request()->routeIs('reports.index') || request()->routeIs('reports.export.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Reports
                    </a>
                    <a href="{{ route('reports.rankings') }}" class="{{ $sub(request()->routeIs('reports.rankings')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Rankings
                    </a>
                    @endcan
                    @role('Student')
                    <a href="{{ route('student.results') }}" class="{{ $sub(request()->routeIs('student.results')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        My Results
                    </a>
                    @endrole

                </div>
            </div>
            @endcanany

            <!-- ===== ADMINISTRATION GROUP ===== -->
            @can('manage users')
            <div x-data="{ open: @json($adminActive) }" class="pt-1">

                <p class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Administration</p>

                <button type="button" @click="open = !open" :aria-expanded="open"
                    class="{{ $group($adminActive) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="flex-1 text-left">Administration</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-collapse
                     class="mt-1 ml-4 pl-3 border-l border-slate-200 space-y-0.5">

                    <a href="{{ route('users.index') }}" class="{{ $sub(request()->routeIs('users.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5.916-3.519M9 20H4v-2a4 4 0 015.916-3.519M15 7a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zm-18 0a3 3 0 116 0 3 3 0 01-6 0z"/>
                        </svg>
                        Users
                    </a>

                    @can('manage students')
                    <a href="{{ route('link-accounts.index') }}" class="{{ $sub(request()->routeIs('link-accounts.*')) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Link Accounts
                        @php
                            $pendingCount = \App\Models\User::role('Student')->get()
                                ->filter(fn($u) => !\App\Models\Student::where('email', $u->email)->exists())
                                ->count();
                        @endphp
                        @if($pendingCount > 0)
                        <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center shrink-0">
                            {{ $pendingCount }}
                        </span>
                        @endif
                    </a>
                    @endcan

                </div>
            </div>
            @endcan

        </nav>

        <!-- Bottom user info -->
        <div class="p-3 border-t border-slate-100 shrink-0">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-50">
                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>
        </div>

    </aside>

    <!-- MAIN AREA -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- HEADER -->
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 shrink-0">

            <div class="flex items-center gap-3 min-w-0">
                <!-- Hamburger (mobile only) -->
                <button type="button" @click="sidebarOpen = true" class="lg:hidden p-2 -ml-1 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <h1 class="text-lg font-bold text-slate-800 truncate">
                    {{ $header }}
                </h1>
            </div>

            <!-- NOTIFICATIONS + PROFILE -->
            <div class="flex items-center gap-2">

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                    @php
                        $user = Auth::user();
                        $unreadCount = $user->unreadNotifications()->count();
                        $recentNotifications = $user->notifications()->latest()->take(5)->get();
                    @endphp

                    <button @click="open = !open"
                            class="relative flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.157V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.157c0 .538-.214 1.055-.595 1.438L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         x-cloak
                         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-lg border border-slate-100 py-2 z-50">

                        <div class="px-4 py-3 border-b border-slate-100">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Notifications</p>
                                    <p class="text-xs text-slate-400">{{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }}</p>
                                </div>
                                <a href="{{ route('notifications.index') }}" class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 transition">
                                    View all
                                </a>
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            @forelse($recentNotifications as $notification)
                                @php
                                    $data = $notification->data ?? [];
                                    $isUnread = is_null($notification->read_at);
                                @endphp
                                <a href="{{ route('notifications.read', $notification->id) }}"
                                   class="block px-4 py-3 hover:bg-slate-50 transition {{ $isUnread ? 'bg-blue-50/40' : '' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isUnread ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.157V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.157c0 .538-.214 1.055-.595 1.438L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="truncate text-sm font-semibold text-slate-800">{{ $data['title'] ?? 'Notification' }}</p>
                                                @if($isUnread)
                                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700">New</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $data['message'] ?? '' }}</p>
                                            <p class="mt-1.5 text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.157V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.157c0 .538-.214 1.055-.595 1.438L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-700">You’re all caught up</p>
                                    <p class="mt-1 text-xs text-slate-400">Notifications will appear here when events happen.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">

                    <button @click="open = !open"
                            class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-xl hover:bg-slate-100 transition select-none">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-slate-700">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown panel -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         x-cloak
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-lg border border-slate-100 py-1.5 z-50">

                        <!-- User info header -->
                        <div class="px-4 py-3 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Edit profile -->
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Profile
                        </a>

                        <div class="border-t border-slate-100 my-1"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </header>

        <!-- CONTENT -->
        <main class="flex-1 overflow-y-auto">
            {{ $slot }}
        </main>

    </div>

</div>
