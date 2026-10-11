@props(['evento'])

@php($disponibles = $evento->cuposDisponibles())

<article {{ $attributes->merge(['class' => 'card flex flex-col overflow-hidden transition hover:shadow-md']) }}>
    <a href="{{ route('eventos.show', $evento) }}" class="block aspect-[16/9] bg-gradient-to-br from-marino-800 to-uta-700">
        @if ($evento->imagen_url)
            <img src="{{ $evento->imagen_url }}" alt="{{ $evento->nombre }}" class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="flex h-full items-center justify-center p-4 text-center text-lg font-semibold text-white/90">{{ $evento->tipoEvento->nombre }}</div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge color="uta">{{ $evento->tipoEvento->nombre }}</x-badge>
            <x-badge>{{ $evento->modalidad->label() }}</x-badge>
            @if ($evento->esGratuito())
                <x-badge color="emerald">Gratuito</x-badge>
            @endif
        </div>

        <h3 class="mt-3 text-lg font-semibold text-zinc-900">
            <a href="{{ route('eventos.show', $evento) }}" class="hover:text-uta-700">{{ $evento->nombre }}</a>
        </h3>

        <dl class="mt-3 space-y-1 text-sm text-zinc-600">
            <div><dt class="inline font-medium">Fechas:</dt> <dd class="inline">{{ $evento->fecha_inicio->isoFormat('D MMM') }} – {{ $evento->fecha_fin->isoFormat('D MMM YYYY') }}</dd></div>
            <div><dt class="inline font-medium">Duración:</dt> <dd class="inline">{{ $evento->horas }} horas</dd></div>
            <div><dt class="inline font-medium">Ponente:</dt> <dd class="inline">{{ \Illuminate\Support\Str::limit($evento->ponentes, 60) }}</dd></div>
        </dl>

        <div class="mt-auto flex items-center justify-between pt-4">
            @if ($disponibles === 0)
                <x-badge color="rose">Cupos agotados</x-badge>
            @elseif ($disponibles <= 5)
                <x-badge color="amber">Quedan {{ $disponibles }} cupos</x-badge>
            @else
                <span class="text-xs text-zinc-500">{{ $disponibles }} / {{ $evento->cupos }} cupos</span>
            @endif

            <x-button size="sm" variant="secondary" :href="route('eventos.show', $evento)">Ver detalle</x-button>
        </div>
    </div>
</article>
