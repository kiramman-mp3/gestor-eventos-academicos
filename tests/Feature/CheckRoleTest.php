<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:administrador'])->get('/_prueba/admin', fn () => 'ok');
        Route::middleware(['web', 'auth', 'role:administrador,docente'])->get('/_prueba/gestion', fn () => 'ok');
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get('/_prueba/admin')->assertRedirect(route('login'));
    }

    public function test_estudiante_recibe_403(): void
    {
        $this->actingAs(User::factory()->create())->get('/_prueba/admin')->assertForbidden();
    }

    public function test_administrador_accede(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/_prueba/admin')->assertOk();
    }

    public function test_varios_roles_permitidos(): void
    {
        $this->actingAs(User::factory()->docente()->create())->get('/_prueba/gestion')->assertOk();
        $this->actingAs(User::factory()->docente()->create())->get('/_prueba/admin')->assertForbidden();
    }
}
