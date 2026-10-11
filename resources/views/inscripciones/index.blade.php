<x-app-layout title="Mis inscripciones">
    <x-page-header title="Mis inscripciones" subtitle="Estado de tus inscripciones, pagos y certificados.">
        <x-button variant="secondary" :href="route('eventos.index')">Explorar eventos</x-button>
    </x-page-header>

    @if ($inscripciones->isEmpty())
        <x-empty-state title="Aún no te inscribes en ningún evento.">
            <a href="{{ route('eventos.index') }}" class="font-semibold text-uta-600">Revisa el catálogo</a> para encontrar cursos abiertos.
        </x-empty-state>
    @else
        <div class="space-y-4">
            @foreach ($inscripciones as $inscripcion)
                @php($evento = $inscripcion->evento)
                <article class="card p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-badge color="uta">{{ $evento->tipoEvento->nombre }}</x-badge>
                                <x-badge :color="$inscripcion->estado->color()">{{ $inscripcion->estado->label() }}</x-badge>
                            </div>
                            <h2 class="mt-2 text-lg font-semibold text-zinc-900">
                                <a href="{{ route('eventos.show', $evento) }}" class="hover:text-uta-700">{{ $evento->nombre }}</a>
                            </h2>
                            <p class="text-sm text-zinc-500">
                                {{ $evento->fecha_inicio->isoFormat('LL') }} al {{ $evento->fecha_fin->isoFormat('LL') }} · {{ $evento->horas }} horas
                            </p>

                            <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm">
                                @if ($evento->requiere_nota)
                                    <div><dt class="inline text-zinc-500">Nota:</dt> <dd class="inline font-medium">{{ $inscripcion->nota ?? 'Pendiente' }}</dd></div>
                                @endif
                                @if ($evento->requiere_asistencia)
                                    <div><dt class="inline text-zinc-500">Asistencia:</dt> <dd class="inline font-medium">{{ $inscripcion->asistencia !== null ? $inscripcion->asistencia.'%' : 'Pendiente' }}</dd></div>
                                @endif
                            </dl>

                            @if ($inscripcion->comprobanteVigente?->estado === \App\Enums\EstadoComprobante::Rechazado)
                                <p class="mt-3 rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                    Tu último comprobante fue rechazado: {{ $inscripcion->comprobanteVigente->motivo_rechazo }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col items-start gap-2 lg:items-end">
                            @can('subirComprobante', $inscripcion)
                                <form method="POST" action="{{ route('inscripciones.comprobante', $inscripcion) }}" enctype="multipart/form-data"
                                      class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <input type="file" name="comprobante" required accept=".pdf,.jpg,.jpeg,.png"
                                           class="block w-56 text-xs text-zinc-600 file:mr-2 file:rounded-md file:border-0 file:bg-zinc-100 file:px-2 file:py-1 file:text-xs file:font-semibold">
                                    <x-button size="sm">Subir comprobante</x-button>
                                </form>
                                <x-input-error :messages="$errors->get('comprobante')" />
                            @endcan

                            @if ($inscripcion->comprobanteVigente)
                                <x-button size="sm" variant="secondary" :href="route('comprobantes.show', $inscripcion->comprobanteVigente)" target="_blank" rel="noopener">
                                    Ver comprobante
                                </x-button>
                            @endif

                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
