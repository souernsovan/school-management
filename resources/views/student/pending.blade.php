<x-student-layout>

    {{-- Auto-check every 10 seconds if account has been linked --}}
    <meta http-equiv="refresh" content="10;url={{ route('student.pending') }}">

    @php
        $user = Auth::user();
        $initial = strtoupper(substr($user->name, 0, 1));
    @endphp

    <div class="min-h-[calc(100vh-3.5rem)] bg-slate-50">
        <div class="mx-auto max-w-[1180px] px-4 py-6 sm:px-6 lg:px-8">
            <div class="w-full rounded-none border-x border-slate-200 bg-slate-50 px-4 py-6 sm:px-6 lg:px-8 xl:px-10">

                <div class="mx-auto max-w-5xl pb-10">
                    {{-- Status chips --}}
                    <div class="flex flex-wrap items-center gap-5 text-sm">
                        <span class="inline-flex items-center gap-2 font-semibold text-blue-700">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                            Pending approval
                        </span>
                        <span class="inline-flex items-center gap-2 text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                            </svg>
                            Refreshing every 10 seconds
                        </span>
                    </div>

                    {{-- Hero --}}
                    <div class="mt-10 flex flex-col items-start">
                        <div class="mx-auto mb-10 flex h-20 w-20 items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 animate-spin text-black" style="animation-duration: 3s" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>

                        <div class="max-w-3xl">
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Account Setup in Progress</h1>
                            <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-[15px]">
                                Your account has been created successfully. The administrator still needs to link your profile before the student portal becomes available.
                            </p>
                        </div>
                    </div>

                    {{-- Quick stats --}}
                    <div class="mt-10 grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Status</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">Waiting for link</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Auto refresh</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">10 second checks</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Access</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">Portal pending</p>
                        </div>
                    </div>

                    {{-- Progress panel --}}
                    <div class="mt-6 rounded-none border border-slate-200 bg-white px-5 py-5 sm:px-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Progress</p>
                                <p class="mt-1 text-sm text-slate-500">Your account activation is in the final review stage.</p>
                            </div>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Almost ready</span>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Account registered</p>
                                    <p class="text-sm text-slate-500">Your login is active and ready for linking.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-500 text-white">
                                    <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-white"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Waiting for admin to link your profile</p>
                                    <p class="text-sm text-slate-500">This is the only remaining step before access is granted.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-400">
                                    <div class="h-2.5 w-2.5 rounded-full bg-slate-400"></div>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-400">Access student portal</p>
                                    <p class="text-sm text-slate-400">Timetable, results, and rankings will appear here once approved.</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- Action --}}
                    <div class="mt-6 flex flex-col items-center gap-3">
                        <a href="{{ route('student.pending') }}"
                           class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Check Again
                        </a>

                        <p class="text-xs text-slate-400">This page checks automatically every 10 seconds</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-student-layout>
