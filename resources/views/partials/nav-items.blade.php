<x-nav-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-nav-link>
<x-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">Eventos</x-nav-link>

@auth
    @if (auth()->user()->esEstudiante())
        <x-nav-link :href="route('inscripciones.index')" :active="request()->routeIs('inscripciones.*')">Mis inscripciones</x-nav-link>
    @endif

    <div x-data="{ menu: false }" class="relative" @click.outside="menu = false">
        <button type="button" @click="menu = !menu"
                class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-white/90 hover:bg-white/10">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-uta-600 text-xs font-bold text-white">
                {{ mb_substr(auth()->user()->nombre, 0, 1) }}{{ mb_substr(auth()->user()->apellido, 0, 1) }}
            </span>
            <span>{{ auth()->user()->nombre }}</span>
        </button>
        <div x-show="menu" x-cloak x-transition
             class="z-20 mt-1 w-56 rounded-lg bg-white py-1 text-sm text-zinc-700 shadow-lg ring-1 ring-black/5 md:absolute md:right-0">
            <p class="border-b border-zinc-100 px-4 py-2 text-xs text-zinc-500">{{ auth()->user()->rol->label() }}</p>
            <a href="{{ route('perfil.edit') }}" class="block px-4 py-2 hover:bg-zinc-50">Mi perfil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full px-4 py-2 text-left hover:bg-zinc-50">Cerrar sesión</button>
            </form>
        </div>
    </div>
@else
    <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Iniciar sesión</x-nav-link>
    <a href="{{ route('register') }}" class="rounded-md bg-uta-600 px-3 py-2 text-sm font-semibold text-white hover:bg-uta-700">Registrarse</a>
@endauth
