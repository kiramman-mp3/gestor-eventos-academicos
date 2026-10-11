<x-app-layout title="Mis solicitudes">
    <x-page-header title="Mis solicitudes de cambio">
        <x-button :href="route('solicitudes.create')">Nueva solicitud</x-button>
    </x-page-header>

    @if ($solicitudes->isEmpty())
        <x-empty-state title="No has enviado solicitudes." />
    @else
        <div class="space-y-4">
            @foreach ($solicitudes as $solicitud)
                <article class="card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-semibold text-zinc-900">{{ $solicitud->titulo }}</h2>
                        @if ($solicitud->resolucion)
                            <x-badge color="indigo">{{ $solicitud->resolucion->estado->label() }}</x-badge>
                        @else
                            <x-badge color="amber">Sin revisar</x-badge>
                        @endif
                    </div>
                    <p class="text-xs text-zinc-500">{{ $solicitud->tipo }} · {{ $solicitud->created_at->isoFormat('LL') }}</p>
                    <p class="mt-2 text-sm text-zinc-700">{{ $solicitud->descripcion }}</p>
                    @if ($solicitud->resolucion)
                        <p class="mt-3 rounded-md bg-zinc-50 px-3 py-2 text-sm text-zinc-700"><strong>Respuesta:</strong> {{ $solicitud->resolucion->comentario }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
