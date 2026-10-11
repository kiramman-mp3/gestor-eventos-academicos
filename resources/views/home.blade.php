@use('App\Models\Configuracion')

<x-app-layout>
    {{-- Carrusel: recuadro oscuro sobre la imagen, flechas e indicadores circulares como en el sitio legado --}}
    @if ($carrusel->isNotEmpty())
        <section x-data="{ actual: 0, total: {{ $carrusel->count() }}, ir(i) { this.actual = (i + this.total) % this.total } }"
                 x-init="setInterval(() => ir(actual + 1), {{ Configuracion::entero('carrusel_intervalo') * 1000 }})"
                 class="relative mx-[calc(50%-50vw)] -mt-8 h-[26rem] overflow-hidden lg:h-[550px]" aria-roledescription="carrusel">
            @foreach ($carrusel as $i => $slide)
                <div x-show="actual === {{ $i }}" x-transition.opacity.duration.700ms @if ($i > 0) x-cloak @endif class="absolute inset-0">
                    @if ($slide->imagen_url)
                        <img src="{{ $slide->imagen_url }}" alt="" class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-uta-700 to-tinta"></div>
                    @endif
                    <div class="absolute inset-x-4 bottom-16 max-w-2xl rounded-2xl bg-black/55 p-6 text-white shadow-[0_0_25px_rgba(0,0,0,0.4)] backdrop-blur-[3px] sm:inset-x-auto sm:top-[15%] sm:bottom-auto sm:left-[10%] sm:px-12 sm:py-10">
                        <h1 class="text-3xl font-black tracking-tight lg:text-5xl">{{ $slide->titulo }}</h1>
                        <p class="mt-4 font-semibold tracking-wide uppercase lg:text-xl">{{ $slide->cuerpo }}</p>
                        <a href="{{ route('eventos.index') }}" class="boton mt-6">{{ Configuracion::valor('carrusel_boton') }}</a>
                    </div>
                </div>
            @endforeach

            @if ($carrusel->count() > 1)
                <div class="absolute inset-x-16 top-1/2 hidden -translate-y-1/2 justify-between sm:flex">
                    <button type="button" @click="ir(actual - 1)" aria-label="Diapositiva anterior"
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-uta-800/40 text-2xl text-white shadow-lg transition hover:scale-110 hover:bg-uta-600/80">&#10094;</button>
                    <button type="button" @click="ir(actual + 1)" aria-label="Diapositiva siguiente"
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-uta-800/40 text-2xl text-white shadow-lg transition hover:scale-110 hover:bg-uta-600/80">&#10095;</button>
                </div>

                <div class="absolute bottom-5 left-1/2 flex -translate-x-1/2 gap-3 sm:top-1/2 sm:bottom-auto sm:left-4 sm:left-6 sm:-translate-x-0 sm:-translate-y-1/2 sm:flex-col">
                    @foreach ($carrusel as $i => $slide)
                        <button type="button" @click="ir({{ $i }})" class="indicador" :aria-current="actual === {{ $i }}"
                                aria-label="Ir a la diapositiva {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <section class="mx-[calc(50%-50vw)] -mt-8 bg-gradient-to-br from-uta-700 to-tinta px-6 py-24 text-center text-white sm:-mx-6 lg:-mx-8">
            <span class="etiqueta text-uta-200">{{ Configuracion::valor('eslogan') }}</span>
            <h1 class="mx-auto mt-3 max-w-3xl text-4xl font-black lg:text-5xl">{{ Configuracion::valor('hero_titulo') }}</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-white/85">{{ Configuracion::valor('hero_subtitulo') }}</p>
            <a href="{{ route('eventos.index') }}" class="boton mt-8 bg-white text-uta-700 hover:bg-uta-50">{{ Configuracion::valor('carrusel_boton') }}</a>
        </section>
    @endif

    {{-- Próximos eventos --}}
    @if ($proximos !== null)
        <section class="py-16 text-center">
            <span class="etiqueta">Inscripciones abiertas</span>
            <h2 class="titulo-seccion">Próximos eventos</h2>

            @if ($proximos->isEmpty())
                <x-empty-state class="mt-10" title="No hay eventos con inscripciones abiertas por ahora." />
            @else
                <div class="mt-10 grid gap-8 text-left sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($proximos as $evento)
                        <x-evento-card :evento="$evento" />
                    @endforeach
                </div>
            @endif

            <a href="{{ route('eventos.index') }}" class="boton mt-10">Ver todo el catálogo</a>
        </section>
    @endif

    {{-- Nosotros --}}
    @if (Configuracion::activo('mostrar_nosotros'))
        @foreach ($nosotros as $bloque)
            <section class="flex flex-wrap items-center justify-center gap-10 py-16 @if ($loop->even) flex-row-reverse @endif">
                @if ($bloque->imagen_url)
                    <img src="{{ $bloque->imagen_url }}" alt="" class="w-full max-w-[550px] rounded-xl shadow-[0_4px_16px_rgba(0,0,0,0.1)]">
                @endif
                <div class="max-w-xl">
                    <span class="etiqueta">Sobre nosotros</span>
                    <h2 class="titulo-seccion my-4">{{ $bloque->titulo }}</h2>
                    <p class="leading-relaxed whitespace-pre-line text-zinc-600 sm:text-justify">{{ $bloque->cuerpo }}</p>
                </div>
            </section>
        @endforeach
    @endif

    {{-- Misión y visión --}}
    @if (Configuracion::activo('mostrar_mision_vision') && ($mision || $vision))
        <section class="grid gap-10 py-16 md:grid-cols-2">
            @foreach (['Misión' => $mision, 'Visión' => $vision] as $titulo => $bloque)
                @if ($bloque)
                    <article class="flex flex-col rounded-2xl border-t-[5px] border-uta-600 bg-white px-6 py-8 shadow-[0_4px_14px_rgba(0,0,0,0.08)] transition hover:-translate-y-1">
                        @if ($loop->first)
                            <svg class="h-10 w-10 text-uta-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2" fill="currentColor"/></svg>
                        @else
                            <svg class="h-10 w-10 text-uta-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        @endif
                        <h2 class="mt-4 text-2xl font-extrabold text-uta-700">{{ $titulo }}</h2>
                        <p class="mt-3 leading-relaxed whitespace-pre-line sm:text-justify">{{ $bloque->cuerpo }}</p>
                    </article>
                @endif
            @endforeach
        </section>
    @endif

    {{-- Autoridades --}}
    @if (Configuracion::activo('mostrar_autoridades') && $autoridades->isNotEmpty())
        <section class="py-16 text-center">
            <span class="etiqueta">Autoridades</span>
            <h2 class="titulo-seccion">{{ Configuracion::valor('titulo_autoridades') }}</h2>
            <div class="mt-10 flex flex-wrap justify-center gap-10">
                @foreach ($autoridades as $autoridad)
                    <article class="w-full max-w-[250px] overflow-hidden rounded-2xl bg-[#f9f9fc] shadow-[0_4px_12px_rgba(0,0,0,0.1)] transition hover:-translate-y-1">
                        @if ($autoridad->imagen_url)
                            <img src="{{ $autoridad->imagen_url }}" alt="Foto de {{ $autoridad->titulo }}" class="aspect-[4/5] w-full object-cover">
                        @endif
                        <div class="p-5">
                            <h3 class="text-lg font-bold">{{ $autoridad->titulo }}</h3>
                            <p class="text-sm text-zinc-600">{{ $autoridad->subtitulo }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Reseñas: una opinión a la vez con indicadores --}}
    @if (Configuracion::activo('mostrar_resenas') && $resenas->isNotEmpty())
        <section x-data="{ actual: 0, total: {{ $resenas->count() }} }"
                 x-init="setInterval(() => actual = (actual + 1) % total, {{ Configuracion::entero('carrusel_intervalo') * 1000 }})"
                 class="py-16 text-center">
            <span class="etiqueta">Reseñas</span>
            <h2 class="titulo-seccion uppercase">{{ Configuracion::valor('titulo_resenas') }}</h2>

            <div class="mx-auto mt-10 grid max-w-3xl">
                @foreach ($resenas as $i => $resena)
                    <figure x-show="actual === {{ $i }}" x-transition.opacity.duration.500ms @if ($i > 0) x-cloak @endif class="col-start-1 row-start-1">
                        <svg class="mx-auto h-10 w-10 text-uta-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 18v-5.5C4 8.4 6.3 5.7 10 5l.6 1.6C8.4 7.4 7.4 8.9 7.3 11H10v7H4Zm10 0v-5.5c0-4.1 2.3-6.8 6-7.5l.6 1.6c-2.2.8-3.2 2.3-3.3 4.4H20v7h-6Z"/></svg>
                        <blockquote class="mt-5 text-xl leading-relaxed lg:text-2xl">{{ $resena->cuerpo }}</blockquote>
                        <figcaption class="mt-8 flex flex-col items-center gap-3">
                            @if ($resena->imagen_url)
                                <img src="{{ $resena->imagen_url }}" alt="Foto de {{ $resena->titulo }}" class="h-20 w-20 rounded-full border-2 border-zinc-300 object-cover">
                            @endif
                            <div>
                                <strong>{{ $resena->titulo }}</strong>
                                <p class="text-sm text-zinc-500">{{ $resena->subtitulo }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            @if ($resenas->count() > 1)
                <div class="mt-6 flex justify-center gap-3">
                    @foreach ($resenas as $i => $resena)
                        <button type="button" @click="actual = {{ $i }}" class="indicador" :aria-current="actual === {{ $i }}"
                                aria-label="Ver reseña {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
</x-app-layout>
