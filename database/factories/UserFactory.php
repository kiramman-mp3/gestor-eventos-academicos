<?php

namespace Database\Factories;

use App\Enums\RolUsuario;
use App\Enums\TipoUsuario;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'cedula' => self::cedulaValida(),
            'telefono' => '09'.fake()->numerify('########'),
            'genero' => fake()->randomElement(['Masculino', 'Femenino', 'Otro']),
            'fecha_nacimiento' => fake()->dateTimeBetween('-40 years', '-18 years'),
            'tipo' => TipoUsuario::Publico,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['rol' => RolUsuario::Administrador]);
    }

    public function docente(): static
    {
        return $this->state(fn () => ['rol' => RolUsuario::Docente]);
    }

    public function institucional(?Categoria $categoria = null): static
    {
        return $this->state(fn () => [
            'tipo' => TipoUsuario::Institucional,
            'email' => fake()->unique()->userName().'@'.TipoUsuario::DOMINIO_INSTITUCIONAL,
            'categoria_id' => $categoria ?? Categoria::factory(),
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }

    /** Genera una cédula ecuatoriana con dígito verificador correcto. */
    public static function cedulaValida(): string
    {
        $base = str_pad((string) random_int(1, 24), 2, '0', STR_PAD_LEFT)
            .random_int(0, 5)
            .str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $suma = 0;
        foreach (str_split($base) as $i => $digito) {
            $valor = (int) $digito * ($i % 2 === 0 ? 2 : 1);
            $suma += $valor > 9 ? $valor - 9 : $valor;
        }

        return $base.((10 - $suma % 10) % 10);
    }
}
