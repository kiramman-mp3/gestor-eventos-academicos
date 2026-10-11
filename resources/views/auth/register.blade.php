@php($dominio = '@'.\App\Enums\TipoUsuario::DOMINIO_INSTITUCIONAL)

<x-guest-layout title="Registro">
    <h1 class="text-2xl font-bold text-zinc-900">Crear cuenta</h1>
    <p class="mt-1 text-sm text-zinc-500">
        Si usas tu correo <strong>{{ $dominio }}</strong> selecciona tu carrera para ver los eventos dirigidos a ella.
    </p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 grid gap-4 sm:grid-cols-2"
          x-data="{ email: @js(old('email', '')) }">
        @csrf

        <x-field name="nombre" label="Nombres" required autofocus autocomplete="given-name" />
        <x-field name="apellido" label="Apellidos" required autocomplete="family-name" />
        <x-field name="cedula" label="Cédula" required inputmode="numeric" maxlength="10" pattern="\d{10}" />
        <x-field name="telefono" label="Celular" required inputmode="numeric" maxlength="10" pattern="09\d{8}" placeholder="09XXXXXXXX" />
        <x-field name="fecha_nacimiento" label="Fecha de nacimiento" type="date" required
                 :max="now()->subYears(config('fisei.edad_minima'))->toDateString()" />
        <x-field name="genero" label="Género" type="select" required placeholder="Selecciona"
                 :options="array_combine($generos, $generos)" />

        <x-field class="sm:col-span-2" name="email" label="Correo electrónico" type="email" required autocomplete="email"
                 x-model="email" />

        <div class="sm:col-span-2" x-show="email.toLowerCase().endsWith({{ Js::from($dominio) }})" x-cloak>
            <x-field name="categoria_id" label="Carrera" type="select" placeholder="Selecciona tu carrera"
                     :options="$categorias" x-bind:disabled="!email.toLowerCase().endsWith({{ Js::from($dominio) }})" />
        </div>

        <x-field name="password" label="Contraseña" type="password" required autocomplete="new-password"
                 help="Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos." />
        <x-field name="password_confirmation" label="Confirmar contraseña" type="password" required autocomplete="new-password" />

        <div class="sm:col-span-2">
            <x-button class="w-full">Registrarme</x-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-600">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="font-semibold text-uta-600 hover:text-uta-700">Inicia sesión</a>
    </p>
</x-guest-layout>
