<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoEvento;
use App\Enums\TipoRequisito;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\TipoEvento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventosAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    private function datos(array $extra = []): array
    {
        return [
            'nombre' => 'Curso de Laravel',
            'tipo_evento_id' => TipoEvento::factory()->create()->id,
            'ponentes' => 'Ing. Ana Pérez',
            'descripcion' => 'Desarrollo web moderno.',
            'modalidad' => 'virtual',
            'estado' => 'abierto',
            'fecha_inicio_inscripciones' => now()->toDateString(),
            'fecha_fin_inscripciones' => now()->addDays(5)->toDateString(),
            'fecha_inicio' => now()->addDays(6)->toDateString(),
            'fecha_fin' => now()->addDays(20)->toDateString(),
            'horas' => 40,
            'cupos' => 25,
            'costo' => '30.00',
            'es_publico' => '1',
            'requiere_nota' => '1',
            'nota_minima' => '7',
            'requiere_asistencia' => '0',
            ...$extra,
        ];
    }

    public function test_crea_evento_con_carreras_requisitos_e_imagen(): void
    {
        $carrera = Categoria::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.eventos.store'), $this->datos([
            'es_publico' => '0',
            'categorias' => [$carrera->id],
            'imagen' => UploadedFile::fake()->image('banner.png', 1200, 600),
            'requisitos' => [
                ['tipo' => 'documento', 'tipo_documento' => 'cedula', 'descripcion' => 'Copia de cédula'],
                ['tipo' => 'texto', 'descripcion' => 'Carta de motivación'],
            ],
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $evento = Evento::sole();
        $this->assertSame(EstadoEvento::Abierto, $evento->estado);
        $this->assertFalse($evento->es_publico);
        $this->assertSame([$carrera->id], $evento->categorias->pluck('id')->all());
        $this->assertCount(2, $evento->requisitos);
        $this->assertNull($evento->requisitos->firstWhere('tipo', TipoRequisito::Texto)->tipo_documento);
        $this->assertNull($evento->asistencia_minima);
        Storage::disk('public')->assertExists($evento->imagen_path);
    }

    public function test_valida_fechas_y_destinatarios(): void
    {
        $this->actingAs($this->admin)->post(route('admin.eventos.store'), $this->datos([
            'fecha_fin_inscripciones' => now()->addDays(10)->toDateString(),
            'fecha_fin' => now()->toDateString(),
            'es_publico' => '0',
            'categorias' => [],
            'nota_minima' => '15',
        ]))->assertSessionHasErrors(['fecha_fin_inscripciones', 'fecha_fin', 'categorias', 'nota_minima']);

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_rechaza_imagenes_que_no_son_imagenes(): void
    {
        $this->actingAs($this->admin)->post(route('admin.eventos.store'), $this->datos([
            'imagen' => UploadedFile::fake()->create('banner.svg', 5, 'image/svg+xml'),
        ]))->assertSessionHasErrors('imagen');
    }

    public function test_actualiza_y_reemplaza_la_imagen(): void
    {
        $this->actingAs($this->admin)->post(route('admin.eventos.store'), $this->datos(['imagen' => UploadedFile::fake()->image('a.png')]));
        $evento = Evento::sole();
        $anterior = $evento->imagen_path;

        $this->actingAs($this->admin)->put(route('admin.eventos.update', $evento), $this->datos([
            'nombre' => 'Curso de Laravel 13',
            'tipo_evento_id' => $evento->tipo_evento_id,
            'imagen' => UploadedFile::fake()->image('b.png'),
        ]))->assertSessionHasNoErrors();

        $evento->refresh();
        $this->assertSame('Curso de Laravel 13', $evento->nombre);
        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists($evento->imagen_path);
    }

    public function test_no_reduce_cupos_por_debajo_de_los_ocupados_ni_cambia_requisitos(): void
    {
        $evento = Evento::factory()->conCupos(5)->create();
        $evento->requisitos()->create(['tipo' => TipoRequisito::Texto, 'descripcion' => 'Carta']);
        Inscripcion::factory()->count(3)->for($evento)->create();

        $this->actingAs($this->admin)->put(route('admin.eventos.update', $evento), $this->datos([
            'tipo_evento_id' => $evento->tipo_evento_id,
            'cupos' => 2,
        ]))->assertSessionHasErrors('cupos');

        $this->actingAs($this->admin)->put(route('admin.eventos.update', $evento), $this->datos([
            'tipo_evento_id' => $evento->tipo_evento_id,
            'cupos' => 3,
            'requisitos' => [],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(3, $evento->fresh()->cupos);
        $this->assertCount(1, $evento->requisitos()->get());
    }

    public function test_cambia_estado_y_bloquea_edicion_de_eventos_finalizados(): void
    {
        $evento = Evento::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.eventos.estado', $evento), ['estado' => 'finalizado'])->assertSessionHas('status');
        $this->assertSame(EstadoEvento::Finalizado, $evento->fresh()->estado);

        $this->actingAs($this->admin)->get(route('admin.eventos.edit', $evento))->assertRedirect(route('admin.eventos.show', $evento));
        $this->actingAs($this->admin)->put(route('admin.eventos.update', $evento), $this->datos(['tipo_evento_id' => $evento->tipo_evento_id]))->assertForbidden();
    }

    public function test_solo_elimina_eventos_sin_inscripciones(): void
    {
        $conInscritos = Evento::factory()->create();
        Inscripcion::factory()->for($conInscritos)->create();
        $vacio = Evento::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.eventos.destroy', $conInscritos))->assertSessionHas('error');
        $this->assertNotSoftDeleted($conInscritos);

        $this->actingAs($this->admin)->delete(route('admin.eventos.destroy', $vacio))->assertRedirect(route('admin.eventos.index'));
        $this->assertSoftDeleted($vacio);
    }

    public function test_paginas_de_gestion_cargan(): void
    {
        $evento = Evento::factory()->create();
        Inscripcion::factory()->for($evento)->create();

        $this->actingAs($this->admin)->get(route('admin.eventos.index'))->assertOk()->assertSee($evento->nombre);
        $this->actingAs($this->admin)->get(route('admin.eventos.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.eventos.show', $evento))->assertOk()->assertSee('Inscritos');
        $this->actingAs($this->admin)->get(route('admin.eventos.edit', $evento))->assertOk()->assertSee('los requisitos no se pueden modificar');
    }

    public function test_estudiantes_no_gestionan_eventos(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.eventos.store'), $this->datos())->assertForbidden();
        $this->assertDatabaseCount('eventos', 0);
    }
}
