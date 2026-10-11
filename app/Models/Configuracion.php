<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Configuración general clave/valor del sitio público y del sistema.
 *
 * GRUPOS define cada campo editable desde /admin/configuracion: etiqueta, tipo de control,
 * reglas de validación y valor por defecto (se usa mientras el campo esté vacío).
 */
#[Table('configuraciones', key: 'clave', keyType: 'string', incrementing: false)]
#[Fillable(['clave', 'valor'])]
class Configuracion extends Model
{
    public const DISK = 'public';

    public const DIRECTORIO = 'configuracion';

    public const GRUPOS = [
        'identidad' => [
            'titulo' => 'Identidad',
            'descripcion' => 'Nombre, eslogan y logotipos del encabezado, el pie y la pestaña del navegador.',
            'campos' => [
                'nombre_sitio' => ['etiqueta' => 'Nombre del sitio', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:80'], 'defecto' => 'Eventos Académicos FISEI', 'ayuda' => 'Aparece en el título de la pestaña y junto al logo.'],
                'nombre_institucion' => ['etiqueta' => 'Nombre de la institución', 'tipo' => 'text', 'reglas' => ['required', 'string', 'max:255'], 'defecto' => 'Facultad de Ingeniería en Sistemas, Electrónica e Industrial'],
                'eslogan' => ['etiqueta' => 'Eslogan', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:160'], 'defecto' => 'Universidad Técnica de Ambato'],
                'logo' => ['etiqueta' => 'Logo del encabezado', 'tipo' => 'imagen', 'defecto' => 'images/logo-fisei.png', 'ayuda' => 'PNG, JPG o WEBP de hasta 2 MB. Preferible con fondo transparente.'],
                'logo_pie' => ['etiqueta' => 'Logo del pie de página', 'tipo' => 'imagen', 'defecto' => 'images/logo-uta.png'],
                'favicon' => ['etiqueta' => 'Ícono de la pestaña (favicon)', 'tipo' => 'imagen', 'defecto' => 'images/logo-uta-escudo.png', 'ayuda' => 'Imagen cuadrada, idealmente de 64×64 px.'],
            ],
        ],
        'contacto' => [
            'titulo' => 'Contacto',
            'descripcion' => 'Datos que se muestran en el pie de página.',
            'campos' => [
                'email_contacto' => ['etiqueta' => 'Correo de contacto', 'tipo' => 'email', 'reglas' => ['nullable', 'email', 'max:255']],
                'telefono_contacto' => ['etiqueta' => 'Teléfono', 'tipo' => 'tel', 'reglas' => ['nullable', 'string', 'max:30']],
                'whatsapp' => ['etiqueta' => 'WhatsApp', 'tipo' => 'tel', 'reglas' => ['nullable', 'regex:/^\+?[0-9 ]{7,20}$/'], 'ayuda' => 'Número con código de país, por ejemplo +593 99 999 9999.'],
                'direccion' => ['etiqueta' => 'Dirección', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:255']],
                'horario_atencion' => ['etiqueta' => 'Horario de atención', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:120'], 'ayuda' => 'Ejemplo: Lunes a viernes, 08:00 a 17:00.'],
                'mapa_url' => ['etiqueta' => 'Enlace a Google Maps', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https', 'max:500']],
            ],
        ],
        'redes' => [
            'titulo' => 'Redes sociales',
            'descripcion' => 'Solo se muestran las redes con enlace.',
            'campos' => [
                'sitio_web_url' => ['etiqueta' => 'Sitio web institucional', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https,http', 'max:255']],
                'facebook_url' => ['etiqueta' => 'Facebook', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https', 'max:255']],
                'instagram_url' => ['etiqueta' => 'Instagram', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https', 'max:255']],
                'x_url' => ['etiqueta' => 'X (Twitter)', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https', 'max:255']],
                'youtube_url' => ['etiqueta' => 'YouTube', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https', 'max:255']],
                'linkedin_url' => ['etiqueta' => 'LinkedIn', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https', 'max:255']],
                'tiktok_url' => ['etiqueta' => 'TikTok', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https', 'max:255']],
            ],
        ],
        'inicio' => [
            'titulo' => 'Página de inicio',
            'descripcion' => 'Textos y secciones visibles en la portada. El contenido de cada sección se edita en «Página de inicio».',
            'campos' => [
                'hero_titulo' => ['etiqueta' => 'Título de portada sin carrusel', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:120'], 'defecto' => 'Eventos académicos FISEI'],
                'hero_subtitulo' => ['etiqueta' => 'Subtítulo de portada sin carrusel', 'tipo' => 'textarea', 'reglas' => ['nullable', 'string', 'max:300'], 'defecto' => 'Cursos, congresos, talleres y webinars de la Facultad de Ingeniería en Sistemas, Electrónica e Industrial.'],
                'carrusel_boton' => ['etiqueta' => 'Texto del botón del carrusel', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:40'], 'defecto' => 'Ver más'],
                'carrusel_intervalo' => ['etiqueta' => 'Segundos entre diapositivas', 'tipo' => 'number', 'reglas' => ['nullable', 'integer', 'min:3', 'max:30'], 'defecto' => '7'],
                'mostrar_proximos' => ['etiqueta' => 'Mostrar «Próximos eventos»', 'tipo' => 'boolean', 'defecto' => '1'],
                'cantidad_proximos' => ['etiqueta' => 'Cantidad de próximos eventos', 'tipo' => 'number', 'reglas' => ['nullable', 'integer', 'min:1', 'max:12'], 'defecto' => '3'],
                'mostrar_nosotros' => ['etiqueta' => 'Mostrar «Nosotros»', 'tipo' => 'boolean', 'defecto' => '1'],
                'mostrar_mision_vision' => ['etiqueta' => 'Mostrar misión y visión', 'tipo' => 'boolean', 'defecto' => '1'],
                'mostrar_autoridades' => ['etiqueta' => 'Mostrar autoridades', 'tipo' => 'boolean', 'defecto' => '1'],
                'titulo_autoridades' => ['etiqueta' => 'Título de la sección de autoridades', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:160'], 'defecto' => 'Facultad de Ingeniería en Sistemas, Electrónica e Industrial'],
                'mostrar_resenas' => ['etiqueta' => 'Mostrar reseñas', 'tipo' => 'boolean', 'defecto' => '1'],
                'titulo_resenas' => ['etiqueta' => 'Título de la sección de reseñas', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:80'], 'defecto' => 'Opiniones'],
            ],
        ],
        'aviso' => [
            'titulo' => 'Aviso global',
            'descripcion' => 'Franja informativa sobre el encabezado de todas las páginas.',
            'campos' => [
                'aviso_activo' => ['etiqueta' => 'Mostrar aviso', 'tipo' => 'boolean', 'defecto' => '0'],
                'aviso_texto' => ['etiqueta' => 'Texto del aviso', 'tipo' => 'text', 'reglas' => ['nullable', 'required_if_accepted:aviso_activo', 'string', 'max:200']],
                'aviso_enlace' => ['etiqueta' => 'Enlace del aviso', 'tipo' => 'url', 'reglas' => ['nullable', 'url:https,http', 'max:255']],
                'aviso_estilo' => ['etiqueta' => 'Estilo', 'tipo' => 'select', 'opciones' => ['info' => 'Informativo', 'alerta' => 'Advertencia', 'exito' => 'Novedad'], 'reglas' => ['nullable', 'in:info,alerta,exito'], 'defecto' => 'info'],
            ],
        ],
        'eventos' => [
            'titulo' => 'Eventos e inscripciones',
            'descripcion' => 'Límites del catálogo y de las inscripciones de estudiantes.',
            'campos' => [
                'eventos_por_pagina' => ['etiqueta' => 'Eventos por página en el catálogo', 'tipo' => 'number', 'reglas' => ['nullable', 'integer', 'min:3', 'max:48'], 'defecto' => '9'],
                'max_inscripciones_estudiante' => ['etiqueta' => 'Máximo de inscripciones activas por estudiante', 'tipo' => 'number', 'reglas' => ['nullable', 'integer', 'min:0', 'max:50'], 'defecto' => '0', 'ayuda' => 'Cuenta inscripciones vigentes en eventos abiertos o en ejecución. 0 = sin límite.'],
                'texto_catalogo' => ['etiqueta' => 'Texto de presentación del catálogo', 'tipo' => 'textarea', 'reglas' => ['nullable', 'string', 'max:500']],
            ],
        ],
        'pie' => [
            'titulo' => 'Pie de página',
            'descripcion' => 'Texto descriptivo y derechos de autor.',
            'campos' => [
                'texto_pie' => ['etiqueta' => 'Descripción breve', 'tipo' => 'textarea', 'reglas' => ['nullable', 'string', 'max:400']],
                'texto_copyright' => ['etiqueta' => 'Texto de derechos', 'tipo' => 'text', 'reglas' => ['nullable', 'string', 'max:160'], 'defecto' => 'FISEI · Universidad Técnica de Ambato', 'ayuda' => 'Se antepone «© año».'],
            ],
        ],
    ];

    private const CACHE_KEY = 'configuraciones';

    /** Definición de todos los campos, indexada por clave. */
    public static function campos(): array
    {
        return array_merge(...array_column(self::GRUPOS, 'campos'));
    }

    /** Valor guardado o, si está vacío, el definido por defecto. */
    public static function valor(string $clave, ?string $default = null): ?string
    {
        $valor = self::guardados()[$clave] ?? null;

        return $valor ?? $default ?? (self::campos()[$clave]['defecto'] ?? null);
    }

    public static function activo(string $clave): bool
    {
        return self::valor($clave) === '1';
    }

    public static function entero(string $clave): int
    {
        return (int) self::valor($clave);
    }

    /** URL de una imagen subida desde el panel o, si no hay, del recurso por defecto en public/. */
    public static function imagenUrl(string $clave): ?string
    {
        if ($path = self::guardados()[$clave] ?? null) {
            return Storage::disk(self::DISK)->url($path);
        }

        $defecto = self::campos()[$clave]['defecto'] ?? null;

        return $defecto ? asset($defecto) : null;
    }

    /** Reglas de validación de los campos de un grupo. */
    public static function reglas(string $grupo): array
    {
        $reglas = [];

        foreach (self::GRUPOS[$grupo]['campos'] as $clave => $campo) {
            $reglas[$clave] = match ($campo['tipo']) {
                'boolean' => ['required', 'boolean'],
                'imagen' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
                default => $campo['reglas'],
            };
        }

        return $reglas;
    }

    /** @return array<string, ?string> */
    public static function guardados(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => self::query()->pluck('valor', 'clave')->all());
    }

    /** @param array<string, mixed> $valores */
    public static function guardar(array $valores): void
    {
        $campos = self::campos();

        foreach (array_intersect_key($valores, $campos) as $clave => $valor) {
            if ($campos[$clave]['tipo'] === 'boolean') {
                $valor = filter_var($valor, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
            }

            self::query()->updateOrCreate(['clave' => $clave], ['valor' => $valor === null ? null : (string) $valor]);
        }

        self::olvidarCache();
    }

    public static function olvidarCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
