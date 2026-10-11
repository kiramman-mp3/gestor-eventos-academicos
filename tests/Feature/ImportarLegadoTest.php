<?php

namespace Tests\Feature;

use App\Enums\EstadoEvento;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoSolicitud;
use App\Enums\RolUsuario;
use App\Enums\TipoContenido;
use App\Enums\TipoDocumento;
use App\Enums\TipoRequisito;
use App\Models\Categoria;
use App\Models\Contenido;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\SolicitudCambio;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportarLegadoTest extends TestCase
{
    use RefreshDatabase;

    private string $raiz;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        config(['database.connections.legado' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $this->crearEsquemaLegado();
        $this->sembrarLegado();

        $this->raiz = sys_get_temp_dir().'/legado_'.uniqid();
        File::ensureDirectoryExists($this->raiz.'/uploads/documentos');
        // PNG mínimo de 1x1
        File::put($this->raiz.'/uploads/documentos/cedula.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        File::put($this->raiz.'/uploads/documentos/script.php', '<?php echo 1;');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->raiz);
        parent::tearDown();
    }

    public function test_importa_usuarios_eventos_inscripciones_contenido_y_solicitudes(): void
    {
        $this->artisan('legado:importar', ['--uploads' => $this->raiz, '--costo' => 15])->assertSuccessful();

        $admin = User::query()->where('email', 'chris@uta.edu.ec')->sole();
        $this->assertSame(RolUsuario::Administrador, $admin->rol);
        $this->assertTrue(Hash::check('secreto123', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
        $this->assertSame('Software', $admin->categoria->nombre);
        $this->assertSame(TipoDocumento::Cedula, $admin->documentos()->sole()->tipo);
        Storage::disk('local')->assertExists($admin->documentos()->sole()->path);

        // "Publico" no es una carrera: se convierte en el indicador es_publico
        $this->assertFalse(Categoria::query()->where('nombre', 'Publico')->exists());

        $evento = Evento::query()->where('nombre', 'Inteligencia artificial')->sole();
        $this->assertTrue($evento->es_publico);
        $this->assertEquals(15, $evento->costo);
        $this->assertSame(EstadoEvento::Finalizado, $evento->estado);
        $this->assertSame(['Software'], $evento->categorias->pluck('nombre')->all());
        $this->assertSame(
            [TipoRequisito::Documento, TipoRequisito::Texto],
            $evento->requisitos()->orderBy('id')->pluck('tipo')->all(),
        );

        // Fechas de inscripción posteriores al inicio se recortan al inicio del evento
        $python = Evento::query()->where('nombre', 'Python')->sole();
        $this->assertSame('2025-02-25', $python->fecha_fin_inscripciones->toDateString());

        $inscripcion = Inscripcion::query()->sole();
        $this->assertSame(EstadoInscripcion::Pagado, $inscripcion->estado);
        $this->assertEquals(8.5, $inscripcion->nota);
        $this->assertSame('Quiero aprender', $inscripcion->respuestas()->sole()->texto);

        $this->assertSame('Ing. Franklin Mayorga', Contenido::query()->where('tipo', TipoContenido::Autoridad)->sole()->titulo);

        $solicitud = SolicitudCambio::query()->sole();
        $this->assertSame($admin->id, $solicitud->user_id);
        $this->assertSame(EstadoSolicitud::Terminado, $solicitud->resolucion->estado);
    }

    public function test_es_idempotente(): void
    {
        $this->artisan('legado:importar')->assertSuccessful();
        $this->artisan('legado:importar')->assertSuccessful();

        $this->assertSame(2, User::query()->count());
        $this->assertSame(2, Evento::query()->count());
        $this->assertSame(1, Inscripcion::query()->count());
        $this->assertSame(1, SolicitudCambio::query()->count());
    }

    public function test_no_copia_archivos_con_tipo_no_permitido_ni_fuera_de_la_raiz(): void
    {
        DB::connection('legado')->table('estudiantes')->where('id', 18)->update(['cedula_path' => 'uploads/documentos/script.php']);
        DB::connection('legado')->table('estudiantes')->where('id', 2)->update(['cedula_path' => '../../../etc/passwd']);

        $this->artisan('legado:importar', ['--uploads' => $this->raiz])->assertSuccessful();

        $this->assertDatabaseCount('documentos', 0);
    }

    public function test_falla_si_la_carpeta_de_uploads_no_existe(): void
    {
        $this->artisan('legado:importar', ['--uploads' => $this->raiz.'/no-existe'])->assertFailed();
    }

    private function crearEsquemaLegado(): void
    {
        $schema = Schema::connection('legado');

        $schema->create('categorias_evento', function (Blueprint $t) {
            $t->integer('id');
            $t->string('nombre');
        });
        $schema->create('tipos_evento', function (Blueprint $t) {
            $t->integer('id');
            $t->string('nombre');
        });
        $schema->create('estudiantes', function (Blueprint $t) {
            $t->integer('id');
            foreach (['nombre', 'apellido', 'cedula', 'telefono', 'correo', 'password', 'genero', 'tipo', 'carrera', 'rol'] as $c) {
                $t->string($c)->nullable();
            }
            $t->date('fecha_nacimiento')->nullable();
            $t->string('cedula_path')->nullable();
            $t->string('papeleta_path')->nullable();
            $t->string('matricula_path')->nullable();
            $t->timestamp('fecha_registro')->nullable();
            $t->boolean('verificado')->default(false);
        });
        $schema->create('eventos', function (Blueprint $t) {
            $t->integer('id');
            foreach (['nombre_evento', 'ponentes', 'descripcion', 'ruta_imagen', 'estado'] as $c) {
                $t->string($c)->nullable();
            }
            $t->integer('tipo_evento_id');
            $t->integer('categoria_id');
            foreach (['fecha_inicio', 'fecha_fin', 'fecha_inicio_inscripciones', 'fecha_fin_inscripciones'] as $c) {
                $t->date($c);
            }
            $t->integer('horas');
            $t->integer('cupos');
        });
        $schema->create('evento_categoria', function (Blueprint $t) {
            $t->integer('evento_id');
            $t->integer('categoria_id');
        });
        $schema->create('requisitos_evento', function (Blueprint $t) {
            $t->integer('id');
            $t->integer('evento_id');
            $t->string('descripcion');
            $t->string('tipo');
            $t->string('campo_estudiante')->nullable();
        });
        $schema->create('inscripciones', function (Blueprint $t) {
            $t->integer('id');
            $t->integer('usuario_id');
            $t->integer('evento_id');
            $t->string('estado');
            $t->decimal('nota')->nullable();
            $t->decimal('asistencia')->nullable();
            $t->string('comprobante_pago')->nullable();
            $t->boolean('legalizado')->default(false);
        });
        $schema->create('requisitos_inscripcion', function (Blueprint $t) {
            $t->integer('id');
            $t->integer('inscripcion_id');
            $t->integer('requisito_id');
            $t->string('texto_respuesta')->nullable();
        });
        $schema->create('info_fisei', function (Blueprint $t) {
            $t->integer('id');
            $t->string('tipo')->nullable();
            $t->text('contenido');
        });
        $schema->create('solicitudes', function (Blueprint $t) {
            $t->integer('id');
            foreach (['titulo', 'tipo', 'descripcion', 'justificacion', 'contexto', 'uid', 'uemail'] as $c) {
                $t->string($c)->nullable();
            }
            $t->date('fecha');
            $t->timestamp('creado_en')->nullable();
        });
        $schema->create('resoluciones', function (Blueprint $t) {
            $t->integer('id');
            $t->integer('id_solicitud');
            $t->string('prioridad')->nullable();
            $t->string('comentario')->nullable();
            $t->string('estado')->nullable();
        });
    }

    private function sembrarLegado(): void
    {
        $db = DB::connection('legado');
        $hash = Hash::make('secreto123');

        $db->table('categorias_evento')->insert([['id' => 1, 'nombre' => 'Software'], ['id' => 2, 'nombre' => 'Publico']]);
        $db->table('tipos_evento')->insert([['id' => 3, 'nombre' => 'Congreso']]);
        $db->table('estudiantes')->insert([
            ['id' => 2, 'nombre' => 'Chris', 'apellido' => 'Changoluisa', 'cedula' => '1850410638', 'telefono' => '', 'correo' => 'Chris@uta.edu.ec', 'password' => $hash, 'genero' => 'Hombre', 'tipo' => 'institucional', 'carrera' => 'Software', 'rol' => 'administrador', 'fecha_nacimiento' => '2006-04-11', 'cedula_path' => 'uploads/documentos/cedula.png', 'fecha_registro' => '2025-06-08 16:26:05', 'verificado' => true],
            ['id' => 18, 'nombre' => 'Cristian', 'apellido' => 'Amaya', 'cedula' => '1802570059', 'telefono' => '0985576390', 'correo' => 'cristian@gmail.com', 'password' => $hash, 'genero' => 'Hombre', 'tipo' => 'publico', 'carrera' => '0', 'rol' => 'estudiante', 'fecha_nacimiento' => '2004-09-16', 'cedula_path' => null, 'fecha_registro' => '2025-06-30 01:44:57', 'verificado' => false],
        ]);
        $db->table('eventos')->insert([
            ['id' => 10, 'nombre_evento' => 'Python', 'tipo_evento_id' => 3, 'categoria_id' => 1, 'ponentes' => 'Johan', 'descripcion' => null, 'fecha_inicio' => '2025-02-25', 'fecha_fin' => '2025-02-28', 'fecha_inicio_inscripciones' => '2025-01-20', 'fecha_fin_inscripciones' => '2025-03-01', 'horas' => 40, 'cupos' => 30, 'ruta_imagen' => '../uploads/no-existe.jpg', 'estado' => 'abierto'],
            ['id' => 11, 'nombre_evento' => 'Inteligencia artificial', 'tipo_evento_id' => 3, 'categoria_id' => 1, 'ponentes' => 'Johan', 'descripcion' => 'IA', 'fecha_inicio' => '2025-07-01', 'fecha_fin' => '2025-07-01', 'fecha_inicio_inscripciones' => '2025-06-20', 'fecha_fin_inscripciones' => '2025-06-30', 'horas' => 40, 'cupos' => 20, 'ruta_imagen' => '', 'estado' => 'cerrado'],
        ]);
        $db->table('evento_categoria')->insert([['evento_id' => 11, 'categoria_id' => 2]]);
        $db->table('requisitos_evento')->insert([
            ['id' => 3, 'evento_id' => 11, 'descripcion' => 'Ruta_cedula', 'tipo' => '', 'campo_estudiante' => 'ruta_cedula'],
            ['id' => 5, 'evento_id' => 11, 'descripcion' => 'Carta de motivación', 'tipo' => 'texto', 'campo_estudiante' => null],
        ]);
        $db->table('inscripciones')->insert([
            ['id' => 1, 'usuario_id' => 18, 'evento_id' => 11, 'estado' => 'Pagado', 'nota' => 8.5, 'asistencia' => 90, 'comprobante_pago' => null, 'legalizado' => true],
            ['id' => 2, 'usuario_id' => 99, 'evento_id' => 11, 'estado' => 'Pagado', 'nota' => null, 'asistencia' => null, 'comprobante_pago' => null, 'legalizado' => false],
        ]);
        $db->table('requisitos_inscripcion')->insert([['id' => 1, 'inscripcion_id' => 1, 'requisito_id' => 5, 'texto_respuesta' => 'Quiero aprender']]);
        $db->table('info_fisei')->insert([
            ['id' => 1, 'tipo' => 'mision', 'contenido' => 'Formar profesionales.'],
            ['id' => 3, 'tipo' => 'evento', 'contenido' => 'Tipo descontinuado'],
            ['id' => 8, 'tipo' => 'autoridad', 'contenido' => '{"nombre":"Ing. Franklin Mayorga","cargo":"DECANO","img":"uploads/landing/no-existe.png"}'],
        ]);
        $db->table('solicitudes')->insert([['id' => 2, 'titulo' => 'Rol mal definido', 'fecha' => '2025-06-12', 'tipo' => 'Corrección de Error', 'descripcion' => 'Se guarda admimistrador', 'justificacion' => null, 'contexto' => null, 'uid' => '2', 'uemail' => 'chris@uta.edu.ec', 'creado_en' => '2025-06-11 22:31:03']]);
        $db->table('resoluciones')->insert([
            ['id' => 7, 'id_solicitud' => 2, 'prioridad' => 'Alta', 'comentario' => 'Ok', 'estado' => 'En revisión'],
            ['id' => 8, 'id_solicitud' => 2, 'prioridad' => 'Alta', 'comentario' => 'Ok', 'estado' => 'Terminado'],
        ]);
    }
}
