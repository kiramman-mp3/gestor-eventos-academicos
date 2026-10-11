<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum EstadoSolicitud: string
{
    use HasValues;

    case Pendiente = 'pendiente';
    case EnProgreso = 'en_progreso';
    case Terminado = 'terminado';
    case Rechazado = 'rechazado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnProgreso => 'En progreso',
            self::Terminado => 'Terminado',
            self::Rechazado => 'Rechazado',
        };
    }
}
