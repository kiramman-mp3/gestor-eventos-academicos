<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum TipoUsuario: string
{
    use HasValues;

    /** Correo @uta.edu.ec: debe pertenecer a una carrera. */
    case Institucional = 'institucional';
    case Publico = 'publico';

    public const DOMINIO_INSTITUCIONAL = 'uta.edu.ec';

    public function label(): string
    {
        return match ($this) {
            self::Institucional => 'Comunidad UTA',
            self::Publico => 'Público general',
        };
    }

    public static function desdeEmail(string $email): self
    {
        return str_ends_with(strtolower($email), '@'.self::DOMINIO_INSTITUCIONAL)
            ? self::Institucional
            : self::Publico;
    }
}
