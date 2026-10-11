{{-- Vista compartida para catálogos simples (nombre único). $items: [modelo, uso, eliminable] --}}
<x-app-layout :title="$titulo">
    <x-admin.nav />
    <x-page-header :title="$titulo" :subtitle="$descripcion" />

    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route($ruta.'.store') }}" class="card h-fit space-y-4 p-6">
            @csrf
            <h2 class="font-semibold text-zinc-900">Agregar</h2>
            <x-field name="nombre" label="Nombre" required maxlength="100" />
            <x-button>Guardar</x-button>
        </form>

        <div class="card overflow-hidden lg:col-span-2">
            <ul class="divide-y divide-zinc-100">
                @forelse ($items as [$item, $uso, $eliminable])
                    <li x-data="{ editando: false }" class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div x-show="!editando">
                            <p class="font-medium text-zinc-900">{{ $item->nombre }}</p>
                            <p class="text-xs text-zinc-500">{{ $uso }}</p>
                        </div>

                        <form x-show="editando" x-cloak method="POST" action="{{ route($ruta.'.update', $item) }}" class="flex flex-1 gap-2">
                            @csrf
                            @method('PUT')
                            <x-text-input name="nombre" :value="$item->nombre" required maxlength="100" aria-label="Nombre" />
                            <x-button size="sm">Guardar</x-button>
                            <x-button type="button" size="sm" variant="ghost" @click="editando = false">Cancelar</x-button>
                        </form>

                        <div x-show="!editando" class="flex gap-2">
                            <x-button type="button" size="sm" variant="secondary" @click="editando = true">Editar</x-button>
                            @if ($eliminable)
                                <form method="POST" action="{{ route($ruta.'.destroy', $item) }}" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="ghost" class="text-rose-600">Eliminar</x-button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-zinc-500">Sin registros.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
