{{-- Franja de aviso global configurable desde /admin/configuracion --}}
@use('App\Models\Configuracion')

@if (Configuracion::activo('aviso_activo') && ($texto = Configuracion::valor('aviso_texto')))
    @php
        $estilos = [
            'info' => 'bg-marino-800 text-white',
            'alerta' => 'bg-amber-400 text-amber-950',
            'exito' => 'bg-uta-600 text-white',
        ];
        $enlace = Configuracion::valor('aviso_enlace');
    @endphp

    <div class="{{ $estilos[Configuracion::valor('aviso_estilo')] ?? $estilos['info'] }} px-4 py-2 text-center text-sm font-medium" role="status">
        {{ $texto }}
        @if ($enlace)
            <a href="{{ $enlace }}" class="ml-2 font-semibold underline underline-offset-2 hover:no-underline">Más información</a>
        @endif
    </div>
@endif
