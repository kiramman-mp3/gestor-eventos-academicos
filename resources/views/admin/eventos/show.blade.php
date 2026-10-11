@use('App\Enums\EstadoEvento')

@php
    // Acciones de estado disponibles según el estado actual: [estado destino => etiqueta]
    $transiciones = match ($evento->estado) {
        EstadoEvento::Borrador => [EstadoEvento::Abierto->value => 'Publicar', EstadoEvento::Cancelado->value => 'Cancelar'],
        EstadoEvento::Abierto => [EstadoEvento::EnEjecucion->value => 'Marcar en ejecución', EstadoEvento::Borrador->value => 'Volver a borrador', EstadoEvento::Cancelado->value => 'Cancelar'],
        EstadoEvento::EnEjecucion => [EstadoEvento::Finalizado->value => 'Finalizar evento'],
        default => [],
    };
@endphp

<x-app-layout :title="$evento->nombre">
    <x-admin.nav />

    <x-page-header :title="$evento->nombre" :subtitle="$evento->tipoEvento->nombre.' · '.$evento->fecha_inicio->isoFormat('LL').' al '.$evento->fecha_fin->isoFormat('LL')">
        <x-badge :color="$evento->estado->color()">{{ $evento->estado->label() }}</x-badge>
        @if ($evento->esEditable())
            <x-button size="sm" variant="secondary" :href="route('admin.eventos.edit', $evento)">Editar</x-button>
        @endif
        @if ($evento->requiere_nota || $evento->requiere_asistencia)
            <x-button size="sm" variant="secondary" :href="route('gestion.notas.edit', $evento)">Notas y asistencia</x-button>
        @endif
        <x-button size="sm" variant="secondary" :href="route('admin.eventos.reporte', $evento)" target="_blank">Reporte PDF</x-button>
        @foreach ($transiciones as $destino => $etiqueta)
            <form method="POST" action="{{ route('admin.eventos.estado', $evento) }}"
                  @if ($destino === EstadoEvento::Cancelado->value) onsubmit="return confirm('¿Cancelar este evento?')" @endif>
                @csrf
                @method('PATCH')
                <input type="hidden" name="estado" value="{{ $destino }}">
                <x-button size="sm" :variant="$destino === EstadoEvento::Cancelado->value ? 'danger' : 'dark'">{{ $etiqueta }}</x-button>
            </form>
        @endforeach
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-zinc-500">Inscritos activos</p><p class="text-2xl font-bold">{{ $evento->cupos - $cuposDisponibles }} / {{ $evento->cupos }}</p></div>
        <div class="card p-4"><p class="text-xs text-zinc-500">Costo</p><p class="text-2xl font-bold">{{ $evento->esGratuito() ? 'Gratuito' : '$'.number_format((float) $evento->costo, 2) }}</p></div>
        <div class="card p-4"><p class="text-xs text-zinc-500">Docente</p><p class="font-semibold">{{ $evento->docente?->nombre_completo ?? 'Sin asignar' }}</p></div>
        <div class="card p-4"><p class="text-xs text-zinc-500">Destinatarios</p><p class="text-sm font-semibold">{{ $evento->es_publico ? 'Público general' : '' }} {{ $evento->categorias->pluck('nombre')->join(', ') }}</p></div>
    </div>

    <section class="card overflow-hidden">
        <h2 class="border-b border-zinc-100 px-5 py-4 font-semibold text-zinc-900">Inscritos</h2>
        @if ($inscripciones->isEmpty())
            <p class="px-5 py-6 text-sm text-zinc-500">Aún no hay inscripciones.</p>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr><th>Estudiante</th><th>Estado</th><th>Nota</th><th>Asistencia</th><th>Documentos</th><th>Respuestas</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($inscripciones as $inscripcion)
                            <tr>
                                <td>
                                    <p class="font-medium text-zinc-900">{{ $inscripcion->user->apellido }} {{ $inscripcion->user->nombre }}</p>
                                    <p class="text-xs text-zinc-500">{{ $inscripcion->user->cedula }} · {{ $inscripcion->user->email }}</p>
                                </td>
                                <td>
                                    <x-badge :color="$inscripcion->estado->color()">{{ $inscripcion->estado->label() }}</x-badge>
                                    @if ($inscripcion->comprobanteVigente)
                                        <a href="{{ route('comprobantes.show', $inscripcion->comprobanteVigente) }}" target="_blank" class="mt-1 block text-xs text-uta-600">Comprobante</a>
                                    @endif
                                </td>
                                <td>{{ $inscripcion->nota ?? '—' }}</td>
                                <td>{{ $inscripcion->asistencia !== null ? $inscripcion->asistencia.'%' : '—' }}</td>
                                <td class="text-xs">
                                    @forelse ($inscripcion->user->documentos as $documento)
                                        <a href="{{ route('documentos.show', $documento) }}" target="_blank" class="block text-uta-600 hover:text-uta-700">{{ $documento->tipo->label() }}</a>
                                    @empty
                                        <span class="text-zinc-400">—</span>
                                    @endforelse
                                </td>
                                <td class="max-w-xs text-xs text-zinc-600">
                                    @foreach ($inscripcion->respuestas as $respuesta)
                                        <p><span class="font-medium">{{ $respuesta->requisito->descripcion }}:</span> {{ \Illuminate\Support\Str::limit($respuesta->texto, 120) }}</p>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($evento->inscripciones_count === 0)
        <form method="POST" action="{{ route('admin.eventos.destroy', $evento) }}" class="mt-6 text-right" onsubmit="return confirm('¿Eliminar definitivamente este evento?')">
            @csrf
            @method('DELETE')
            <x-button variant="ghost" class="text-rose-600">Eliminar evento</x-button>
        </form>
    @endif
</x-app-layout>
