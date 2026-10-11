<x-app-layout title="Comprobantes de pago">
    <x-admin.nav />

    <x-page-header title="Comprobantes de pago" subtitle="Aprueba o rechaza los pagos enviados por los estudiantes.">
        @foreach (\App\Enums\EstadoComprobante::cases() as $opcion)
            <x-button size="sm" :variant="$opcion === $estado ? 'dark' : 'secondary'"
                      :href="route('admin.comprobantes.index', ['estado' => $opcion->value])">{{ $opcion->label() }}</x-button>
        @endforeach
    </x-page-header>

    @if ($comprobantes->isEmpty())
        <x-empty-state title="No hay comprobantes en estado «{{ $estado->label() }}»." />
    @else
        <div class="card overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr><th>Estudiante</th><th>Evento</th><th>Enviado</th><th>Archivo</th><th class="text-right">Acciones</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($comprobantes as $comprobante)
                        @php($inscripcion = $comprobante->inscripcion)
                        <tr>
                            <td>
                                <p class="font-medium text-zinc-900">{{ $inscripcion->user->nombre_completo }}</p>
                                <p class="text-xs text-zinc-500">{{ $inscripcion->user->cedula }} · {{ $inscripcion->user->email }}</p>
                            </td>
                            <td>
                                <p>{{ $inscripcion->evento->nombre }}</p>
                                <p class="text-xs text-zinc-500">Costo: ${{ number_format((float) $inscripcion->evento->costo, 2) }}</p>
                            </td>
                            <td class="whitespace-nowrap">{{ $comprobante->created_at->isoFormat('lll') }}</td>
                            <td>
                                <a href="{{ route('comprobantes.show', $comprobante) }}" target="_blank" rel="noopener" class="font-medium text-uta-600 hover:text-uta-700">Abrir</a>
                            </td>
                            <td class="text-right">
                                @if ($comprobante->estado === \App\Enums\EstadoComprobante::Pendiente)
                                    <div x-data="{ rechazar: false }" class="flex flex-col items-end gap-2">
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('admin.comprobantes.aprobar', $comprobante) }}">
                                                @csrf
                                                <x-button size="sm" variant="success">Aprobar</x-button>
                                            </form>
                                            <x-button type="button" size="sm" variant="secondary" @click="rechazar = !rechazar">Rechazar</x-button>
                                        </div>
                                        <form x-show="rechazar" x-cloak method="POST" action="{{ route('admin.comprobantes.rechazar', $comprobante) }}" class="flex w-72 flex-col gap-2">
                                            @csrf
                                            <x-text-input name="motivo" required minlength="5" maxlength="500" placeholder="Motivo del rechazo" />
                                            <x-button size="sm" variant="danger">Confirmar rechazo</x-button>
                                        </form>
                                    </div>
                                @else
                                    <p class="text-xs text-zinc-500">
                                        {{ $comprobante->estado->label() }} por {{ $comprobante->revisor?->nombre_completo ?? '—' }}<br>
                                        {{ $comprobante->revisado_at?->isoFormat('lll') }}
                                    </p>
                                    @if ($comprobante->motivo_rechazo)
                                        <p class="text-xs text-rose-600">{{ $comprobante->motivo_rechazo }}</p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
        <div class="mt-6">{{ $comprobantes->links() }}</div>
    @endif
</x-app-layout>
