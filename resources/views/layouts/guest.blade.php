<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'School Management') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">

<div class="min-h-screen flex">

    {{-- ===== LEFT PANEL — Brand ===== --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 flex-col">

        {{-- Decorative circles --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/5 rounded-full"></div>
        <div class="absolute top-1/3 -right-16 w-72 h-72 bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-20 left-1/3 w-80 h-80 bg-indigo-500/30 rounded-full"></div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col h-full px-14 py-14">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="w-7 h-7 rounded-lg object-contain">
                </div>
                <span class="text-white font-bold text-lg tracking-tight">{{ config('app.name', 'School Management') }}</span>
            </div>

            {{-- Hero --}}
            <div class="flex-1 flex flex-col justify-center">
                <h2 class="text-4xl xl:text-5xl font-bold text-white leading-tight">
                    Manage your school<br>
                    <span class="text-blue-200">smarter & faster</span>
                </h2>
                <p class="mt-4 text-blue-100 text-lg leading-relaxed max-w-md">
                    A complete platform for managing students, teachers, classes, attendance, timetables and exam results — all in one place.
                </p>

                {{-- Feature list --}}
                <div class="mt-10 space-y-4">
                    @foreach([
                        ['Students & Teachers', 'Manage all your staff and student records with ease'],
                        ['Attendance Tracking', 'Take and review attendance in seconds'],
                        ['Exam & Results',      'Create exams, enter marks, and track grades'],
                        ['Smart Timetable',     'Visual weekly schedule for every class'],
                    ] as [$title, $desc])
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ $title }}</p>
                            <p class="text-blue-200 text-xs mt-0.5">{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <p class="text-blue-300 text-xs">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>

    {{-- ===== RIGHT PANEL — Form ===== --}}
    <div class="w-full lg:w-1/2 xl:w-2/5 flex flex-col items-center justify-center px-6 py-12 sm:px-12">

        {{-- Mobile logo --}}
        <div class="lg:hidden flex items-center gap-3 mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="" class="w-10 h-10 rounded-xl">
            <span class="font-bold text-slate-800 text-lg">{{ config('app.name') }}</span>
        </div>

        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

</div>

</body>
</html>
