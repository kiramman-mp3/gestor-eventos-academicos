<x-app-layout title="Solicitudes de cambio">
    <x-admin.nav />
    <x-page-header title="Solicitudes de cambio">
        <x-button size="sm" :variant="request('estado') === 'sin_resolver' ? 'dark' : 'secondary'" :href="route('admin.solicitudes.index', ['estado' => 'sin_resolver'])">Sin resolver</x-button>
        @foreach (\App\Enums\EstadoSolicitud::cases() as $estado)
            <x-button size="sm" :variant="request('estado') === $estado->value ? 'dark' : 'secondary'" :href="route('admin.solicitudes.index', ['estado' => $estado->value])">{{ $estado->label() }}</x-button>
        @endforeach
        <x-button size="sm" variant="ghost" :href="route('admin.solicitudes.index')">Todas</x-button>
    </x-page-header>

    <div class="card overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Solicitud</th><th>Solicitante</th><th>Fecha</th><th>Prioridad</th><th>Estado</th><th></th></tr></thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse ($solicitudes as $solicitud)
                    <tr>
                        <td><p class="font-medium text-zinc-900">{{ $solicitud->titulo }}</p><p class="text-xs text-zinc-500">{{ $solicitud->tipo }}</p></td>
                        <td>{{ $solicitud->user->nombre_completo }}<p class="text-xs text-zinc-500">{{ $solicitud->user->rol->label() }}</p></td>
                        <td class="whitespace-nowrap">{{ $solicitud->created_at->isoFormat('ll') }}</td>
                        <td>{{ $solicitud->resolucion?->prioridad->label() ?? '—' }}</td>
                        <td>
                            @if ($solicitud->resolucion)
                                <x-badge color="indigo">{{ $solicitud->resolucion->estado->label() }}</x-badge>
                            @else
                                <x-badge color="amber">Sin resolver</x-badge>
                            @endif
                        </td>
                        <td class="text-right"><x-button size="sm" variant="secondary" :href="route('admin.solicitudes.show', $solicitud)">Revisar</x-button></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-zinc-500">Sin solicitudes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $solicitudes->links() }}</div>
</x-app-layout>
