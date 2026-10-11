@use('App\Enums\TipoContenido')
@use('App\Models\Configuracion')
@use('App\Models\Contenido')

@php($fondo = Contenido::query()->deTipo(TipoContenido::Carrusel)->whereNotNull('imagen_path')->first()?->imagen_url)

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
{{-- Como el login legado: tarjeta blanca centrada sobre una imagen del carrusel a pantalla completa --}}
<body class="relative min-h-screen bg-tinta font-sans text-tinta antialiased">
    @if ($fondo)
        <img src="{{ $fondo }}" alt="" class="fixed inset-0 h-full w-full object-cover">
    @endif
    <div class="fixed inset-0 bg-gradient-to-br from-tinta/80 via-tinta/50 to-uta-800/70"></div>

    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-6 rounded-2xl bg-white/95 px-5 py-3 shadow-lg">
            <img src="{{ Configuracion::imagenUrl('logo') }}" alt="{{ Configuracion::valor('nombre_sitio') }}" class="h-16 w-auto">
        </a>

        <div class="w-full max-w-lg rounded-2xl bg-white/[0.97] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.3)] sm:p-8">
            <x-flash />
            {{ $slot }}
        </div>

        <a href="{{ route('home') }}" class="mt-6 text-sm font-medium text-white/85 hover:text-white">&larr; Volver al inicio</a>
    </div>
</body>
</html>
