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

                            @can('descargar-certificado', $inscripcion)
                                <x-button size="sm" variant="success" :href="route('certificados.descargar', $inscripcion)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Descargar certificado
                                </x-button>
                            @endcan

                            @if ($inscripcion->comprobanteVigente)
                                <x-button size="sm" variant="secondary" :href="route('comprobantes.show', $inscripcion->comprobanteVigente)" target="_blank" rel="noopener">
                                    <svg class="h-4 w-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
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
