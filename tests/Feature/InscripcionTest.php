<?php

namespace Tests\Feature;

use App\Enums\EstadoComprobante;
use App\Enums\EstadoInscripcion;
use App\Enums\TipoDocumento;
use App\Enums\TipoRequisito;
use App\Exceptions\InscripcionException;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use App\Services\InscripcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InscripcionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_estudiante_se_inscribe_en_evento_de_pago(): void
    {
        $estudiante = User::factory()->create();
        $evento = Evento::factory()->create();
        $requisito = $evento->requisitos()->create(['tipo' => TipoRequisito::Texto, 'descripcion' => 'Carta de motivación']);

        $this->actingAs($estudiante)
            ->post(route('inscripciones.store', $evento), ['respuestas' => [$requisito->id => 'Quiero aprender Laravel']])
            ->assertRedirect(route('inscripciones.index'));

        $inscripcion = Inscripcion::sole();
        $this->assertSame(EstadoInscripcion::EnEsperaPago, $inscripcion->estado);
        $this->assertSame('Quiero aprender Laravel', $inscripcion->respuestas()->sole()->texto);
    }

    public function test_evento_gratuito_confirma_directamente(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('inscripciones.store', Evento::factory()->gratuito()->create()));

        $this->assertSame(EstadoInscripcion::Pagado, Inscripcion::sole()->estado);
    }

    public function test_requisito_de_texto_es_obligatorio(): void
    {
        $evento = Evento::factory()->create();
        $evento->requisitos()->create(['tipo' => TipoRequisito::Texto, 'descripcion' => 'Carta de motivación']);

        $this->actingAs(User::factory()->create())
            ->post(route('inscripciones.store', $evento), ['respuestas' => []])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('inscripciones', 0);
    }

    public function test_requisito_de_documento_se_verifica_contra_el_perfil(): void
    {
        $estudiante = User::factory()->create();
        $evento = Evento::factory()->create();
        $evento->requisitos()->create(['tipo' => TipoRequisito::Documento, 'tipo_documento' => TipoDocumento::Cedula, 'descripcion' => 'Copia de cédula']);

        $this->actingAs($estudiante)->post(route('inscripciones.store', $evento))->assertSessionHas('error');
        $this->assertDatabaseCount('inscripciones', 0);

        $estudiante->documentos()->create(['tipo' => TipoDocumento::Cedula, 'path' => 'x.pdf', 'nombre_original' => 'x.pdf', 'mime' => 'application/pdf', 'tamano' => 1]);

        $this->actingAs($estudiante->fresh())->post(route('inscripciones.store', $evento))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('inscripciones', 1);
    }

    public function test_no_permite_inscribirse_dos_veces(): void
    {
        $estudiante = User::factory()->create();
        $evento = Evento::factory()->create();

        $this->actingAs($estudiante)->post(route('inscripciones.store', $evento));
        $this->actingAs($estudiante)->post(route('inscripciones.store', $evento))
            ->assertSessionHas('error', InscripcionException::yaInscrito()->getMessage());

        $this->assertDatabaseCount('inscripciones', 1);
    }

    public function test_respeta_los_cupos_y_las_anuladas_no_cuentan(): void
    {
        $evento = Evento::factory()->conCupos(2)->create();
        Inscripcion::factory()->for($evento)->create();
        Inscripcion::factory()->for($evento)->estado(EstadoInscripcion::Error)->create();

        $this->actingAs(User::factory()->create())->post(route('inscripciones.store', $evento))->assertSessionHasNoErrors();
        $this->actingAs(User::factory()->create())->post(route('inscripciones.store', $evento))
            ->assertSessionHas('error', InscripcionException::sinCupos()->getMessage());

        $this->assertSame(2, $evento->cuposOcupados());
    }

    public function test_el_servicio_nunca_sobrepasa_los_cupos(): void
    {
        $evento = Evento::factory()->conCupos(3)->create();
        $servicio = app(InscripcionService::class);
        $rechazadas = 0;

        foreach (User::factory()->count(5)->create() as $estudiante) {
            try {
                $servicio->inscribir($estudiante, $evento);
            } catch (InscripcionException) {
                $rechazadas++;
            }
        }

        $this->assertSame(3, Inscripcion::count());
        $this->assertSame(2, $rechazadas);
    }

    public function test_no_puede_inscribirse_fuera_de_plazo_ni_en_otra_carrera(): void
    {
        $estudiante = User::factory()->institucional()->create();

        $this->actingAs($estudiante)->post(route('inscripciones.store', Evento::factory()->inscripcionesCerradas()->create()))->assertForbidden();

        $otraCarrera = Evento::factory()->privado()->create();
        $otraCarrera->categorias()->attach(Categoria::factory()->create());
        $this->actingAs($estudiante)->post(route('inscripciones.store', $otraCarrera))->assertForbidden();

        $suCarrera = Evento::factory()->privado()->create();
        $suCarrera->categorias()->attach($estudiante->categoria_id);
        $this->actingAs($estudiante)->post(route('inscripciones.store', $suCarrera))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('inscripciones', 1);
    }

    public function test_administradores_y_docentes_no_se_inscriben(): void
    {
        $evento = Evento::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->post(route('inscripciones.store', $evento))->assertForbidden();
        $this->actingAs(User::factory()->docente()->create())->post(route('inscripciones.store', $evento))->assertForbidden();
    }

    public function test_sube_comprobante_privado_y_pasa_a_revision(): void
    {
        $inscripcion = Inscripcion::factory()->create();

        $this->actingAs($inscripcion->user)
            ->post(route('inscripciones.comprobante', $inscripcion), ['comprobante' => UploadedFile::fake()->image('pago.jpg')])
            ->assertSessionHasNoErrors();

        $inscripcion->refresh();
        $comprobante = $inscripcion->comprobantes()->sole();
        $this->assertSame(EstadoInscripcion::PendienteAprobacion, $inscripcion->estado);
        $this->assertSame(EstadoComprobante::Pendiente, $comprobante->estado);
        Storage::disk('local')->assertExists($comprobante->path);

        // Ya en revisión: no puede subir otro
        $this->actingAs($inscripcion->user)
            ->post(route('inscripciones.comprobante', $inscripcion), ['comprobante' => UploadedFile::fake()->image('otro.jpg')])
            ->assertForbidden();
    }

    public function test_no_puede_subir_ni_ver_comprobantes_ajenos(): void
    {
        $inscripcion = Inscripcion::factory()->create();
        $intruso = User::factory()->create();

        $this->actingAs($intruso)
            ->post(route('inscripciones.comprobante', $inscripcion), ['comprobante' => UploadedFile::fake()->image('pago.jpg')])
            ->assertForbidden();

        $comprobante = app(InscripcionService::class)->subirComprobante($inscripcion, UploadedFile::fake()->image('pago.jpg'));

        $this->actingAs($intruso)->get(route('comprobantes.show', $comprobante))->assertForbidden();
        $this->actingAs($inscripcion->user)->get(route('comprobantes.show', $comprobante))->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get(route('comprobantes.show', $comprobante))->assertOk();
    }

    public function test_aprobar_y_rechazar_comprobantes_respeta_las_transiciones(): void
    {
        $servicio = app(InscripcionService::class);
        $admin = User::factory()->admin()->create();

        $inscripcion = Inscripcion::factory()->create();
        $comprobante = $servicio->subirComprobante($inscripcion, UploadedFile::fake()->image('pago.jpg'));

        $servicio->rechazarComprobante($comprobante, $admin, 'Monto incorrecto');
        $this->assertSame(EstadoInscripcion::EnEsperaPago, $inscripcion->fresh()->estado);
        $this->assertSame('Monto incorrecto', $comprobante->fresh()->motivo_rechazo);

        // Un comprobante ya revisado no se puede volver a revisar
        $this->expectException(InscripcionException::class);
        $servicio->aprobarComprobante($comprobante->fresh(), $admin);
    }

    public function test_mis_inscripciones_lista_solo_las_propias(): void
    {
        $estudiante = User::factory()->create();
        Inscripcion::factory()->for($estudiante)->for(Evento::factory()->state(['nombre' => 'Mi curso']))->create();
        Inscripcion::factory()->for(Evento::factory()->state(['nombre' => 'Curso ajeno']))->create();

        $this->actingAs($estudiante)->get(route('inscripciones.index'))
            ->assertOk()
            ->assertSee('Mi curso')
            ->assertDontSee('Curso ajeno');
    }
}
