<x-guest-layout title="Iniciar sesión">
    <h1 class="text-2xl font-bold text-uta-600">Iniciar sesión</h1>
    <p class="mt-1 text-sm text-zinc-500">Accede con tu correo registrado.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <x-field name="email" label="Correo electrónico" type="email" required autofocus autocomplete="username" />
        <x-field name="password" label="Contraseña" type="password" required autocomplete="current-password" />

        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2 text-zinc-600">
                <input type="checkbox" name="remember" class="rounded border-zinc-300 text-uta-600 focus:ring-uta-500">
                Recordarme
            </label>
            <a href="{{ route('password.request') }}" class="font-medium text-uta-600 hover:text-uta-700">¿Olvidaste tu contraseña?</a>
        </div>

        <x-button class="w-full">Ingresar</x-button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-600">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" class="font-semibold text-uta-600 hover:text-uta-700">Regístrate aquí</a>
    </p>
</x-guest-layout>
