<div class="card p-6">
    @guest
        <p class="text-sm text-zinc-600">Inicia sesión como estudiante para inscribirte.</p>
        <div class="mt-4 flex gap-2">
            <x-button :href="route('login')">Iniciar sesión</x-button>
            <x-button variant="secondary" :href="route('register')">Registrarme</x-button>
        </div>
    @else
        @if ($inscripcion)
            <p class="text-sm text-zinc-600">Ya estás inscrito en este evento.</p>
            <x-badge class="mt-2" :color="$inscripcion->estado->color()">{{ $inscripcion->estado->label() }}</x-badge>
        @elseif (! $evento->inscripcionAbierta())
            <p class="text-sm text-zinc-600">Las inscripciones no están abiertas para este evento.</p>
        @elseif ($cuposDisponibles === 0)
            <p class="text-sm font-medium text-rose-600">No quedan cupos disponibles.</p>
        @else
            <p class="text-sm text-zinc-600">Las inscripciones están abiertas.</p>
        @endif
    @endguest
</div>
