<x-app-layout title="Página de inicio">
    <x-admin.nav />
    <x-page-header title="Página de inicio" subtitle="Edita el carrusel, la sección «Nosotros», misión, visión, autoridades y reseñas.">
        <x-button variant="secondary" :href="route('home')" target="_blank">Ver sitio</x-button>
    </x-page-header>

    <div x-data="{ pestana: @js(old('tipo', $tipos[0]->value)) }">
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach ($tipos as $tipo)
                <button type="button" @click="pestana = '{{ $tipo->value }}'"
                        :class="pestana === '{{ $tipo->value }}' ? 'bg-marino-800 text-white' : 'bg-white text-zinc-700 ring-1 ring-zinc-200'"
                        class="rounded-full px-4 py-1.5 text-sm font-medium">
                    {{ $tipo->label() }} <span class="opacity-70">({{ $contenidos->get($tipo->value)?->count() ?? 0 }})</span>
                </button>
            @endforeach
        </div>

        @foreach ($tipos as $tipo)
            @php($items = $contenidos->get($tipo->value, collect()))
            <section x-show="pestana === '{{ $tipo->value }}'" x-cloak class="grid gap-6 lg:grid-cols-2">
                @foreach ($items as $contenido)
                    <form method="POST" action="{{ route('admin.contenidos.update', $contenido) }}" enctype="multipart/form-data" class="card space-y-3 p-5">
                        @csrf
                        @method('PUT')
                        @include('admin.contenidos._campos', ['tipo' => $tipo, 'contenido' => $contenido])
                        <div class="flex justify-between">
                            <x-button size="sm">Guardar</x-button>
                            <x-button type="submit" size="sm" variant="ghost" class="text-rose-600"
                                      form="eliminar-{{ $contenido->id }}">Eliminar</x-button>
                        </div>
                    </form>
                    <form id="eliminar-{{ $contenido->id }}" method="POST" action="{{ route('admin.contenidos.destroy', $contenido) }}"
                          onsubmit="return confirm('¿Eliminar este elemento?')" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach

                @if (! $tipo->esUnico() || $items->isEmpty())
                    <form method="POST" action="{{ route('admin.contenidos.store') }}" enctype="multipart/form-data"
                          class="card space-y-3 border-dashed p-5">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $tipo->value }}">
                        <p class="font-semibold text-zinc-900">Agregar a «{{ $tipo->label() }}»</p>
                        @include('admin.contenidos._campos', ['tipo' => $tipo, 'contenido' => null])
                        <x-button size="sm" variant="dark">Agregar</x-button>
                    </form>
                @endif
            </section>
        @endforeach
    </div>
</x-app-layout>
