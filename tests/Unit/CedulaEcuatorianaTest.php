<?php

namespace Tests\Unit;

use App\Rules\CedulaEcuatoriana;
use Database\Factories\UserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CedulaEcuatorianaTest extends TestCase
{
    public static function cedulasInvalidas(): array
    {
        return [
            'letras' => ['17ABC45678'],
            'corta' => ['171234567'],
            'provincia 00' => ['0012345678'],
            'provincia 25' => ['2512345678'],
            'tercer digito 6' => ['1761234567'],
            'verificador incorrecto' => ['1800000001'],
        ];
    }

    #[DataProvider('cedulasInvalidas')]
    public function test_rechaza_cedulas_invalidas(string $cedula): void
    {
        $this->assertFalse(CedulaEcuatoriana::esValida($cedula));
    }

    public function test_acepta_cedulas_validas(): void
    {
        $this->assertTrue(CedulaEcuatoriana::esValida('1800000000'));
        $this->assertTrue(CedulaEcuatoriana::esValida('1710034065'));

        foreach (range(1, 50) as $_) {
            $this->assertTrue(CedulaEcuatoriana::esValida(UserFactory::cedulaValida()));
        }
    }
}
