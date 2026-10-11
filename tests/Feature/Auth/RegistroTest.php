<?php

namespace Tests\Feature\Auth;

use App\Enums\RolUsuario;
use App\Enums\TipoUsuario;
use App\Models\Categoria;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistroTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(array $extra = []): array
    {
        return [
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'cedula' => UserFactory::cedulaValida(),
            'telefono' => '0991234567',
            'genero' => 'Femenino',
            'fecha_nacimiento' => now()->subYears(20)->toDateString(),
            'email' => 'ana@example.com',
            'password' => 'Secreta.123',
            'password_confirmation' => 'Secreta.123',
            ...$extra,
        ];
    }

    public function test_la_pagina_de_registro_se_muestra(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Crear cuenta');
    }

    public function test_registro_publico_crea_estudiante_y_envia_verificacion(): void
    {
        Notification::fake();

        $this->post(route('register'), $this->datosValidos())
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertSame(RolUsuario::Estudiante, $user->rol);
        $this->assertSame(TipoUsuario::Publico, $user->tipo);
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_no_se_puede_registrar_como_administrador(): void
    {
        $this->post(route('register'), $this->datosValidos(['rol' => 'administrador']));

        $this->assertSame(RolUsuario::Estudiante, User::where('email', 'ana@example.com')->first()->rol);
    }

    public function test_correo_institucional_exige_carrera(): void
    {
        $this->post(route('register'), $this->datosValidos(['email' => 'ana@uta.edu.ec']))
            ->assertSessionHasErrors('categoria_id');

        $categoria = Categoria::factory()->create();

        $this->post(route('register'), $this->datosValidos(['email' => 'ana@uta.edu.ec', 'categoria_id' => $categoria->id]))
            ->assertSessionHasNoErrors();

        $user = User::where('email', 'ana@uta.edu.ec')->firstOrFail();
        $this->assertSame(TipoUsuario::Institucional, $user->tipo);
        $this->assertSame($categoria->id, $user->categoria_id);
    }

    public function test_correo_publico_no_puede_declarar_carrera(): void
    {
        $categoria = Categoria::factory()->create();

        $this->post(route('register'), $this->datosValidos(['categoria_id' => $categoria->id]))
            ->assertSessionHasErrors('categoria_id');
    }

    public function test_valida_cedula_telefono_edad_y_contrasena(): void
    {
        $this->post(route('register'), $this->datosValidos([
            'cedula' => '1234567890',
            'telefono' => '0812345678',
            'fecha_nacimiento' => now()->subYears(10)->toDateString(),
            'password' => 'debil',
            'password_confirmation' => 'debil',
        ]))->assertSessionHasErrors(['cedula', 'telefono', 'fecha_nacimiento', 'password']);

        $this->assertGuest();
    }

    public function test_no_permite_correo_ni_cedula_duplicados(): void
    {
        $existente = User::factory()->create();

        $this->post(route('register'), $this->datosValidos([
            'email' => $existente->email,
            'cedula' => $existente->cedula,
        ]))->assertSessionHasErrors(['email', 'cedula']);
    }
}
