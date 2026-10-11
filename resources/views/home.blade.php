<x-app-layout>
    {{-- Carrusel --}}
    @if ($carrusel->isNotEmpty())
        <section x-data="{ actual: 0, total: {{ $carrusel->count() }} }"
                 x-init="setInterval(() => actual = (actual + 1) % total, 6000)"
                 class="relative -mx-4 -mt-8 mb-12 h-[22rem] overflow-hidden sm:-mx-6 sm:rounded-b-2xl lg:-mx-8 lg:h-[28rem]">
            @foreach ($carrusel as $i => $slide)
                <div x-show="actual === {{ $i }}" x-transition.opacity.duration.700ms @if ($i > 0) x-cloak @endif class="absolute inset-0">
                    @if ($slide->imagen_url)
                        <img src="{{ $slide->imagen_url }}" alt="" class="h-full w-full object-cover">
                    @endif
                    <div class="absolute inset-0 flex items-end bg-gradient-to-t from-marino-900/90 via-marino-900/40 to-transparent">
                        <div class="mx-auto w-full max-w-7xl px-6 pb-12 lg:px-8">
                            <h1 class="max-w-2xl text-3xl font-bold text-white lg:text-5xl">{{ $slide->titulo }}</h1>
                            <p class="mt-3 max-w-xl text-white/85">{{ $slide->cuerpo }}</p>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                @foreach ($carrusel as $i => $slide)
                    <button type="button" @click="actual = {{ $i }}" class="h-2.5 w-2.5 rounded-full"
                            :class="actual === {{ $i }} ? 'bg-white' : 'bg-white/40'" aria-label="Ir a la diapositiva {{ $i + 1 }}"></button>
                @endforeach
            </div>
        </section>
    @else
        <section class="-mx-4 -mt-8 mb-12 bg-gradient-to-br from-marino-900 to-uta-800 px-6 py-20 text-white sm:-mx-6 lg:-mx-8">
            <div class="mx-auto max-w-7xl">
                <h1 class="text-4xl font-bold">Eventos académicos FISEI</h1>
                <p class="mt-3 max-w-xl text-white/85">Cursos, congresos, talleres y webinars de la Facultad de Ingeniería en Sistemas, Electrónica e Industrial.</p>
            </div>
        </section>
    @endif

    {{-- Próximos eventos --}}
    <section class="mb-16">
        <x-page-header title="Próximos eventos" subtitle="Inscripciones abiertas">
            <x-button variant="secondary" :href="route('eventos.index')">Ver todo el catálogo</x-button>
        </x-page-header>

        @if ($proximos->isEmpty())
            <x-empty-state title="No hay eventos con inscripciones abiertas por ahora." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($proximos as $evento)
                    <x-evento-card :evento="$evento" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Nosotros --}}
    @foreach ($nosotros as $bloque)
        <section class="mb-16 grid items-center gap-8 lg:grid-cols-2">
            @if ($bloque->imagen_url)
                <img src="{{ $bloque->imagen_url }}" alt="" class="h-72 w-full rounded-2xl object-cover shadow-md @if ($loop->odd) lg:order-last @endif">
            @endif
            <div>
                <p class="text-sm font-semibold tracking-wide text-uta-600 uppercase">Nosotros</p>
                <h2 class="mt-2 text-2xl font-bold text-zinc-900">{{ $bloque->titulo }}</h2>
                <p class="mt-4 leading-relaxed whitespace-pre-line text-zinc-600">{{ $bloque->cuerpo }}</p>
            </div>
        </section>
    @endforeach

    {{-- Misión y visión --}}
    @if ($mision || $vision)
        <section class="mb-16 grid gap-6 md:grid-cols-2">
            @foreach (['Misión' => $mision, 'Visión' => $vision] as $titulo => $bloque)
                @if ($bloque)
                    <div class="card border-t-4 border-t-uta-600 p-6">
                        <h2 class="text-xl font-bold text-marino-800">{{ $titulo }}</h2>
                        <p class="mt-3 leading-relaxed text-zinc-600">{{ $bloque->cuerpo }}</p>
                    </div>
                @endif
            @endforeach
        </section>
    @endif

    {{-- Autoridades --}}
    @if ($autoridades->isNotEmpty())
        <section class="mb-16 text-center">
            <h2 class="text-2xl font-bold text-zinc-900">Autoridades</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($autoridades as $autoridad)
                    <div class="card p-6">
                        @if ($autoridad->imagen_url)
                            <img src="{{ $autoridad->imagen_url }}" alt="{{ $autoridad->titulo }}" class="mx-auto h-28 w-28 rounded-full object-cover">
                        @endif
                        <h3 class="mt-4 font-semibold text-zinc-900">{{ $autoridad->titulo }}</h3>
                        <p class="text-sm text-uta-600">{{ $autoridad->subtitulo }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Reseñas --}}
    @if ($resenas->isNotEmpty())
        <section class="rounded-2xl bg-marino-800 px-6 py-12 text-white">
            <h2 class="text-center text-2xl font-bold">Opiniones</h2>
            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($resenas as $resena)
                    <figure class="rounded-xl bg-white/10 p-6">
                        <blockquote class="leading-relaxed text-white/90">“{{ $resena->cuerpo }}”</blockquote>
                        <figcaption class="mt-4 flex items-center gap-3">
                            @if ($resena->imagen_url)
                                <img src="{{ $resena->imagen_url }}" alt="" class="h-10 w-10 rounded-full object-cover">
                            @endif
                            <div>
                                <p class="font-semibold">{{ $resena->titulo }}</p>
                                <p class="text-xs text-white/70">{{ $resena->subtitulo }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
