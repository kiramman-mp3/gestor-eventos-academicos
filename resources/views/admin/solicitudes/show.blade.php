<x-app-layout :title="$solicitud->titulo">
    <x-admin.nav />
    <x-page-header :title="$solicitud->titulo" :subtitle="$solicitud->tipo.' · '.$solicitud->user->nombre_completo.' · '.$solicitud->created_at->isoFormat('LLL')">
        <x-button size="sm" variant="secondary" :href="route('admin.solicitudes.index')">Volver</x-button>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card space-y-4 p-6 text-sm lg:col-span-2">
            @foreach (['Descripción' => $solicitud->descripcion, 'Justificación' => $solicitud->justificacion, 'Contexto' => $solicitud->contexto] as $titulo => $texto)
                @if ($texto)
                    <div>
                        <h2 class="font-semibold text-zinc-900">{{ $titulo }}</h2>
                        <p class="mt-1 whitespace-pre-line text-zinc-700">{{ $texto }}</p>
                    </div>
                @endif
            @endforeach
            @if ($solicitud->captura_path)
                <a href="{{ route('solicitudes.captura', $solicitud) }}" target="_blank" class="font-medium text-uta-600">Ver captura adjunta</a>
            @endif
        </section>

        <form method="POST" action="{{ route('admin.solicitudes.resolver', $solicitud) }}" class="card h-fit space-y-4 p-6">
            @csrf
            @method('PUT')
            <h2 class="font-semibold text-zinc-900">Resolución</h2>
            <x-field name="prioridad" label="Prioridad" type="select" required :options="\App\Enums\Prioridad::options()" :value="$solicitud->resolucion?->prioridad" />
            <x-field name="estado" label="Estado" type="select" required :options="\App\Enums\EstadoSolicitud::options()" :value="$solicitud->resolucion?->estado" />
            <x-field name="comentario" label="Comentario" type="textarea" rows="4" required :value="$solicitud->resolucion?->comentario" />
            @if ($solicitud->resolucion?->resolutor)
                <p class="text-xs text-zinc-500">Última actualización por {{ $solicitud->resolucion->resolutor->nombre_completo }}, {{ $solicitud->resolucion->updated_at->diffForHumans() }}.</p>
            @endif
            <x-button>Guardar resolución</x-button>
        </form>
    </div>
</x-app-layout>
