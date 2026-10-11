@php
    $equipo = [
        ['nombre' => 'Pablo Vayas', 'foto' => 'pablo.png', 'perfil' => 'Estudiante de Software, especializado en frontend y bases de datos.'],
        ['nombre' => 'Alexis López', 'foto' => 'alexis.jpg', 'perfil' => 'Apasionado por la programación web y el diseño visual interactivo.'],
        ['nombre' => 'José Manzano', 'foto' => 'jose.jpg', 'perfil' => 'Interesado en computación gráfica y desarrollo de videojuegos.'],
        ['nombre' => 'Johan Rodríguez', 'foto' => 'johan.jpg', 'perfil' => 'Experiencia en simulaciones gráficas y sistemas interactivos.'],
        ['nombre' => 'Alan Puruncajas', 'foto' => 'alan.jpg', 'perfil' => 'Apasionado por el diseño gráfico y la realidad aumentada.'],
    ];
@endphp

<x-app-layout title="Créditos">
    <x-page-header title="Créditos" subtitle="Equipo de desarrollo · Carrera de Software, Universidad Técnica de Ambato" />

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($equipo as $persona)
            <div class="card overflow-hidden text-center">
                <img src="{{ asset('images/equipo/'.$persona['foto']) }}" alt="{{ $persona['nombre'] }}" class="aspect-square w-full object-cover" loading="lazy">
                <div class="p-4">
                    <h2 class="font-semibold text-zinc-900">{{ $persona['nombre'] }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ $persona['perfil'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
