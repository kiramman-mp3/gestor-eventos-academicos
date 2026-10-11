@props(['evento'])

@php
    $disponibles = $evento->cuposDisponibles();
    $porcentajeOcupado = $evento->cupos > 0 ? min(100, round((($evento->cupos - $disponibles) / $evento->cupos) * 100)) : 100;
@endphp

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col overflow-hidden rounded-2xl bg-white border border-zinc-200/80 shadow-[0_2px_10px_rgba(0,0,0,0.06)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_12px_30px_rgba(0,0,0,0.12)]']) }}>
    <a href="{{ route('eventos.show', $evento) }}" class="relative block aspect-[16/9] overflow-hidden border-b border-zinc-100 bg-gradient-to-br from-uta-700 to-tinta">
        @if ($evento->imagen_url)
            <img src="{{ $evento->imagen_url }}" alt="{{ $evento->nombre }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105" loading="lazy">
        @else
            <div class="flex h-full items-center justify-center p-4 text-center text-lg font-semibold text-white/90 transition-transform duration-500 ease-out group-hover:scale-105">
                {{ $evento->tipoEvento->nombre }}
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
            <span class="rounded-full bg-white/95 px-2.5 py-1 text-xs font-bold text-uta-700 shadow-sm backdrop-blur-xs">
                {{ $evento->tipoEvento->nombre }}
            </span>
            @if ($evento->esGratuito())
                <span class="rounded-full bg-emerald-600/95 px-2.5 py-1 text-xs font-bold text-white shadow-sm backdrop-blur-xs">
                    Gratuito
                </span>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge color="zinc">{{ $evento->modalidad->label() }}</x-badge>
            @if (! $evento->es_publico && $evento->categorias->isNotEmpty())
                <x-badge color="indigo">{{ $evento->categorias->first()->nombre }}</x-badge>
            @endif
        </div>

        <h3 class="mt-3 text-lg font-bold text-zinc-900 group-hover:text-uta-700 transition-colors">
            <a href="{{ route('eventos.show', $evento) }}" class="hover:underline">{{ $evento->nombre }}</a>
        </h3>

        <dl class="mt-3 mb-4 space-y-1.5 text-xs text-zinc-600">
            <div class="flex items-center gap-1.5">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>
                <span>{{ $evento->fecha_inicio->isoFormat('D MMM') }} – {{ $evento->fecha_fin->isoFormat('D MMM YYYY') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
                <span>{{ $evento->horas }} horas académicas</span>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="8" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
                <span class="truncate">{{ \Illuminate\Support\Str::limit($evento->ponentes, 50) }}</span>
            </div>
        </dl>

        <div class="mt-auto border-t border-zinc-100 pt-3.5">
            <div class="mb-2 flex items-center justify-between text-xs">
                @if ($disponibles === 0)
                    <span class="font-bold text-rose-600">Cupos agotados</span>
                @elseif ($disponibles <= 5)
                    <span class="font-semibold text-amber-700">¡Últimos {{ $disponibles }} cupos!</span>
                @else
                    <span class="text-zinc-500 font-medium">{{ $disponibles }} / {{ $evento->cupos }} cupos libres</span>
                @endif
                <span class="font-semibold {{ $evento->esGratuito() ? 'text-emerald-600' : 'text-zinc-800' }}">
                    {{ $evento->esGratuito() ? 'Gratis' : '$'.number_format((float) $evento->costo, 2) }}
                </span>
            </div>
            <div class="mb-3.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                <div class="h-full rounded-full transition-all duration-500 {{ $disponibles === 0 ? 'bg-rose-500' : ($disponibles <= 5 ? 'bg-amber-500' : 'bg-uta-600') }}"
                     style="width: {{ $porcentajeOcupado }}%"></div>
            </div>

            <x-button size="sm" :href="route('eventos.show', $evento)" class="w-full">Ver detalle</x-button>
        </div>
    </div>
</article>
