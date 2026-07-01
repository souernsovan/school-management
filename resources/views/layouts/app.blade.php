<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased h-full w-full">
        @include('layouts.navigation')

        <header class="bg-white border-b border-slate-200 px-4 py-4 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Laravel') }} logo" class="h-10 w-10 rounded-lg object-cover">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900">{{ config('app.name', 'Laravel') }}</h1>
                    <p class="text-sm text-slate-500">School management dashboard</p>
                </div>
            </div>
        </header>

        @include('layouts.flash-alerts')
    </body>
</html>
