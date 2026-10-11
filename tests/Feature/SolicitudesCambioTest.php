<?php

namespace Tests\Feature;

use App\Enums\EstadoSolicitud;
use App\Models\SolicitudCambio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SolicitudesCambioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_el_solicitante_sale_de_la_sesion_no_del_formulario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create();

        $this->actingAs($user)->get(route('solicitudes.create'))->assertOk();
        $this->actingAs($user)->post(route('solicitudes.store'), [
            'titulo' => 'Agregar filtro por fecha',
            'tipo' => 'Mejora',
            'descripcion' => 'Filtrar el catálogo por mes.',
            'user_id' => $otro->id,
            'captura' => UploadedFile::fake()->image('pantalla.png'),
        ])->assertRedirect(route('solicitudes.index'));

        $solicitud = SolicitudCambio::sole();
        $this->assertSame($user->id, $solicitud->user_id);
        Storage::disk('local')->assertExists($solicitud->captura_path);

        $this->actingAs($user)->get(route('solicitudes.index'))->assertSee('Agregar filtro por fecha');
        $this->actingAs($otro)->get(route('solicitudes.index'))->assertDontSee('Agregar filtro por fecha');
    }

    public function test_captura_privada_solo_para_autor_y_admin(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('solicitudes.store'), [
            'titulo' => 'Error', 'tipo' => 'Corrección de error', 'descripcion' => 'Falla al guardar',
            'captura' => UploadedFile::fake()->image('error.png'),
        ]);
        $solicitud = SolicitudCambio::sole();

        $this->actingAs($user)->get(route('solicitudes.captura', $solicitud))->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get(route('solicitudes.captura', $solicitud))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('solicitudes.captura', $solicitud))->assertForbidden();
    }

    public function test_admin_resuelve_y_actualiza_la_resolucion(): void
    {
        $admin = User::factory()->admin()->create();
        $solicitud = new SolicitudCambio(['titulo' => 'Cambio', 'tipo' => 'Mejora', 'descripcion' => 'Detalle']);
        $solicitud->user()->associate(User::factory()->create())->save();

        $this->actingAs($admin)->get(route('admin.solicitudes.index'))->assertOk()->assertSee('Cambio');
        $this->actingAs($admin)->get(route('admin.solicitudes.show', $solicitud))->assertOk();

        $this->actingAs($admin)->put(route('admin.solicitudes.resolver', $solicitud), [
            'prioridad' => 'alta', 'estado' => 'en_progreso', 'comentario' => 'En desarrollo',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->put(route('admin.solicitudes.resolver', $solicitud), [
            'prioridad' => 'alta', 'estado' => 'terminado', 'comentario' => 'Publicado',
        ]);

        $this->assertDatabaseCount('resoluciones', 1);
        $this->assertSame(EstadoSolicitud::Terminado, $solicitud->resolucion()->sole()->estado);
        $this->assertSame($admin->id, $solicitud->resolucion->resuelto_por);
    }

    public function test_estudiantes_no_resuelven(): void
    {
        $user = User::factory()->create();
        $solicitud = new SolicitudCambio(['titulo' => 'Cambio', 'tipo' => 'Mejora', 'descripcion' => 'Detalle']);
        $solicitud->user()->associate($user)->save();

        $this->actingAs($user)->put(route('admin.solicitudes.resolver', $solicitud), [
            'prioridad' => 'alta', 'estado' => 'terminado', 'comentario' => 'Autoaprobado',
        ])->assertForbidden();
    }
}
