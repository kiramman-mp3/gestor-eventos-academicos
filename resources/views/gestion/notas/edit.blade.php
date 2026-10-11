<x-app-layout :title="'Calificaciones · '.$evento->nombre">
    @if (auth()->user()->esAdmin())
        <x-admin.nav />
    @endif

    <x-page-header :title="$evento->nombre" subtitle="Registro de notas, asistencia y legalización.">
        <x-button variant="secondary" size="sm" :href="route('gestion.notas.index')">Volver</x-button>
    </x-page-header>

    <div class="mb-4 flex flex-wrap gap-3 text-sm text-zinc-600">
        @if ($evento->requiere_nota)
            <x-badge color="indigo">Nota mínima: {{ $evento->nota_minima }} / 10</x-badge>
        @endif
        @if ($evento->requiere_asistencia)
            <x-badge color="indigo">Asistencia mínima: {{ $evento->asistencia_minima }}%</x-badge>
        @endif
        @if (! $evento->requiere_nota && ! $evento->requiere_asistencia)
            <x-badge>Este evento no exige nota ni asistencia: solo se registra la legalización.</x-badge>
        @endif
    </div>

    @if ($inscripciones->isEmpty())
        <x-empty-state title="No hay participantes con pago confirmado." />
    @else
        <form method="POST" action="{{ route('gestion.notas.update', $evento) }}">
            @csrf
            @method('PUT')

            <div class="card overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Participante</th>
                            @if ($evento->requiere_nota)<th class="w-32">Nota</th>@endif
                            @if ($evento->requiere_asistencia)<th class="w-32">Asistencia %</th>@endif
                            <th class="w-28 text-center">Legalizado</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($inscripciones as $inscripcion)
                            @php($base = "inscripciones.{$inscripcion->id}")
                            <tr>
                                <td>
                                    <p class="font-medium text-zinc-900">{{ $inscripcion->user->apellido }} {{ $inscripcion->user->nombre }}</p>
                                    <p class="text-xs text-zinc-500">{{ $inscripcion->user->cedula }}</p>
                                </td>
                                @if ($evento->requiere_nota)
                                    <td>
                                        <x-text-input type="number" step="0.01" min="0" max="10" :name="'inscripciones['.$inscripcion->id.'][nota]'"
                                                      :value="old($base.'.nota', $inscripcion->nota)" aria-label="Nota" />
                                        <x-input-error :messages="$errors->get($base.'.nota')" />
                                    </td>
                                @endif
                                @if ($evento->requiere_asistencia)
                                    <td>
                                        <x-text-input type="number" step="0.01" min="0" max="100" :name="'inscripciones['.$inscripcion->id.'][asistencia]'"
                                                      :value="old($base.'.asistencia', $inscripcion->asistencia)" aria-label="Asistencia" />
                                        <x-input-error :messages="$errors->get($base.'.asistencia')" />
                                    </td>
                                @endif
                                <td class="text-center">
                                    <input type="hidden" name="inscripciones[{{ $inscripcion->id }}][legalizado]" value="0">
                                    <input type="checkbox" name="inscripciones[{{ $inscripcion->id }}][legalizado]" value="1"
                                           @checked(old($base.'.legalizado', $inscripcion->legalizado)) class="rounded border-zinc-300 text-uta-600" aria-label="Legalizado">
                                </td>
                                <td>
                                    @if ($inscripcion->aptaParaCertificado())
                                        <x-badge color="emerald">Aprobado</x-badge>
                                    @elseif (($evento->requiere_nota && $inscripcion->nota === null) || ($evento->requiere_asistencia && $inscripcion->asistencia === null))
                                        <x-badge>Pendiente</x-badge>
                                    @else
                                        <x-badge color="rose">No aprobado</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-right"><x-button>Guardar calificaciones</x-button></div>
        </form>
    @endif
</x-app-layout>
