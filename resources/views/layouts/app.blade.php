@use('App\Models\Configuracion')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ Configuracion::valor('nombre_sitio') }}</title>
    <link rel="icon" href="{{ Configuracion::imagenUrl('favicon') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col overflow-x-clip bg-white font-sans text-tinta antialiased">
    <x-aviso />
    <x-navigation />

    @isset($header)
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main class="flex-1">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <x-flash />
            {{ $slot }}
        </div>
    </main>

    <x-footer />
</body>
</html>
