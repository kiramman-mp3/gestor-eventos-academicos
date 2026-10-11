<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum RolUsuario: string
{
    use HasValues;

    case Administrador = 'administrador';
    case Estudiante = 'estudiante';
    case Docente = 'docente';

    public function label(): string
    {
        return match ($this) {
            self::Administrador => 'Administrador',
            self::Estudiante => 'Estudiante',
            self::Docente => 'Docente',
        };
    }
}
