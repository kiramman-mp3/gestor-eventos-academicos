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

<nav class="-mt-2 mb-8 overflow-x-auto rounded-2xl bg-white p-2 shadow-[0_4px_12px_rgba(0,0,0,0.06)] ring-1 ring-zinc-100" aria-label="Administración">
    <ul class="flex min-w-max gap-1">
        @foreach ($secciones as $ruta => [$etiqueta, $patron])
            <li>
                <a href="{{ route($ruta) }}"
                   @if (request()->routeIs($patron)) aria-current="page" @endif
                   @class([
                       'inline-block rounded-lg px-3.5 py-2 text-sm font-semibold transition',
                       'bg-uta-600 text-white shadow' => request()->routeIs($patron),
                       'text-tinta hover:bg-uta-50 hover:text-uta-700' => ! request()->routeIs($patron),
                   ])>{{ $etiqueta }}</a>
            </li>
        @endforeach
    </ul>
</nav>
