<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Management KH2') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
    <div class="min-h-screen">
        @include('layouts.navigation')
        @isset($header)
            <header class="bg-white/80 backdrop-blur shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset
        <main>
            {{ $slot }}
        </main>
        <footer class="mt-16 border-t bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">&copy; {{ now()->year }} {{ config('app.name', 'Management KH2') }}</p>
                <div class="text-sm text-slate-500">
                    <a href="{{ url('/') }}" class="hover:text-slate-700">Beranda</a>
                </div>
            </div>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
