{{-- Encabezado inspirado en el sitio legado: logo grande, accesos con íconos rojos y menú inferior --}}
@use('App\Models\Configuracion')

<header x-data="{ open: false }" class="relative z-30 bg-white shadow-[0_2px_5px_rgba(0,0,0,0.05)]">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-4">
            <img src="{{ Configuracion::imagenUrl('logo') }}" alt="{{ Configuracion::valor('nombre_sitio') }}" class="h-14 w-auto object-contain sm:h-20">
            <span class="hidden min-w-0 border-l-2 border-uta-600 pl-4 lg:block">
                <span class="block truncate text-lg font-bold text-tinta">{{ Configuracion::valor('nombre_sitio') }}</span>
                @if ($eslogan = Configuracion::valor('eslogan'))
                    <span class="block truncate text-sm text-zinc-500">{{ $eslogan }}</span>
                @endif
            </span>
        </a>

        <div class="hidden shrink-0 items-center gap-6 md:flex">
            <div class="flex items-center gap-3">
                <svg class="h-8 w-8 text-uta-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="1.5"/><path stroke-linecap="round" d="M8 20h8M12 16v4"/></svg>
                <div class="leading-tight">
                    <span class="block font-semibold">Plataforma educativa</span>
                    <a href="{{ route('eventos.index') }}" class="text-sm font-medium text-uta-600 hover:underline">Ver eventos</a>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <svg class="h-8 w-8 text-uta-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                <div class="leading-tight">
                    @auth
                        <span class="block max-w-48 truncate font-semibold">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-uta-600 hover:underline">Cerrar sesión</button>
                        </form>
                    @else
                        <span class="block font-semibold">Mi cuenta</span>
                        <a href="{{ route('login') }}" class="text-sm font-medium text-uta-600 hover:underline">Iniciar sesión</a>
                        <span class="text-sm text-zinc-400">·</span>
                        <a href="{{ route('register') }}" class="text-sm font-medium text-uta-600 hover:underline">Regístrate</a>
                    @endauth
                </div>
            </div>
        </div>

        <button type="button" @click="open = !open" class="rounded-md p-2 text-uta-600 hover:bg-uta-50 md:hidden" aria-label="Abrir menú" :aria-expanded="open">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <nav class="border-t border-zinc-100" aria-label="Principal">
        <div class="mx-auto hidden max-w-7xl items-center gap-6 px-4 sm:px-6 md:flex lg:px-8">
            @include('partials.nav-items')
        </div>

        <div x-show="open" x-cloak x-transition class="flex flex-col px-4 pb-3 md:hidden">
            @include('partials.nav-items')
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="py-2 text-sm font-medium text-uta-600">Cerrar sesión</button>
                </form>
            @else
                <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Iniciar sesión</x-nav-link>
                <x-nav-link :href="route('register')" :active="request()->routeIs('register')">Registrarse</x-nav-link>
            @endauth
        </div>
    </nav>
</header>
