<?php

namespace Tests\Feature;

use App\Enums\EstadoInscripcion;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificadoTest extends TestCase
{
    use RefreshDatabase;

    private function inscripcionAprobada(array $estadoEvento = []): Inscripcion
    {
        $evento = Evento::factory()->finalizado()->conCalificacion(7, 70)->create($estadoEvento);

        $inscripcion = Inscripcion::factory()->for($evento)->pagada()->create();
        $inscripcion->forceFill(['nota' => 8.5, 'asistencia' => 90])->save();

        return $inscripcion;
    }

    public function test_descarga_pdf_y_asigna_codigo_unico_estable(): void
    {
        $inscripcion = $this->inscripcionAprobada();

        $respuesta = $this->actingAs($inscripcion->user)->get(route('certificados.descargar', $inscripcion));

        $respuesta->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());

        $codigo = $inscripcion->fresh()->codigo_certificado;
        $this->assertMatchesRegularExpression('/^FISEI-\d{4}-[A-Z0-9]{8}$/', $codigo);

        $this->actingAs($inscripcion->user)->get(route('certificados.descargar', $inscripcion))->assertOk();
        $this->assertSame($codigo, $inscripcion->fresh()->codigo_certificado);
    }

    public function test_no_emite_sin_pago_o_sin_cumplir_minimos_o_con_evento_abierto(): void
    {
        $sinPago = $this->inscripcionAprobada();
        $sinPago->forceFill(['estado' => EstadoInscripcion::PendienteAprobacion])->save();
        $this->actingAs($sinPago->user)->get(route('certificados.descargar', $sinPago))->assertForbidden();

        $reprobada = $this->inscripcionAprobada();
        $reprobada->forceFill(['nota' => 5])->save();
        $this->actingAs($reprobada->user)->get(route('certificados.descargar', $reprobada))->assertForbidden();

        $abierto = $this->inscripcionAprobada(['estado' => 'abierto']);
        $this->actingAs($abierto->user)->get(route('certificados.descargar', $abierto))->assertForbidden();

        $this->assertDatabaseMissing('inscripciones', ['codigo_certificado' => null, 'id' => -1]);
        $this->assertSame(0, Inscripcion::whereNotNull('codigo_certificado')->count());
    }

    public function test_no_se_puede_descargar_el_certificado_de_otro(): void
    {
        $inscripcion = $this->inscripcionAprobada();

        $this->actingAs(User::factory()->create())->get(route('certificados.descargar', $inscripcion))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('certificados.descargar', $inscripcion))->assertOk();
    }

    public function test_verificacion_publica_del_codigo(): void
    {
        $inscripcion = $this->inscripcionAprobada();
        $this->actingAs($inscripcion->user)->get(route('certificados.descargar', $inscripcion));
        auth()->logout();

        $this->get(route('certificados.verificar', $inscripcion->fresh()->codigo_certificado))
            ->assertOk()
            ->assertSee('Certificado válido')
            ->assertSee($inscripcion->user->nombre_completo);

        $this->get(route('certificados.verificar', 'FISEI-2000-NOEXISTE'))->assertOk()->assertSee('No encontrado');
    }

    public function test_mis_inscripciones_muestra_el_boton_solo_si_corresponde(): void
    {
        $inscripcion = $this->inscripcionAprobada();

        $this->actingAs($inscripcion->user)->get(route('inscripciones.index'))->assertSee('Descargar certificado');

        $inscripcion->forceFill(['nota' => 3])->save();
        $this->actingAs($inscripcion->user)->get(route('inscripciones.index'))->assertDontSee('Descargar certificado');
    }
}
