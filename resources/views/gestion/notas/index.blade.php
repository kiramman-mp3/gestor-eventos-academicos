<x-app-layout title="Calificaciones">
    @if (auth()->user()->esAdmin())
        <x-admin.nav />
    @endif

    <x-page-header title="Calificaciones" subtitle="Eventos en los que puedes registrar notas y asistencia." />

    @if ($eventos->isEmpty())
        <x-empty-state title="No tienes eventos asignados." />
    @else
        <div class="card overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Evento</th><th>Fechas</th><th>Estado</th><th class="text-right">Participantes</th><th></th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($eventos as $evento)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $evento->nombre }}</td>
                            <td class="whitespace-nowrap">{{ $evento->fecha_inicio->isoFormat('D MMM') }} – {{ $evento->fecha_fin->isoFormat('D MMM YYYY') }}</td>
                            <td><x-badge :color="$evento->estado->color()">{{ $evento->estado->label() }}</x-badge></td>
                            <td class="text-right">{{ $evento->participantes }}</td>
                            <td class="text-right"><x-button size="sm" variant="secondary" :href="route('gestion.notas.edit', $evento)">Calificar</x-button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $eventos->links() }}</div>
    @endif
</x-app-layout>
