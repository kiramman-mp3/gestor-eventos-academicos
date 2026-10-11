<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum EstadoComprobante: string
{
    use HasValues;

    case Pendiente = 'pendiente';
    case Aprobado = 'aprobado';
    case Rechazado = 'rechazado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aprobado => 'Aprobado',
            self::Rechazado => 'Rechazado',
        };
    }
}
