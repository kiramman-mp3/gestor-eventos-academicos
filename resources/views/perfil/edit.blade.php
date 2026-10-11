<x-app-layout title="Mi perfil">
    <x-page-header title="Mi perfil" subtitle="Tus datos personales y documentos para inscribirte en eventos." />

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-6 lg:col-span-1">
            <h2 class="text-lg font-semibold text-zinc-900">Datos personales</h2>

            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="font-medium text-zinc-500">Nombre</dt><dd class="text-zinc-900">{{ $user->nombre_completo }}</dd></div>
                <div><dt class="font-medium text-zinc-500">Cédula</dt><dd class="text-zinc-900">{{ $user->cedula }}</dd></div>
                <div><dt class="font-medium text-zinc-500">Correo</dt><dd class="text-zinc-900">{{ $user->email }}</dd></div>
                <div><dt class="font-medium text-zinc-500">Tipo de cuenta</dt><dd class="text-zinc-900">{{ $user->tipo->label() }}</dd></div>
                <div><dt class="font-medium text-zinc-500">Carrera</dt><dd class="text-zinc-900">{{ $user->categoria?->nombre ?? 'No aplica' }}</dd></div>
            </dl>

            <form method="POST" action="{{ route('perfil.update') }}" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')

                <x-field name="telefono" label="Celular" :value="$user->telefono" required inputmode="numeric" maxlength="10" />

                @if ($user->tipo === \App\Enums\TipoUsuario::Institucional && ! $user->categoria_id)
                    <x-field name="categoria_id" label="Carrera" type="select" :options="$categorias" placeholder="Selecciona tu carrera" required
                             help="Solo puedes elegirla una vez." />
                @endif

                <x-button>Guardar</x-button>
            </form>
        </section>

        <section class="card p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-zinc-900">Documentos</h2>
            <p class="mt-1 text-sm text-zinc-500">PDF, JPG o PNG de hasta {{ config('fisei.uploads.documento_max_kb') / 1024 }} MB. Se guardan de forma privada: solo tú y la administración pueden verlos.</p>

            <ul class="mt-4 divide-y divide-zinc-100">
                @foreach ($tiposDocumento as $tipo)
                    @php($documento = $user->documento($tipo))
                    <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-medium text-zinc-900">{{ $tipo->label() }}</p>
                            @if ($documento)
                                <p class="text-xs text-zinc-500">Subido {{ $documento->updated_at->diffForHumans() }}</p>
                            @else
                                <x-badge color="amber">Pendiente</x-badge>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($documento)
                                <x-button size="sm" variant="secondary" :href="route('documentos.show', $documento)" target="_blank" rel="noopener">Ver</x-button>
                                <form method="POST" action="{{ route('documentos.destroy', $documento) }}" onsubmit="return confirm('¿Eliminar este documento?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="ghost">Eliminar</x-button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="tipo" value="{{ $tipo->value }}">
                                <input type="file" name="archivo" required accept=".pdf,.jpg,.jpeg,.png"
                                       class="block w-48 text-xs text-zinc-600 file:mr-2 file:rounded-md file:border-0 file:bg-zinc-100 file:px-2 file:py-1 file:text-xs file:font-semibold">
                                <x-button size="sm">{{ $documento ? 'Reemplazar' : 'Subir' }}</x-button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
            <x-input-error :messages="$errors->get('archivo')" />
            <x-input-error :messages="$errors->get('tipo')" />
        </section>

        <section class="card p-6 lg:col-span-3">
            <h2 class="text-lg font-semibold text-zinc-900">Cambiar contraseña</h2>
            <form method="POST" action="{{ route('perfil.password') }}" class="mt-4 grid gap-4 sm:grid-cols-3">
                @csrf
                @method('PUT')
                <div>
                    <x-input-label for="current_password" value="Contraseña actual" required />
                    <x-text-input id="current_password" name="current_password" type="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->password->get('current_password')" />
                </div>
                <div>
                    <x-input-label for="password" value="Nueva contraseña" required />
                    <x-text-input id="password" name="password" type="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->password->get('password')" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirmar" required />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                </div>
                <div class="sm:col-span-3"><x-button variant="dark">Actualizar contraseña</x-button></div>
            </form>
        </section>
    </div>
</x-app-layout>
