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
            <div class="mt-4">
                <x-button variant="secondary" :href="route('inscripciones.index')">Ver mis inscripciones</x-button>
            </div>
        @elseif ($cuposDisponibles === 0)
            <p class="text-sm font-medium text-rose-600">No quedan cupos disponibles.</p>
        @elseif (auth()->user()->can('inscribirse', $evento))
            @php($pendientes = $evento->requisitos->reject(fn ($r) => $r->cumplidoPor(auth()->user())))

            <h2 class="text-lg font-semibold text-zinc-900">Inscribirme</h2>

            @if ($pendientes->isNotEmpty())
                <p class="mt-2 text-sm text-amber-700">Antes de inscribirte, sube estos documentos en tu perfil:</p>
                <ul class="mt-2 list-inside list-disc text-sm text-zinc-700">
                    @foreach ($pendientes as $requisito)
                        <li>{{ $requisito->tipo_documento->label() }}</li>
                    @endforeach
                </ul>
                <x-button class="mt-4" variant="secondary" :href="route('perfil.edit')">Ir a mi perfil</x-button>
            @else
                <form method="POST" action="{{ route('inscripciones.store', $evento) }}" class="mt-4 space-y-4">
                    @csrf
                    @foreach ($evento->requisitos->where('tipo', \App\Enums\TipoRequisito::Texto) as $requisito)
                        <x-field :name="'respuestas['.$requisito->id.']'" :label="$requisito->descripcion" type="textarea" rows="3" required />
                    @endforeach

                    <p class="text-xs text-zinc-500">
                        @if ($evento->esGratuito())
                            Evento gratuito: tu cupo se confirma al inscribirte.
                        @else
                            Costo: ${{ number_format((float) $evento->costo, 2) }}. Tu cupo queda reservado hasta que subas el comprobante de pago.
                        @endif
                    </p>
                    <x-button class="w-full">Confirmar inscripción</x-button>
                </form>
            @endif
        @elseif (! $evento->inscripcionAbierta())
            <p class="text-sm text-zinc-600">Las inscripciones no están abiertas para este evento.</p>
        @else
            <p class="text-sm text-zinc-600">Solo los estudiantes pueden inscribirse en eventos.</p>
        @endif
    @endguest
</div>
