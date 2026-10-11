<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoContenido;
use App\Models\Configuracion;
use App\Models\Contenido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContenidoAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_agrega_edita_y_elimina_elementos_del_carrusel(): void
    {
        $this->actingAs($this->admin)->get(route('admin.contenidos.index'))->assertOk();

        $this->actingAs($this->admin)->post(route('admin.contenidos.store'), [
            'tipo' => 'carrusel',
            'titulo' => 'Bienvenidos',
            'cuerpo' => 'Nuevos cursos',
            'imagen' => UploadedFile::fake()->image('slide.jpg'),
        ])->assertSessionHasNoErrors();

        $slide = Contenido::sole();
        Storage::disk('public')->assertExists($slide->imagen_path);
        $anterior = $slide->imagen_path;

        $this->actingAs($this->admin)->put(route('admin.contenidos.update', $slide), [
            'titulo' => 'Bienvenidas y bienvenidos',
            'cuerpo' => 'Nuevos cursos',
            'imagen' => UploadedFile::fake()->image('slide2.jpg'),
        ])->assertSessionHasNoErrors();

        $slide->refresh();
        $this->assertSame('Bienvenidas y bienvenidos', $slide->titulo);
        Storage::disk('public')->assertMissing($anterior);

        $this->actingAs($this->admin)->delete(route('admin.contenidos.destroy', $slide));
        $this->assertModelMissing($slide);
        Storage::disk('public')->assertMissing($slide->imagen_path);
    }

    public function test_valida_campos_segun_el_tipo_y_bloques_unicos(): void
    {
        $this->actingAs($this->admin)->post(route('admin.contenidos.store'), ['tipo' => 'autoridad', 'titulo' => 'Decano'])
            ->assertSessionHasErrors('subtitulo');

        $this->actingAs($this->admin)->post(route('admin.contenidos.store'), [
            'tipo' => 'mision', 'cuerpo' => 'Misión', 'imagen' => UploadedFile::fake()->image('x.jpg'),
        ])->assertSessionHasErrors('imagen');

        Contenido::create(['tipo' => TipoContenido::Mision, 'cuerpo' => 'Existente']);
        $this->actingAs($this->admin)->post(route('admin.contenidos.store'), ['tipo' => 'mision', 'cuerpo' => 'Otra'])->assertStatus(422);
    }

    public function test_configuracion_se_guarda_y_se_muestra_en_el_pie(): void
    {
        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'contacto',
            'email_contacto' => 'contacto@uta.edu.ec',
            'telefono_contacto' => '032851894',
        ])->assertSessionHasNoErrors();

        $this->assertSame('contacto@uta.edu.ec', Configuracion::valor('email_contacto'));
        $this->get(route('home'))->assertSee('contacto@uta.edu.ec');
    }

    public function test_estudiantes_no_editan_el_sitio(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.contenidos.store'), ['tipo' => 'resena'])->assertForbidden();
        $this->actingAs(User::factory()->create())->put(route('admin.configuracion.update'), ['grupo' => 'identidad', 'nombre_institucion' => 'X'])->assertForbidden();
    }
}
