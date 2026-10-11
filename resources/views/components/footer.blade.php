@php($config = \App\Models\Configuracion::class)

<footer class="mt-12 bg-marino-900 text-white/80">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
            <img src="{{ asset('images/logo-uta.png') }}" alt="Universidad Técnica de Ambato" class="h-12 w-auto rounded bg-white p-1">
            <p class="mt-3 text-sm">{{ $config::valor('nombre_institucion', 'Facultad de Ingeniería en Sistemas, Electrónica e Industrial') }}</p>
        </div>

        <div class="text-sm">
            <p class="font-semibold text-white">Contacto</p>
            <ul class="mt-2 space-y-1">
                @if ($email = $config::valor('email_contacto'))
                    <li><a href="mailto:{{ $email }}" class="hover:text-white">{{ $email }}</a></li>
                @endif
                @if ($telefono = $config::valor('telefono_contacto'))
                    <li>{{ $telefono }}</li>
                @endif
                @if ($direccion = $config::valor('direccion'))
                    <li>{{ $direccion }}</li>
                @endif
            </ul>
        </div>

        <div class="text-sm">
            <p class="font-semibold text-white">Enlaces</p>
            <ul class="mt-2 space-y-1">
                @include('partials.footer-links')
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10 py-4 text-center text-xs text-white/60">
        &copy; {{ now()->year }} FISEI · Universidad Técnica de Ambato
    </div>
</footer>
