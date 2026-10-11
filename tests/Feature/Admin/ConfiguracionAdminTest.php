<?php

namespace Tests\Feature\Admin;

use App\Exceptions\InscripcionException;
use App\Models\Configuracion;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use App\Services\InscripcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConfiguracionAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_muestra_todas_las_secciones(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.configuracion.edit'))->assertOk();

        foreach (Configuracion::GRUPOS as $grupo) {
            $respuesta->assertSee($grupo['titulo']);
        }
    }

    public function test_usa_valores_por_defecto_hasta_que_se_guardan(): void
    {
        $this->assertSame('Ver más', Configuracion::valor('carrusel_boton'));
        $this->assertTrue(Configuracion::activo('mostrar_resenas'));
        $this->assertSame(asset('images/logo-fisei.png'), Configuracion::imagenUrl('logo'));
    }

    public function test_guarda_solo_el_grupo_enviado_y_valida_sus_reglas(): void
    {
        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'redes',
            'facebook_url' => 'no-es-url',
        ])->assertSessionHasErrors('facebook_url');

        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'redes',
            'facebook_url' => 'https://facebook.com/fisei',
            'nombre_institucion' => 'Ignorado: pertenece a otro grupo',
        ])->assertRedirect(route('admin.configuracion.edit', ['seccion' => 'redes']));

        $this->assertSame('https://facebook.com/fisei', Configuracion::valor('facebook_url'));
        $this->assertDatabaseMissing('configuraciones', ['clave' => 'nombre_institucion']);

        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), ['grupo' => 'inexistente'])
            ->assertSessionHasErrors('grupo');
    }

    public function test_sube_y_restaura_el_logo(): void
    {
        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'identidad',
            'nombre_institucion' => 'FISEI',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertSessionHasNoErrors();

        $path = Configuracion::guardados()['logo'];
        Storage::disk('public')->assertExists($path);
        $this->get(route('home'))->assertSee(Storage::disk('public')->url($path));

        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'identidad',
            'nombre_institucion' => 'FISEI',
            'quitar_logo' => '1',
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($path);
        $this->assertSame(asset('images/logo-fisei.png'), Configuracion::imagenUrl('logo'));
    }

    public function test_portada_respeta_secciones_ocultas_y_aviso(): void
    {
        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'aviso',
            'aviso_activo' => '1',
        ])->assertSessionHasErrors('aviso_texto');

        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'aviso',
            'aviso_activo' => '1',
            'aviso_texto' => 'Inscripciones abiertas hasta el viernes',
            'aviso_estilo' => 'alerta',
        ])->assertSessionHasNoErrors();

        $evento = Evento::factory()->create(['nombre' => 'Taller de Laravel']);
        $this->get(route('home'))->assertSee('Inscripciones abiertas hasta el viernes')->assertSee($evento->nombre);

        $this->actingAs($this->admin)->put(route('admin.configuracion.update'), [
            'grupo' => 'inicio',
            'mostrar_proximos' => '0',
            'mostrar_nosotros' => '1',
            'mostrar_mision_vision' => '1',
            'mostrar_autoridades' => '1',
            'mostrar_resenas' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertFalse(Configuracion::activo('mostrar_proximos'));
        $this->get(route('home'))->assertDontSee($evento->nombre);
    }

    public function test_limita_las_inscripciones_activas_por_estudiante(): void
    {
        Configuracion::guardar(['max_inscripciones_estudiante' => '1']);
        $estudiante = User::factory()->create();
        Inscripcion::factory()->for($estudiante)->for(Evento::factory()->gratuito())->pagada()->create();

        $this->expectException(InscripcionException::class);
        app(InscripcionService::class)->inscribir($estudiante, Evento::factory()->gratuito()->create());
    }

    public function test_exporta_la_configuracion_y_limpia_la_cache(): void
    {
        Configuracion::guardar(['email_contacto' => 'fisei@uta.edu.ec']);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.configuracion.exportar'))->assertOk();
        $this->assertStringContainsString('fisei@uta.edu.ec', $respuesta->streamedContent());

        $this->actingAs($this->admin)->post(route('admin.configuracion.limpiar-cache'))->assertSessionHas('status');
    }
}
