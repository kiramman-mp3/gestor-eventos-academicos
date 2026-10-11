<?php

namespace Tests\Feature\Admin;

use App\Enums\RolUsuario;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuariosAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_lista_y_busca_usuarios(): void
    {
        User::factory()->create(['apellido' => 'Zambrano']);
        User::factory()->create(['apellido' => 'Altamirano']);

        $this->actingAs($this->admin)->get(route('admin.usuarios.index', ['q' => 'Zambrano']))
            ->assertOk()
            ->assertSee('Zambrano')
            ->assertDontSee('Altamirano');
    }

    public function test_crea_docente_verificado(): void
    {
        $this->actingAs($this->admin)->post(route('admin.usuarios.store'), [
            'nombre' => 'Luis',
            'apellido' => 'Torres',
            'cedula' => UserFactory::cedulaValida(),
            'email' => 'ltorres@uta.edu.ec',
            'rol' => 'docente',
            'password' => 'Docente.2026',
            'password_confirmation' => 'Docente.2026',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.usuarios.index'));

        $docente = User::where('email', 'ltorres@uta.edu.ec')->sole();
        $this->assertSame(RolUsuario::Docente, $docente->rol);
        $this->assertTrue($docente->hasVerifiedEmail());
    }

    public function test_no_crea_estudiantes_ni_roles_inventados_desde_el_panel(): void
    {
        $this->actingAs($this->admin)->post(route('admin.usuarios.store'), ['rol' => 'superadmin'])
            ->assertSessionHasErrors('rol');
    }

    public function test_cambia_rol_y_desactiva(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.usuarios.rol', $usuario), ['rol' => 'docente']);
        $this->assertSame(RolUsuario::Docente, $usuario->fresh()->rol);

        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $usuario));
        $this->assertFalse($usuario->fresh()->activo);
    }

    public function test_no_puede_modificarse_a_si_mismo(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.usuarios.rol', $this->admin), ['rol' => 'estudiante'])->assertForbidden();
        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $this->admin))->assertForbidden();
        $this->actingAs($this->admin)->delete(route('admin.usuarios.destroy', $this->admin))->assertForbidden();

        $this->assertTrue($this->admin->fresh()->esAdmin());
        $this->assertTrue($this->admin->fresh()->activo);

        // Otro administrador sí puede desactivarlo
        $this->actingAs(User::factory()->admin()->create())->patch(route('admin.usuarios.activo', $this->admin));
        $this->assertFalse($this->admin->fresh()->activo);
    }

    public function test_elimina_con_soft_delete(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.usuarios.destroy', $usuario));

        $this->assertSoftDeleted($usuario);
    }

    public function test_estudiantes_no_acceden(): void
    {
        $estudiante = User::factory()->create();

        $this->actingAs($estudiante)->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($estudiante)->patch(route('admin.usuarios.rol', $estudiante), ['rol' => 'administrador'])->assertForbidden();

        $this->assertSame(RolUsuario::Estudiante, $estudiante->fresh()->rol);
    }
}
