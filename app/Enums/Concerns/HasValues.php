<?php

namespace App\Enums\Concerns;

/**
 * Helpers comunes para enums respaldados por string.
 *
 * @mixin \BackedEnum
 */
trait HasValues
{
    /**
     * Valores en el orden declarado. Las migraciones los usan para generar las columnas ENUM.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Opciones para un <select>: [valor => etiqueta].
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
