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
                @auth
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-uta-600 to-uta-800 text-sm font-bold text-white shadow-sm ring-2 ring-uta-100">
                        {{ mb_strtoupper(mb_substr(auth()->user()->nombre, 0, 1).mb_substr(auth()->user()->apellido, 0, 1)) }}
                    </div>
                    <div class="leading-tight">
                        <div class="flex items-center gap-2">
                            <span class="block max-w-40 truncate font-bold text-zinc-900">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</span>
                            <span class="rounded bg-uta-50 px-1.5 py-0.5 text-[10px] font-bold text-uta-700 ring-1 ring-uta-200">
                                {{ auth()->user()->rol->label() }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="mt-0.5">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-zinc-500 hover:text-uta-600 transition">Cerrar sesión</button>
                        </form>
                    </div>
                @else
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-uta-600 ring-1 ring-zinc-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                    </div>
                    <div class="leading-tight">
                        <span class="block font-bold text-zinc-800">Mi cuenta</span>
                        <div class="flex items-center gap-1.5 text-xs">
                            <a href="{{ route('login') }}" class="font-semibold text-uta-600 hover:underline">Iniciar sesión</a>
                            <span class="text-zinc-300">·</span>
                            <a href="{{ route('register') }}" class="font-semibold text-zinc-600 hover:text-uta-600 hover:underline">Regístrate</a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        <button type="button" @click="open = !open" class="rounded-xl p-2.5 text-uta-600 transition hover:bg-uta-50 md:hidden" aria-label="Abrir menú" :aria-expanded="open">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <nav class="border-t border-zinc-100 bg-white/90 backdrop-blur-xs" aria-label="Principal">
        <div class="mx-auto hidden max-w-7xl items-center gap-7 px-4 sm:px-6 md:flex lg:px-8">
            @include('partials.nav-items')
        </div>

        <div x-show="open" x-cloak x-transition class="flex flex-col gap-1 border-t border-zinc-100 bg-zinc-50/95 px-5 py-4 md:hidden">
            @include('partials.nav-items')
            @auth
                <div class="mt-3 border-t border-zinc-200/80 pt-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500">{{ auth()->user()->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 ring-1 ring-rose-200">Cerrar sesión</button>
                    </form>
                </div>
            @else
                <div class="mt-3 border-t border-zinc-200/80 pt-3 flex gap-2">
                    <x-button size="sm" :href="route('login')" class="flex-1">Iniciar sesión</x-button>
                    <x-button size="sm" variant="secondary" :href="route('register')" class="flex-1">Registrarse</x-button>
                </div>
            @endauth
        </div>
    </nav>
</header>
