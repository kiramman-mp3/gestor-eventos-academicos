<?php

use App\Enums\EstadoEvento;
use App\Enums\Modalidad;
use App\Enums\TipoDocumento;
use App\Enums\TipoRequisito;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Eventos académicos, su tipo, las categorías a las que se dirigen y sus requisitos.
 *
 * Resuelve la duplicidad del legado (`eventos.categoria_id` 1:N junto a `evento_categoria` N:M):
 * solo existe la relación N:M `categoria_evento`. Un evento abierto a cualquier persona
 * se marca con `es_publico` (antes: una categoría ficticia "Publico").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_evento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->timestamps();
        });

        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_evento_id')->constrained('tipos_evento')->restrictOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->text('ponentes');
            $table->enum('modalidad', Modalidad::values())->default(Modalidad::Presencial->value);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_inicio_inscripciones');
            $table->date('fecha_fin_inscripciones');
            $table->unsignedSmallInteger('horas');
            $table->unsignedInteger('cupos');
            $table->decimal('costo', 8, 2)->default(0);
            $table->boolean('es_publico')->default(false);
            $table->boolean('requiere_nota')->default(false);
            $table->decimal('nota_minima', 4, 2)->nullable();
            $table->boolean('requiere_asistencia')->default(false);
            $table->decimal('asistencia_minima', 5, 2)->nullable();
            $table->string('imagen_path')->nullable();
            $table->enum('estado', EstadoEvento::values())->default(EstadoEvento::Borrador->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha_inicio']);
        });

        Schema::create('categoria_evento', function (Blueprint $table) {
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->primary(['categoria_id', 'evento_id']);
        });

        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->enum('tipo', TipoRequisito::values());
            // Solo para tipo = documento: qué documento del perfil se exige
            $table->enum('tipo_documento', TipoDocumento::values())->nullable();
            $table->string('descripcion', 500);
            $table->timestamps();
        });

        // Reglas de negocio garantizadas por la BD (MySQL >= 8.0.16 / MariaDB >= 10.2)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE eventos ADD CONSTRAINT chk_eventos_fechas CHECK (
                fecha_inicio <= fecha_fin
                AND fecha_inicio_inscripciones <= fecha_fin_inscripciones
                AND fecha_fin_inscripciones <= fecha_inicio)');
            DB::statement('ALTER TABLE eventos ADD CONSTRAINT chk_eventos_nota CHECK (nota_minima IS NULL OR nota_minima BETWEEN 0 AND 10)');
            DB::statement('ALTER TABLE eventos ADD CONSTRAINT chk_eventos_asistencia CHECK (asistencia_minima IS NULL OR asistencia_minima BETWEEN 0 AND 100)');
            DB::statement('ALTER TABLE eventos ADD CONSTRAINT chk_eventos_costo CHECK (costo >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos');
        Schema::dropIfExists('categoria_evento');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('tipos_evento');
    }
};
