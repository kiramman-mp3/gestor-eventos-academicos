<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida una cédula ecuatoriana: 10 dígitos, provincia 01-24, tercer dígito < 6
 * y dígito verificador según el algoritmo de módulo 10.
 */
class CedulaEcuatoriana implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::esValida((string) $value)) {
            $fail('El número de cédula no es una cédula ecuatoriana válida.');
        }
    }

    public static function esValida(string $cedula): bool
    {
        if (! preg_match('/^\d{10}$/', $cedula)) {
            return false;
        }

        $provincia = (int) substr($cedula, 0, 2);
        if ($provincia < 1 || $provincia > 24 || (int) $cedula[2] >= 6) {
            return false;
        }

        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $valor = (int) $cedula[$i] * ($i % 2 === 0 ? 2 : 1);
            $suma += $valor > 9 ? $valor - 9 : $valor;
        }

        return (10 - $suma % 10) % 10 === (int) $cedula[9];
    }
}
