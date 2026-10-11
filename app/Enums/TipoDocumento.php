<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

/** Documentos personales que el usuario sube a su perfil (almacenamiento privado). */
enum TipoDocumento: string
{
    use HasValues;

    case Cedula = 'cedula';
    case Papeleta = 'papeleta';
    case Matricula = 'matricula';

    public function label(): string
    {
        return match ($this) {
            self::Cedula => 'Cédula de identidad',
            self::Papeleta => 'Papeleta de votación',
            self::Matricula => 'Matrícula',
        };
    }
}
