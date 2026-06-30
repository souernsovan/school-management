<x-app-layout>
    <x-slot name="header">Link Student Accounts</x-slot>

    <div class="p-4 sm:p-6 space-y-5">

        @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Explanation --}}
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-700">
                Students who register but are not yet linked to a student record are shown below.
                Select which student record belongs to each user and click <strong>Link</strong>.
            </p>
        </div>



        @if($pendingUsers->isEmpty())
        {{-- All linked --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-14 text-center">
            <div class="mx-auto w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-slate-700 font-semibold text-sm">All student accounts are linked</p>
            <p class="text-slate-400 text-xs mt-1">No pending accounts waiting to be linked.</p>
        </div>

        @else

        

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Pending Accounts</h3>
                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                    {{ $pendingUsers->total() }} waiting
                </span>
            </div>

            <div class="divide-y divide-slate-50">
                @foreach($pendingUsers as $user)
                <div class="px-5 py-4 flex flex-wrap items-center gap-4">

                    {{-- User info --}}
                    <div class="flex items-center gap-3 flex-1 min-w-[200px]">
                        <div class="w-10 h-10 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            <p class="text-[11px] text-slate-300 mt-0.5">Registered {{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-300 shrink-0 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>

                    {{-- Link form --}}
                    <form method="POST" action="{{ route('link-accounts.link', $user) }}"
                          class="flex items-center gap-2 flex-1 min-w-[240px]">
                        @csrf
                        <select name="student_id" required
                                class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select student record…</option>
                            @foreach($unlinkedStudents as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->first_name }} {{ $student->last_name }}
                                @if($student->schoolClass) – {{ $student->schoolClass->name }} @endif
                            </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="shrink-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-blue-600/20">
                            Link
                        </button>
                    </form>

                </div>
                @endforeach
            </div>

            @if($pendingUsers->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Showing {{ $pendingUsers->firstItem() }}–{{ $pendingUsers->lastItem() }} of {{ $pendingUsers->total() }} pending
                </p>
                <div>
                    {{ $pendingUsers->links() }}
                </div>
            </div>
            @endif
        </div>

        @endif

                {{-- Per-page selector --}}
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-slate-500">Show</label>
            <select name="per_page" onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 py-1.5">
                @foreach([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" @selected(request('per_page', 10) == $n)>{{ $n }}</option>
                @endforeach
            </select>
            <span class="text-sm text-slate-500">per page</span>
        </form>

        {{-- Already linked accounts --}}
        @if($linkedUsers->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Linked Accounts</h3>
                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                    {{ $linkedUsers->total() }} linked
                </span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($linkedUsers as $user)
                @php $student = $linkedStudentsMap[$user->email] ?? null; @endphp
                <div class="px-5 py-3.5 flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-3 flex-1 min-w-[200px]">
                        <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700 text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $user->email }}</p>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400 shrink-0 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    @if($student)
                    <div class="flex items-center gap-2 flex-1 min-w-[200px]">
                        <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700 text-sm">{{ $student->first_name }} {{ $student->last_name }}</p>
                            <p class="text-xs text-slate-400">{{ $student->schoolClass?->name }}</p>
                        </div>
                    </div>
                    @endif
                    <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Linked
                    </span>
                </div>
                @endforeach
            </div>

            @if($linkedUsers->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Showing {{ $linkedUsers->firstItem() }}–{{ $linkedUsers->lastItem() }} of {{ $linkedUsers->total() }} linked
                </p>
                <div>
                    {{ $linkedUsers->links() }}
                </div>
            </div>
            @endif
        </div>
        @endif

    </div>
</x-app-layout>
