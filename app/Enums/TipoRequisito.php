<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum TipoRequisito: string
{
    use HasValues;

    /** Exige que el usuario tenga cargado un documento de perfil (ver requisitos.tipo_documento). */
    case Documento = 'documento';

    /** Respuesta de texto que el usuario escribe al inscribirse. */
    case Texto = 'texto';

    public function label(): string
    {
        return match ($this) {
            self::Documento => 'Documento del perfil',
            self::Texto => 'Respuesta de texto',
        };
    }
}
