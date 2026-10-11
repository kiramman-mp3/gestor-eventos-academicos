<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Evento;
use App\Models\TipoEvento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogosAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_crud_de_carreras(): void
    {
        $this->actingAs($this->admin)->get(route('admin.categorias.index'))->assertOk();

        $this->actingAs($this->admin)->post(route('admin.categorias.store'), ['nombre' => 'Mecatrónica'])->assertSessionHasNoErrors();
        $categoria = Categoria::where('nombre', 'Mecatrónica')->sole();

        $this->actingAs($this->admin)->post(route('admin.categorias.store'), ['nombre' => 'Mecatrónica'])->assertSessionHasErrors('nombre');

        $this->actingAs($this->admin)->put(route('admin.categorias.update', $categoria), ['nombre' => 'Mecatrónica Industrial']);
        $this->assertSame('Mecatrónica Industrial', $categoria->fresh()->nombre);

        $this->actingAs($this->admin)->delete(route('admin.categorias.destroy', $categoria));
        $this->assertModelMissing($categoria);
    }

    public function test_no_elimina_carreras_ni_tipos_en_uso(): void
    {
        $categoria = Categoria::factory()->create();
        User::factory()->institucional($categoria)->create();
        $tipo = TipoEvento::factory()->create();
        Evento::factory()->for($tipo, 'tipoEvento')->create();

        $this->actingAs($this->admin)->delete(route('admin.categorias.destroy', $categoria))->assertSessionHas('error');
        $this->actingAs($this->admin)->delete(route('admin.tipos-evento.destroy', $tipo))->assertSessionHas('error');

        $this->assertModelExists($categoria);
        $this->assertModelExists($tipo);
    }

    public function test_crud_de_tipos_de_evento(): void
    {
        $this->actingAs($this->admin)->get(route('admin.tipos-evento.index'))->assertOk();
        $this->actingAs($this->admin)->post(route('admin.tipos-evento.store'), ['nombre' => 'Hackatón'])->assertSessionHasNoErrors();

        $tipo = TipoEvento::where('nombre', 'Hackatón')->sole();
        $this->actingAs($this->admin)->put(route('admin.tipos-evento.update', $tipo), ['nombre' => 'Hackathon']);
        $this->assertSame('Hackathon', $tipo->fresh()->nombre);
    }

    public function test_estudiantes_no_acceden(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.categorias.store'), ['nombre' => 'X'])->assertForbidden();
        $this->assertDatabaseCount('categorias', 0);
    }
}
