<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogoEventosTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_de_inicio_muestra_contenido_y_proximos_eventos(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $evento = Evento::factory()->create(['nombre' => 'Curso de Laravel avanzado']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Misión')
            ->assertSee('Curso de Laravel avanzado')
            ->assertSee(route('eventos.show', $evento));
    }

    public function test_el_seeder_es_idempotente(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('categorias', 5);
        $this->assertSame(1, \App\Models\Contenido::where('tipo', 'mision')->count());
    }

    public function test_invitado_solo_ve_eventos_publicos_y_visibles(): void
    {
        $publico = Evento::factory()->create(['nombre' => 'Evento Publico']);
        $privado = Evento::factory()->privado()->create(['nombre' => 'Evento De Carrera']);
        $borrador = Evento::factory()->borrador()->create(['nombre' => 'Evento Borrador']);

        $this->get(route('eventos.index'))
            ->assertOk()
            ->assertSee('Evento Publico')
            ->assertDontSee('Evento De Carrera')
            ->assertDontSee('Evento Borrador');

        $this->get(route('eventos.show', $publico))->assertOk();
        $this->get(route('eventos.show', $privado))->assertForbidden();
        $this->get(route('eventos.show', $borrador))->assertForbidden();
    }

    public function test_estudiante_institucional_ve_eventos_de_su_carrera(): void
    {
        $software = Categoria::factory()->create();
        $industrial = Categoria::factory()->create();

        $deSoftware = Evento::factory()->privado()->create(['nombre' => 'Solo Software']);
        $deSoftware->categorias()->attach($software);
        $deIndustrial = Evento::factory()->privado()->create(['nombre' => 'Solo Industrial']);
        $deIndustrial->categorias()->attach($industrial);

        $estudiante = User::factory()->institucional($software)->create();

        $this->actingAs($estudiante)
            ->get(route('eventos.index'))
            ->assertSee('Solo Software')
            ->assertDontSee('Solo Industrial');

        $this->actingAs($estudiante)->get(route('eventos.show', $deIndustrial))->assertForbidden();
    }

    public function test_administrador_ve_todos_los_eventos(): void
    {
        $borrador = Evento::factory()->borrador()->privado()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('eventos.show', $borrador))
            ->assertOk();
    }

    public function test_filtra_por_texto(): void
    {
        Evento::factory()->create(['nombre' => 'Introducción a Python']);
        Evento::factory()->create(['nombre' => 'Redes industriales']);

        $this->get(route('eventos.index', ['q' => 'Python']))
            ->assertSee('Introducción a Python')
            ->assertDontSee('Redes industriales');
    }

    public function test_la_pagina_de_creditos_se_muestra(): void
    {
        $this->get(route('creditos'))->assertOk()->assertSee('Johan Rodríguez');
    }
}
