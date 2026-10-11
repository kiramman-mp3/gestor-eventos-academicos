<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotasTest extends TestCase
{
    use RefreshDatabase;

    public function test_docente_asignado_califica_y_reprobar_no_es_error(): void
    {
        $docente = User::factory()->docente()->create();
        $evento = Evento::factory()->conCalificacion(7, 70)->create(['docente_id' => $docente->id]);
        $aprobada = Inscripcion::factory()->for($evento)->pagada()->create();
        $reprobada = Inscripcion::factory()->for($evento)->pagada()->create();

        $this->actingAs($docente)->get(route('dashboard'))->assertRedirect(route('gestion.notas.index'));
        $this->actingAs($docente)->get(route('gestion.notas.index'))->assertOk()->assertSee($evento->nombre);
        $this->actingAs($docente)->get(route('gestion.notas.edit', $evento))->assertOk();

        $this->actingAs($docente)->put(route('gestion.notas.update', $evento), ['inscripciones' => [
            $aprobada->id => ['nota' => '9.5', 'asistencia' => '95', 'legalizado' => '1'],
            $reprobada->id => ['nota' => '4', 'asistencia' => '50', 'legalizado' => '0'],
        ]])->assertSessionHasNoErrors();

        $this->assertTrue($aprobada->fresh()->aptaParaCertificado());
        $this->assertTrue($aprobada->fresh()->legalizado);
        $this->assertFalse($reprobada->fresh()->aptaParaCertificado());
        $this->assertEquals(4, $reprobada->fresh()->nota);
    }

    public function test_valida_rangos(): void
    {
        $admin = User::factory()->admin()->create();
        $evento = Evento::factory()->conCalificacion()->create();
        $inscripcion = Inscripcion::factory()->for($evento)->pagada()->create();

        $this->actingAs($admin)->put(route('gestion.notas.update', $evento), ['inscripciones' => [
            $inscripcion->id => ['nota' => '11', 'asistencia' => '101'],
        ]])->assertSessionHasErrors(['inscripciones.'.$inscripcion->id.'.nota', 'inscripciones.'.$inscripcion->id.'.asistencia']);
    }

    public function test_docente_no_asignado_y_estudiantes_no_califican(): void
    {
        $evento = Evento::factory()->conCalificacion()->create(['docente_id' => User::factory()->docente()]);
        $inscripcion = Inscripcion::factory()->for($evento)->pagada()->create();
        $payload = ['inscripciones' => [$inscripcion->id => ['nota' => '10', 'asistencia' => '100']]];

        $this->actingAs(User::factory()->docente()->create())->put(route('gestion.notas.update', $evento), $payload)->assertForbidden();
        $this->actingAs($inscripcion->user)->put(route('gestion.notas.update', $evento), $payload)->assertForbidden();

        $this->assertNull($inscripcion->fresh()->nota);
    }

    public function test_ignora_inscripciones_de_otros_eventos_o_sin_pago(): void
    {
        $admin = User::factory()->admin()->create();
        $evento = Evento::factory()->conCalificacion()->create();
        $ajena = Inscripcion::factory()->pagada()->for(Evento::factory()->conCalificacion())->create();
        $sinPago = Inscripcion::factory()->for($evento)->create();

        $this->actingAs($admin)->put(route('gestion.notas.update', $evento), ['inscripciones' => [
            $ajena->id => ['nota' => '10', 'asistencia' => '100'],
            $sinPago->id => ['nota' => '10', 'asistencia' => '100'],
        ]])->assertSessionHasNoErrors();

        $this->assertNull($ajena->fresh()->nota);
        $this->assertNull($sinPago->fresh()->nota);
    }
}
