<x-guest-layout title="Nueva contraseña">
    <h1 class="text-2xl font-bold text-uta-600">Nueva contraseña</h1>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-field name="email" label="Correo electrónico" type="email" :value="$request->email" required autocomplete="username" />
        <x-field name="password" label="Nueva contraseña" type="password" required autocomplete="new-password"
                 help="Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos." />
        <x-field name="password_confirmation" label="Confirmar contraseña" type="password" required autocomplete="new-password" />

        <x-button class="w-full">Restablecer contraseña</x-button>
    </form>
</x-guest-layout>
