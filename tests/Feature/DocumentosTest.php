<?php

namespace Tests\Feature;

use App\Enums\TipoDocumento;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function subir(User $user, string $tipo = 'cedula', ?UploadedFile $archivo = null)
    {
        return $this->actingAs($user)->post(route('documentos.store'), [
            'tipo' => $tipo,
            'archivo' => $archivo ?? UploadedFile::fake()->create('mi cédula.pdf', 100, 'application/pdf'),
        ]);
    }

    public function test_sube_documento_al_disco_privado_con_nombre_aleatorio(): void
    {
        $user = User::factory()->create();

        $this->subir($user)->assertSessionHasNoErrors();

        $documento = $user->documentos()->sole();
        $this->assertSame(TipoDocumento::Cedula, $documento->tipo);
        $this->assertStringStartsWith("documentos/{$user->id}/", $documento->path);
        $this->assertStringNotContainsString('cédula', $documento->path);
        Storage::disk('local')->assertExists($documento->path);
        Storage::disk('public')->assertMissing($documento->path);
    }

    public function test_reemplazar_documento_borra_el_archivo_anterior(): void
    {
        $user = User::factory()->create();

        $this->subir($user);
        $anterior = $user->documentos()->sole()->path;

        $this->subir($user);
        $nuevo = $user->fresh()->documentos()->sole()->path;

        $this->assertNotSame($anterior, $nuevo);
        Storage::disk('local')->assertMissing($anterior);
        Storage::disk('local')->assertExists($nuevo);
    }

    public function test_rechaza_archivos_ejecutables_aunque_tengan_extension_permitida(): void
    {
        $user = User::factory()->create();

        // Archivo real (no fake): el MIME se detecta con finfo a partir del contenido
        $tmp = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($tmp, '<?php system($_GET["c"]); ?>');
        $falso = new UploadedFile($tmp, 'shell.pdf', 'application/pdf', null, true);

        $this->subir($user, 'cedula', $falso)->assertSessionHasErrors('archivo');
        $this->subir($user, 'cedula', UploadedFile::fake()->create('shell.php', 1, 'application/x-php'))->assertSessionHasErrors('archivo');

        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_rechaza_tipo_invalido(): void
    {
        $this->subir(User::factory()->create(), 'pasaporte')->assertSessionHasErrors('tipo');
    }

    public function test_solo_el_dueno_o_un_admin_pueden_descargar(): void
    {
        $dueno = User::factory()->create();
        $this->subir($dueno);
        $documento = Documento::sole();

        $this->actingAs($dueno)->get(route('documentos.show', $documento))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->actingAs(User::factory()->admin()->create())->get(route('documentos.show', $documento))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('documentos.show', $documento))->assertForbidden();

        auth()->logout();
        $this->get(route('documentos.show', $documento))->assertRedirect(route('login'));
    }

    public function test_solo_el_dueno_puede_eliminar(): void
    {
        $dueno = User::factory()->create();
        $this->subir($dueno);
        $documento = Documento::sole();

        $this->actingAs(User::factory()->create())->delete(route('documentos.destroy', $documento))->assertForbidden();

        $this->actingAs($dueno)->delete(route('documentos.destroy', $documento))->assertRedirect();
        $this->assertModelMissing($documento);
        Storage::disk('local')->assertMissing($documento->path);
    }

    public function test_perfil_actualiza_telefono_y_carrera_una_sola_vez(): void
    {
        $user = User::factory()->institucional()->create(['categoria_id' => null]);
        $categoria = \App\Models\Categoria::factory()->create();

        $this->actingAs($user)->get(route('perfil.edit'))->assertOk()->assertSee('Documentos');

        $this->actingAs($user)->patch(route('perfil.update'), ['telefono' => '0987654321', 'categoria_id' => $categoria->id])
            ->assertSessionHasNoErrors();

        $this->assertSame($categoria->id, $user->fresh()->categoria_id);

        $this->actingAs($user->fresh())->patch(route('perfil.update'), ['telefono' => '0987654321', 'categoria_id' => $categoria->id])
            ->assertSessionHasErrors('categoria_id');
    }

    public function test_cambiar_contrasena_exige_la_actual(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('perfil.password'), [
            'current_password' => 'incorrecta',
            'password' => 'Nueva.Clave1',
            'password_confirmation' => 'Nueva.Clave1',
        ])->assertSessionHasErrorsIn('password', 'current_password');

        $this->actingAs($user)->put(route('perfil.password'), [
            'current_password' => 'password',
            'password' => 'Nueva.Clave1',
            'password_confirmation' => 'Nueva.Clave1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Nueva.Clave1', $user->fresh()->password));
    }
}
