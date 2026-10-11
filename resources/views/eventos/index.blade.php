<x-app-layout title="Catálogo de eventos">
    <x-page-header title="Catálogo de eventos"
                   :subtitle="auth()->user()?->categoria ? 'Eventos públicos y de la carrera '.auth()->user()->categoria->nombre : 'Eventos abiertos al público'" />

    @if ($presentacion = \App\Models\Configuracion::valor('texto_catalogo'))
        <p class="-mt-2 mb-6 max-w-3xl leading-relaxed whitespace-pre-line text-zinc-600">{{ $presentacion }}</p>
    @endif

    <form method="GET" class="card mb-8 grid gap-4 p-4 sm:grid-cols-[1fr_14rem_auto]">
        <x-text-input name="q" :value="$filtros['q'] ?? ''" placeholder="Buscar por nombre…" aria-label="Buscar" />
        <x-select name="tipo" :options="$tipos" :selected="$filtros['tipo'] ?? null" placeholder="Todos los tipos" aria-label="Tipo de evento" />
        <x-button variant="dark">Filtrar</x-button>
    </form>

    @if ($eventos->isEmpty())
        <x-empty-state title="No encontramos eventos con esos filtros." />
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($eventos as $evento)
                <x-evento-card :evento="$evento" />
            @endforeach
        </div>

        <div class="mt-8">{{ $eventos->links() }}</div>
    @endif
</x-app-layout>
