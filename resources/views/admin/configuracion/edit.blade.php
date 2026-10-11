@use('App\Models\Configuracion')

<x-app-layout title="Configuración">
    <x-admin.nav />
    <x-page-header title="Configuración del sitio" subtitle="Identidad, contacto, redes, portada, avisos y límites del sistema." />

    {{-- Estadísticas --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        @foreach ($estadisticas as $etiqueta => $valor)
            <div class="stat-card">
                <p class="text-3xl font-bold">{{ $valor }}</p>
                <p class="mt-1 text-sm text-white/85">{{ $etiqueta }}</p>
            </div>
        @endforeach
    </div>

    @php($inicial = old('grupo', request('seccion', array_key_first($grupos))))

    <div x-data="{ seccion: @js(array_key_exists($inicial, $grupos) ? $inicial : array_key_first($grupos)) }" class="grid gap-6 lg:grid-cols-[15rem_1fr]">
        <nav aria-label="Secciones de configuración" class="flex gap-2 overflow-x-auto lg:flex-col lg:overflow-visible">
            @foreach ($grupos as $grupo => $definicion)
                <button type="button" @click="seccion = '{{ $grupo }}'"
                        :class="seccion === '{{ $grupo }}' ? 'bg-uta-600 text-white shadow' : 'bg-white text-tinta ring-1 ring-zinc-200 hover:text-uta-700'"
                        class="shrink-0 rounded-lg px-4 py-2.5 text-left text-sm font-semibold transition">
                    {{ $definicion['titulo'] }}
                </button>
            @endforeach
        </nav>

        <div>
            @foreach ($grupos as $grupo => $definicion)
                <form x-show="seccion === '{{ $grupo }}'" @if ($grupo !== $inicial) x-cloak @endif
                      method="POST" action="{{ route('admin.configuracion.update') }}" enctype="multipart/form-data" class="panel">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="grupo" value="{{ $grupo }}">

                    <h2 class="text-xl font-extrabold text-uta-700">{{ $definicion['titulo'] }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ $definicion['descripcion'] }}</p>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        @foreach ($definicion['campos'] as $clave => $campo)
                            @php($valor = $valores[$clave] ?? null)
                            @php($placeholder = isset($campo['defecto']) && $campo['tipo'] !== 'boolean' ? 'Por defecto: '.$campo['defecto'] : null)

                            @switch($campo['tipo'])
                                @case('boolean')
                                    <label class="flex items-center gap-3 rounded-lg border border-zinc-200 px-4 py-3 text-sm font-medium text-zinc-700 md:col-span-1">
                                        <input type="hidden" name="{{ $clave }}" value="0">
                                        <input type="checkbox" name="{{ $clave }}" value="1" class="h-4 w-4 rounded accent-uta-600"
                                               @checked(old($clave, $valor ?? $campo['defecto']) === '1')>
                                        {{ $campo['etiqueta'] }}
                                    </label>
                                    @break

                                @case('imagen')
                                    <div x-data="{ vista: @js(Configuracion::imagenUrl($clave)) }" class="md:col-span-2">
                                        <x-input-label :for="$clave" :value="$campo['etiqueta']" />
                                        <div class="flex flex-wrap items-center gap-5">
                                            <label class="imagen-editable" title="Cambiar imagen">
                                                <img x-show="vista" :src="vista" alt="" class="h-full w-full object-contain p-2">
                                                <span class="imagen-editable__icono" aria-hidden="true">
                                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.3-.86L19.5 7.94a1.88 1.88 0 0 0 0-2.65l-.79-.79a1.88 1.88 0 0 0-2.65 0L4.86 15.7 4 20Z"/></svg>
                                                </span>
                                                <input id="{{ $clave }}" type="file" name="{{ $clave }}" accept=".png,.jpg,.jpeg,.webp" class="sr-only"
                                                       @change="const f = $event.target.files[0]; if (f) vista = URL.createObjectURL(f)">
                                            </label>
                                            <div class="space-y-2 text-xs text-zinc-500">
                                                @isset($campo['ayuda'])<p>{{ $campo['ayuda'] }}</p>@endisset
                                                @if ($valor)
                                                    <label class="flex items-center gap-2 text-sm text-zinc-700">
                                                        <input type="checkbox" name="quitar_{{ $clave }}" value="1" class="h-4 w-4 accent-uta-600">
                                                        Restaurar imagen por defecto
                                                    </label>
                                                @else
                                                    <p>Usando la imagen por defecto.</p>
                                                @endif
                                            </div>
                                        </div>
                                        <x-input-error :messages="$errors->get($clave)" />
                                    </div>
                                    @break

                                @case('select')
                                    <x-field :name="$clave" :label="$campo['etiqueta']" type="select" :options="$campo['opciones']"
                                             :value="$valor ?? $campo['defecto'] ?? null" :help="$campo['ayuda'] ?? null" />
                                    @break

                                @default
                                    <x-field :name="$clave" :label="$campo['etiqueta']" :type="$campo['tipo']" :value="$valor"
                                             :required="in_array('required', $campo['reglas'], true)" :help="$campo['ayuda'] ?? null"
                                             :placeholder="$placeholder" :class="$campo['tipo'] === 'textarea' ? 'md:col-span-2' : null" />
                            @endswitch
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end border-t border-zinc-100 pt-5">
                        <x-button>Guardar {{ mb_strtolower($definicion['titulo']) }}</x-button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>

    {{-- Herramientas y sistema --}}
    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <section class="panel">
            <h2 class="text-lg font-extrabold text-uta-700">Herramientas de mantenimiento</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <x-button variant="secondary" :href="route('admin.configuracion.exportar')">Exportar configuración (JSON)</x-button>
                <form method="POST" action="{{ route('admin.configuracion.limpiar-cache') }}"
                      onsubmit="return confirm('¿Limpiar la caché del sistema?')">
                    @csrf
                    <x-button variant="secondary">Limpiar caché</x-button>
                </form>
                <x-button variant="secondary" :href="route('home')" target="_blank">Ver sitio público</x-button>
            </div>
        </section>

        <section class="panel text-sm text-zinc-600">
            <h2 class="text-lg font-extrabold text-uta-700">Sistema</h2>
            <dl class="mt-3 grid grid-cols-2 gap-2">
                <dt>Laravel</dt><dd>{{ app()->version() }}</dd>
                <dt>PHP</dt><dd>{{ PHP_VERSION }}</dd>
                <dt>Base de datos</dt><dd>{{ config('database.default') }}</dd>
                <dt>Entorno</dt><dd>{{ app()->environment() }}</dd>
                <dt>Correo saliente</dt><dd>{{ config('mail.default') }}</dd>
            </dl>
        </section>
    </div>
</x-app-layout>
