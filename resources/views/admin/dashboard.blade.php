<x-app-layout title="Administración">
    <x-admin.nav />

    <x-page-header title="Bienvenido, {{ auth()->user()->nombre }}" subtitle="Resumen general del sistema de eventos." />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $etiqueta => $valor)
            <div class="stat-card">
                <p class="text-4xl font-bold">{{ $valor }}</p>
                <p class="mt-1 text-sm text-white/85">{{ $etiqueta }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <a href="{{ route('admin.comprobantes.index') }}" class="panel flex items-center justify-between transition hover:-translate-y-1">
            <div>
                <p class="font-semibold text-zinc-900">Comprobantes por revisar</p>
                <p class="text-sm text-zinc-500">Pagos que esperan aprobación.</p>
            </div>
            <x-badge :color="$comprobantesPendientes ? 'amber' : 'emerald'" class="text-base">{{ $comprobantesPendientes }}</x-badge>
        </a>
        <a href="{{ route('admin.solicitudes.index', ['estado' => 'sin_resolver']) }}" class="panel flex items-center justify-between transition hover:-translate-y-1">
            <div>
                <p class="font-semibold text-zinc-900">Solicitudes de cambio sin resolver</p>
                <p class="text-sm text-zinc-500">Pedidos de mejora o corrección.</p>
            </div>
            <x-badge :color="$solicitudesSinResolver ? 'amber' : 'emerald'" class="text-base">{{ $solicitudesSinResolver }}</x-badge>
        </a>
    </div>

    <section class="card mt-8 overflow-hidden">
        <h2 class="border-b border-zinc-100 px-5 py-4 text-lg font-extrabold text-uta-700">Próximos eventos</h2>
        @if ($proximos->isEmpty())
            <p class="px-5 py-6 text-sm text-zinc-500">No hay eventos abiertos o en ejecución.</p>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Evento</th><th>Inicio</th><th>Estado</th><th class="text-right">Ocupación</th></tr></thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($proximos as $evento)
                            <tr>
                                <td class="font-medium text-zinc-900">
                                    <a href="{{ route('admin.eventos.show', $evento) }}" class="hover:text-uta-700">{{ $evento->nombre }}</a>
                                </td>
                                <td>{{ $evento->fecha_inicio->isoFormat('LL') }}</td>
                                <td><x-badge :color="$evento->estado->color()">{{ $evento->estado->label() }}</x-badge></td>
                                <td class="text-right">{{ $evento->cupos_ocupados }} / {{ $evento->cupos }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-app-layout>
