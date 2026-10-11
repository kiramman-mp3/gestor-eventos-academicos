<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum Modalidad: string
{
    use HasValues;

    case Presencial = 'presencial';
    case Virtual = 'virtual';
    case Hibrida = 'hibrida';

    public function label(): string
    {
        return match ($this) {
            self::Presencial => 'Presencial',
            self::Virtual => 'Virtual',
            self::Hibrida => 'Híbrida',
        };
    }
}
