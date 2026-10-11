<?php

namespace Database\Factories;

use App\Enums\EstadoEvento;
use App\Enums\Modalidad;
use App\Models\Evento;
use App\Models\TipoEvento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Por defecto: evento público, abierto, con inscripciones vigentes hoy y de pago.
 *
 * @extends Factory<Evento>
 */
class EventoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tipo_evento_id' => TipoEvento::factory(),
            'nombre' => 'Curso de '.fake()->words(3, true),
            'descripcion' => fake()->paragraph(),
            'ponentes' => fake()->name(),
            'modalidad' => Modalidad::Presencial,
            'fecha_inicio_inscripciones' => now()->subDays(5)->toDateString(),
            'fecha_fin_inscripciones' => now()->addDays(5)->toDateString(),
            'fecha_inicio' => now()->addDays(10)->toDateString(),
            'fecha_fin' => now()->addDays(20)->toDateString(),
            'horas' => 40,
            'cupos' => 30,
            'costo' => 25,
            'es_publico' => true,
            'estado' => EstadoEvento::Abierto,
        ];
    }

    public function gratuito(): static
    {
        return $this->state(fn () => ['costo' => 0]);
    }

    public function conCupos(int $cupos): static
    {
        return $this->state(fn () => ['cupos' => $cupos]);
    }

    public function privado(): static
    {
        return $this->state(fn () => ['es_publico' => false]);
    }

    public function borrador(): static
    {
        return $this->state(fn () => ['estado' => EstadoEvento::Borrador]);
    }

    public function inscripcionesCerradas(): static
    {
        return $this->state(fn () => [
            'fecha_inicio_inscripciones' => now()->subDays(20)->toDateString(),
            'fecha_fin_inscripciones' => now()->subDays(10)->toDateString(),
            'fecha_inicio' => now()->subDays(5)->toDateString(),
        ]);
    }

    public function finalizado(): static
    {
        return $this->inscripcionesCerradas()->state(fn () => [
            'fecha_fin' => now()->subDay()->toDateString(),
            'estado' => EstadoEvento::Finalizado,
        ]);
    }

    public function conCalificacion(float $notaMinima = 7, float $asistenciaMinima = 70): static
    {
        return $this->state(fn () => [
            'requiere_nota' => true,
            'nota_minima' => $notaMinima,
            'requiere_asistencia' => true,
            'asistencia_minima' => $asistenciaMinima,
        ]);
    }
}
