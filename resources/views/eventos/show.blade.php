<x-app-layout :title="$evento->nombre">
    <div class="grid gap-8 lg:grid-cols-3">
        <article class="lg:col-span-2">
            <div class="card overflow-hidden">
                @if ($evento->imagen_url)
                    <img src="{{ $evento->imagen_url }}" alt="{{ $evento->nombre }}" class="max-h-96 w-full object-cover">
                @endif

                <div class="p-6">
                    <div class="flex flex-wrap gap-2">
                        <x-badge color="uta">{{ $evento->tipoEvento->nombre }}</x-badge>
                        <x-badge>{{ $evento->modalidad->label() }}</x-badge>
                        <x-badge :color="$evento->estado->color()">{{ $evento->estado->label() }}</x-badge>
                        @if ($evento->es_publico)
                            <x-badge color="sky">Abierto al público</x-badge>
                        @endif
                        @foreach ($evento->categorias as $categoria)
                            <x-badge color="indigo">{{ $categoria->nombre }}</x-badge>
                        @endforeach
                    </div>

                    <h1 class="mt-4 text-3xl font-bold text-zinc-900">{{ $evento->nombre }}</h1>

                    @if ($evento->descripcion)
                        <p class="mt-4 leading-relaxed whitespace-pre-line text-zinc-600">{{ $evento->descripcion }}</p>
                    @endif

                    <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="font-semibold text-zinc-800">Ponentes</dt><dd class="text-zinc-600">{{ $evento->ponentes }}</dd></div>
                        <div><dt class="font-semibold text-zinc-800">Duración</dt><dd class="text-zinc-600">{{ $evento->horas }} horas académicas</dd></div>
                        <div><dt class="font-semibold text-zinc-800">Fechas del evento</dt><dd class="text-zinc-600">{{ $evento->fecha_inicio->isoFormat('LL') }} al {{ $evento->fecha_fin->isoFormat('LL') }}</dd></div>
                        <div><dt class="font-semibold text-zinc-800">Inscripciones</dt><dd class="text-zinc-600">{{ $evento->fecha_inicio_inscripciones->isoFormat('LL') }} al {{ $evento->fecha_fin_inscripciones->isoFormat('LL') }}</dd></div>
                        <div><dt class="font-semibold text-zinc-800">Costo</dt><dd class="text-zinc-600">{{ $evento->esGratuito() ? 'Gratuito' : '$'.number_format((float) $evento->costo, 2) }}</dd></div>
                        <div><dt class="font-semibold text-zinc-800">Cupos disponibles</dt><dd class="text-zinc-600">{{ $cuposDisponibles }} de {{ $evento->cupos }}</dd></div>
                        @if ($evento->requiere_nota)
                            <div><dt class="font-semibold text-zinc-800">Nota mínima para certificar</dt><dd class="text-zinc-600">{{ $evento->nota_minima }} / 10</dd></div>
                        @endif
                        @if ($evento->requiere_asistencia)
                            <div><dt class="font-semibold text-zinc-800">Asistencia mínima</dt><dd class="text-zinc-600">{{ $evento->asistencia_minima }}%</dd></div>
                        @endif
                    </dl>
                </div>
            </div>
        </article>

        <aside class="space-y-6">
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-zinc-900">Requisitos</h2>
                @if ($evento->requisitos->isEmpty())
                    <p class="mt-2 text-sm text-zinc-500">Este evento no tiene requisitos adicionales.</p>
                @else
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($evento->requisitos as $requisito)
                            <li class="flex items-start gap-2">
                                @auth
                                    @if ($requisito->cumplidoPor(auth()->user()) && $requisito->tipo === \App\Enums\TipoRequisito::Documento)
                                        <span class="text-emerald-600" aria-label="Cumplido">&#10003;</span>
                                    @else
                                        <span class="text-zinc-400" aria-hidden="true">&bull;</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400" aria-hidden="true">&bull;</span>
                                @endauth
                                <span>{{ $requisito->descripcion }}
                                    <span class="text-xs text-zinc-500">({{ $requisito->tipo_documento?->label() ?? $requisito->tipo->label() }})</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @include('eventos.partials.inscripcion')
        </aside>
    </div>
</x-app-layout>
