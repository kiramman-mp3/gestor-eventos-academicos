<x-guest-layout title="Recuperar contraseña">
    <h1 class="text-2xl font-bold text-uta-600">Recuperar contraseña</h1>
    <p class="mt-1 text-sm text-zinc-500">Ingresa tu correo y te enviaremos un enlace para crear una nueva contraseña.</p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <x-field name="email" label="Correo electrónico" type="email" required autofocus />
        <x-button class="w-full">Enviar enlace</x-button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('login') }}" class="font-semibold text-uta-600 hover:text-uta-700">Volver al inicio de sesión</a>
    </p>
</x-guest-layout>
