@php
    $secciones = [
        'admin.dashboard' => ['Resumen', 'admin.dashboard'],
        'admin.eventos.index' => ['Eventos', 'admin.eventos.*'],
        'admin.comprobantes.index' => ['Comprobantes', 'admin.comprobantes.*'],
        'gestion.notas.index' => ['Calificaciones', 'gestion.notas.*'],
        'admin.usuarios.index' => ['Usuarios', 'admin.usuarios.*'],
        'admin.categorias.index' => ['Carreras', 'admin.categorias.*'],
        'admin.tipos-evento.index' => ['Tipos de evento', 'admin.tipos-evento.*'],
        'admin.contenidos.index' => ['Página de inicio', 'admin.contenidos.*'],
        'admin.solicitudes.index' => ['Solicitudes', 'admin.solicitudes.*'],
        'admin.configuracion.edit' => ['Configuración', 'admin.configuracion.*'],
    ];
@endphp

<nav class="-mt-2 mb-8 overflow-x-auto border-b border-zinc-200" aria-label="Administración">
    <ul class="flex min-w-max gap-1">
        @foreach ($secciones as $ruta => [$etiqueta, $patron])
            @if (Route::has($ruta))
                <li>
                    <a href="{{ route($ruta) }}"
                       @class([
                           'inline-block border-b-2 px-3 py-2 text-sm font-medium transition',
                           'border-uta-600 text-uta-700' => request()->routeIs($patron),
                           'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-800' => ! request()->routeIs($patron),
                       ])>{{ $etiqueta }}</a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
