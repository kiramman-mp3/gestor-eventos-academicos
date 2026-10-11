<nav x-data="{ open: false }" class="bg-marino-800 shadow">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/logo-fisei.png') }}" alt="FISEI" class="h-10 w-auto rounded bg-white px-2 py-1">
            <span class="hidden text-sm font-semibold text-white sm:block">Eventos Académicos</span>
        </a>

        <button type="button" @click="open = !open" class="rounded-md p-2 text-white/80 hover:bg-white/10 md:hidden" aria-label="Abrir menú" :aria-expanded="open">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="hidden items-center gap-1 md:flex">
            @include('partials.nav-items')
        </div>
    </div>

    <div x-show="open" x-cloak x-transition class="border-t border-white/10 px-4 pb-4 pt-2 md:hidden">
        <div class="flex flex-col gap-1">
            @include('partials.nav-items')
        </div>
    </div>
</nav>
