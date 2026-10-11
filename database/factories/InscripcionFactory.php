<?php

namespace Database\Factories;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inscripcion>
 */
class InscripcionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'evento_id' => Evento::factory(),
            'estado' => EstadoInscripcion::EnEsperaPago,
        ];
    }

    public function estado(EstadoInscripcion $estado): static
    {
        return $this->state(fn () => ['estado' => $estado]);
    }

    public function pagada(): static
    {
        return $this->estado(EstadoInscripcion::Pagado);
    }
}
