<?php

namespace Database\Factories;

use App\Models\TipoEvento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoEvento>
 */
class TipoEventoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
        ];
    }
}
