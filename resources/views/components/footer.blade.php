@use('App\Models\Configuracion')

@php
    // Íconos de trazo (24×24) de cada red; solo se muestran las que tienen enlace configurado
    $redes = [
        'sitio_web_url' => ['Sitio web', '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3Z"/>'],
        'facebook_url' => ['Facebook', '<path d="M15 3h-2.5A3.5 3.5 0 0 0 9 6.5V10H6.5v3.5H9V21h3.5v-7.5H15l.5-3.5h-3V7a1 1 0 0 1 1-1H15Z"/>'],
        'instagram_url' => ['Instagram', '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".6" fill="currentColor"/>'],
        'x_url' => ['X', '<path d="M4 4h4.5L20 20h-4.5ZM20 4l-6.6 7.2M10.6 12.8 4 20"/>'],
        'youtube_url' => ['YouTube', '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9 5 3-5 3Z"/>'],
        'linkedin_url' => ['LinkedIn', '<rect x="3.5" y="3.5" width="17" height="17" rx="3"/><path d="M8 10.5V16M8 7.5v.01M11.5 16v-5.5M11.5 13c0-1.7 1-2.5 2.3-2.5S16 11.3 16 13v3"/>'],
        'tiktok_url' => ['TikTok', '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5M14 3c.4 2.6 2.4 4.4 5 4.5"/>'],
    ];
    $contacto = array_filter([
        'email' => Configuracion::valor('email_contacto'),
        'telefono' => Configuracion::valor('telefono_contacto'),
        'whatsapp' => Configuracion::valor('whatsapp'),
        'direccion' => Configuracion::valor('direccion'),
        'horario' => Configuracion::valor('horario_atencion'),
    ]);
@endphp

<footer class="mt-16 border-t-4 border-uta-600 bg-tinta text-white/80">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_1fr_0.8fr] lg:px-8">
        <div>
            <img src="{{ Configuracion::imagenUrl('logo_pie') }}" alt="{{ Configuracion::valor('nombre_institucion') }}" class="h-14 w-auto rounded-lg bg-white p-1.5">
            <p class="mt-4 font-semibold text-white">{{ Configuracion::valor('nombre_institucion') }}</p>
            @if ($descripcion = Configuracion::valor('texto_pie'))
                <p class="mt-2 text-sm leading-relaxed whitespace-pre-line">{{ $descripcion }}</p>
            @endif

            <ul class="mt-5 flex flex-wrap gap-3">
                @foreach ($redes as $clave => [$nombre, $icono])
                    @if ($url = Configuracion::valor($clave))
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" title="{{ $nombre }}" aria-label="{{ $nombre }}"
                               class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/30 text-white transition hover:border-uta-500 hover:bg-uta-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icono !!}</svg>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <div class="text-sm">
            <p class="etiqueta text-uta-200">Contacto</p>
            <ul class="mt-3 space-y-2">
                @isset($contacto['email'])
                    <li><a href="mailto:{{ $contacto['email'] }}" class="hover:text-white">{{ $contacto['email'] }}</a></li>
                @endisset
                @isset($contacto['telefono'])
                    <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contacto['telefono']) }}" class="hover:text-white">{{ $contacto['telefono'] }}</a></li>
                @endisset
                @isset($contacto['whatsapp'])
                    <li><a href="https://wa.me/{{ preg_replace('/\D/', '', $contacto['whatsapp']) }}" target="_blank" rel="noopener noreferrer" class="hover:text-white">WhatsApp: {{ $contacto['whatsapp'] }}</a></li>
                @endisset
                @isset($contacto['direccion'])
                    <li>
                        @if ($mapa = Configuracion::valor('mapa_url'))
                            <a href="{{ $mapa }}" target="_blank" rel="noopener noreferrer" class="hover:text-white">{{ $contacto['direccion'] }}</a>
                        @else
                            {{ $contacto['direccion'] }}
                        @endif
                    </li>
                @endisset
                @isset($contacto['horario'])
                    <li>{{ $contacto['horario'] }}</li>
                @endisset
            </ul>
        </div>

        <div class="text-sm">
            <p class="etiqueta text-uta-200">Enlaces</p>
            <ul class="mt-3 space-y-2">
                @include('partials.footer-links')
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10 py-4 text-center text-xs text-white/60">
        &copy; {{ now()->year }} {{ Configuracion::valor('texto_copyright') }}
    </div>
</footer>
