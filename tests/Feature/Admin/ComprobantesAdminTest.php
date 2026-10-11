<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoComprobante;
use App\Enums\EstadoInscripcion;
use App\Models\Comprobante;
use App\Models\Inscripcion;
use App\Models\User;
use App\Services\InscripcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComprobantesAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Comprobante $comprobante;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = User::factory()->admin()->create();
        $this->comprobante = app(InscripcionService::class)
            ->subirComprobante(Inscripcion::factory()->create(), UploadedFile::fake()->image('pago.jpg'));
    }

    public function test_dashboard_y_listado_solo_para_administradores(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Comprobantes por revisar');
        $this->actingAs($this->admin)->get(route('admin.comprobantes.index'))->assertOk()->assertSee($this->comprobante->inscripcion->user->cedula);

        $estudiante = User::factory()->create();
        $this->actingAs($estudiante)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($estudiante)->post(route('admin.comprobantes.aprobar', $this->comprobante))->assertForbidden();

        $this->assertSame(EstadoComprobante::Pendiente, $this->comprobante->fresh()->estado);
    }

    public function test_aprobar_confirma_la_inscripcion(): void
    {
        $this->actingAs($this->admin)->post(route('admin.comprobantes.aprobar', $this->comprobante))->assertSessionHas('status');

        $comprobante = $this->comprobante->fresh();
        $this->assertSame(EstadoComprobante::Aprobado, $comprobante->estado);
        $this->assertSame($this->admin->id, $comprobante->revisado_por);
        $this->assertSame(EstadoInscripcion::Pagado, $comprobante->inscripcion->estado);
    }

    public function test_rechazar_exige_motivo_y_devuelve_a_espera_de_pago(): void
    {
        $this->actingAs($this->admin)->post(route('admin.comprobantes.rechazar', $this->comprobante), ['motivo' => ''])
            ->assertSessionHasErrors('motivo');

        $this->actingAs($this->admin)->post(route('admin.comprobantes.rechazar', $this->comprobante), ['motivo' => 'El monto no coincide'])
            ->assertSessionHas('status');

        $this->assertSame(EstadoInscripcion::EnEsperaPago, $this->comprobante->inscripcion->fresh()->estado);
    }

    public function test_no_se_revisa_dos_veces(): void
    {
        $this->actingAs($this->admin)->post(route('admin.comprobantes.aprobar', $this->comprobante));
        $this->actingAs($this->admin)->post(route('admin.comprobantes.rechazar', $this->comprobante), ['motivo' => 'Intento tardío'])
            ->assertSessionHas('error');

        $this->assertSame(EstadoInscripcion::Pagado, $this->comprobante->inscripcion->fresh()->estado);
    }

    public function test_login_de_admin_redirige_al_panel(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }
}
