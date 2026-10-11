<?php

namespace Tests\Feature\Admin;

use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteEventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_genera_el_reporte_pdf_del_evento(): void
    {
        $evento = Evento::factory()->conCalificacion()->create();
        Inscripcion::factory()->count(3)->for($evento)->pagada()->create();

        $respuesta = $this->actingAs(User::factory()->admin()->create())->get(route('admin.eventos.reporte', $evento));

        $respuesta->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_solo_administradores(): void
    {
        $this->actingAs(User::factory()->docente()->create())
            ->get(route('admin.eventos.reporte', Evento::factory()->create()))
            ->assertForbidden();
    }
}
