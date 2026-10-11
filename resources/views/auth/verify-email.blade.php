<x-guest-layout title="Verifica tu correo">
    <h1 class="text-2xl font-bold text-uta-600">Verifica tu correo</h1>
    <p class="mt-3 text-sm text-zinc-600">
        Te enviamos un enlace de verificación a <strong>{{ auth()->user()->email }}</strong>.
        Ábrelo para activar tu cuenta y poder inscribirte en eventos.
    </p>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button>Reenviar enlace</x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button variant="ghost">Cerrar sesión</x-button>
        </form>
    </div>
</x-guest-layout>
