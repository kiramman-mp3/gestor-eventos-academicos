<x-app-layout title="Usuarios">
    <x-admin.nav />

    <x-page-header title="Usuarios" subtitle="Estudiantes, docentes y administradores.">
        <x-button :href="route('admin.usuarios.create')">Nuevo administrador o docente</x-button>
    </x-page-header>

    <form method="GET" class="card mb-6 grid gap-3 p-4 sm:grid-cols-[1fr_12rem_auto]">
        <x-text-input name="q" :value="request('q')" placeholder="Nombre, correo o cédula…" aria-label="Buscar" />
        <x-select name="rol" :options="\App\Enums\RolUsuario::options()" :selected="$rol?->value" placeholder="Todos los roles" aria-label="Rol" />
        <x-button variant="dark">Filtrar</x-button>
    </form>

    <div class="card overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr><th>Usuario</th><th>Cédula</th><th>Carrera</th><th>Rol</th><th>Estado</th><th class="text-right">Acciones</th></tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse ($usuarios as $usuario)
                    <tr @class(['opacity-60' => ! $usuario->activo])>
                        <td>
                            <p class="font-medium text-zinc-900">{{ $usuario->apellido }} {{ $usuario->nombre }}</p>
                            <p class="text-xs text-zinc-500">{{ $usuario->email }} @unless ($usuario->hasVerifiedEmail()) · <span class="text-amber-600">sin verificar</span> @endunless</p>
                        </td>
                        <td>{{ $usuario->cedula }}</td>
                        <td>{{ $usuario->categoria?->nombre ?? '—' }}</td>
                        <td>
                            @if (auth()->user()->is($usuario))
                                <x-badge color="uta">{{ $usuario->rol->label() }} (tú)</x-badge>
                            @else
                                <form method="POST" action="{{ route('admin.usuarios.rol', $usuario) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-select name="rol" :options="\App\Enums\RolUsuario::options()" :selected="$usuario->rol->value"
                                              onchange="this.form.submit()" class="!w-40 !py-1" aria-label="Cambiar rol" />
                                </form>
                            @endif
                        </td>
                        <td><x-badge :color="$usuario->activo ? 'emerald' : 'zinc'">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</x-badge></td>
                        <td class="text-right">
                            @unless (auth()->user()->is($usuario))
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.usuarios.activo', $usuario) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-button size="sm" variant="secondary">{{ $usuario->activo ? 'Desactivar' : 'Activar' }}</x-button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" onsubmit="return confirm('¿Eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button size="sm" variant="ghost" class="text-rose-600">Eliminar</x-button>
                                    </form>
                                </div>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-zinc-500">Sin resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $usuarios->links() }}</div>
</x-app-layout>
