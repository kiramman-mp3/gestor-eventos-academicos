<x-app-layout title="Eventos">
    <x-admin.nav />

    <x-page-header title="Eventos" subtitle="Crea, publica y administra los eventos académicos.">
        <x-button :href="route('admin.eventos.create')">Nuevo evento</x-button>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-3 p-4 sm:grid-cols-[1fr_14rem_auto]">
        <x-text-input name="q" :value="request('q')" placeholder="Buscar por nombre…" aria-label="Buscar" />
        <x-select name="estado" :options="\App\Enums\EstadoEvento::options()" :selected="$estado?->value" placeholder="Todos los estados" aria-label="Estado" />
        <x-button variant="dark">Filtrar</x-button>
    </form>

    @if ($eventos->isEmpty())
        <x-empty-state title="No hay eventos registrados.">
            <a href="{{ route('admin.eventos.create') }}" class="font-semibold text-uta-600">Crea el primero</a>.
        </x-empty-state>
    @else
        <div class="card overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr><th>Evento</th><th>Tipo</th><th>Fechas</th><th>Estado</th><th class="text-right">Inscritos</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($eventos as $evento)
                        <tr>
                            <td class="font-medium text-zinc-900">
                                <a href="{{ route('admin.eventos.show', $evento) }}" class="hover:text-uta-700">{{ $evento->nombre }}</a>
                            </td>
                            <td>{{ $evento->tipoEvento->nombre }}</td>
                            <td class="whitespace-nowrap">{{ $evento->fecha_inicio->isoFormat('D MMM YYYY') }} – {{ $evento->fecha_fin->isoFormat('D MMM YYYY') }}</td>
                            <td><x-badge :color="$evento->estado->color()">{{ $evento->estado->label() }}</x-badge></td>
                            <td class="text-right">{{ $evento->cupos_ocupados }} / {{ $evento->cupos }}</td>
                            <td class="text-right">
                                <x-button size="sm" variant="secondary" :href="route('admin.eventos.show', $evento)">Gestionar</x-button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $eventos->links() }}</div>
    @endif
</x-app-layout>
