<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Configuración general clave/valor (datos de contacto de la institución, etc.). */
#[Table('configuraciones', key: 'clave', keyType: 'string', incrementing: false)]
#[Fillable(['clave', 'valor'])]
class Configuracion extends Model
{
    /** Claves editables desde el panel con su etiqueta. */
    public const CLAVES = [
        'nombre_institucion' => 'Nombre de la institución',
        'email_contacto' => 'Correo de contacto',
        'telefono_contacto' => 'Teléfono de contacto',
        'direccion' => 'Dirección',
    ];

    private const CACHE_KEY = 'configuraciones';

    public static function valor(string $clave, ?string $default = null): ?string
    {
        $valores = Cache::rememberForever(self::CACHE_KEY, fn () => self::query()->pluck('valor', 'clave')->all());

        return $valores[$clave] ?? $default;
    }

    /** @param array<string, ?string> $valores */
    public static function guardar(array $valores): void
    {
        foreach (array_intersect_key($valores, self::CLAVES) as $clave => $valor) {
            self::query()->updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
