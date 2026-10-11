<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo-uta-escudo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-marino-900 via-marino-800 to-uta-800 font-sans text-zinc-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-6 rounded-xl bg-white/95 px-5 py-3 shadow-lg">
            <img src="{{ asset('images/logo-fisei.png') }}" alt="FISEI - UTA" class="h-14 w-auto">
        </a>

        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            <x-flash />
            {{ $slot }}
        </div>

        <a href="{{ route('home') }}" class="mt-6 text-sm text-white/80 hover:text-white">&larr; Volver al inicio</a>
    </div>
</body>
</html>
