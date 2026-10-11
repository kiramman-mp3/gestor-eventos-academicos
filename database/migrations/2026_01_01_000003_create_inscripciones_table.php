<?php

use App\Enums\EstadoComprobante;
use App\Enums\EstadoInscripcion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inscripciones, respuestas a requisitos y comprobantes de pago.
 *
 * - UNIQUE(user_id, evento_id): sin dobles inscripciones, ni con peticiones concurrentes.
 * - Un único ENUM de estado; se elimina `pago_confirmado` (redundante con estado = pagado).
 * - El comprobante tiene su propia tabla con historial de revisión.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('evento_id')->constrained('eventos')->restrictOnDelete();
            $table->enum('estado', EstadoInscripcion::values())->default(EstadoInscripcion::EnEsperaPago->value);
            $table->decimal('nota', 4, 2)->nullable();
            $table->decimal('asistencia', 5, 2)->nullable();
            $table->boolean('legalizado')->default(false);
            $table->string('codigo_certificado', 40)->nullable()->unique();
            $table->timestamp('certificado_emitido_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'evento_id']);
            // Conteo de cupos ocupados: WHERE evento_id = ? AND estado IN (...)
            $table->index(['evento_id', 'estado']);
        });

        Schema::create('respuestas_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->foreignId('requisito_id')->constrained('requisitos')->cascadeOnDelete();
            $table->text('texto');
            $table->timestamps();

            $table->unique(['inscripcion_id', 'requisito_id']);
        });

        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->string('path');
            $table->string('nombre_original');
            $table->string('mime', 100);
            $table->unsignedInteger('tamano');
            $table->enum('estado', EstadoComprobante::values())->default(EstadoComprobante::Pendiente->value);
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            $table->string('motivo_rechazo', 500)->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE inscripciones ADD CONSTRAINT chk_inscripciones_nota CHECK (nota IS NULL OR nota BETWEEN 0 AND 10)');
            DB::statement('ALTER TABLE inscripciones ADD CONSTRAINT chk_inscripciones_asistencia CHECK (asistencia IS NULL OR asistencia BETWEEN 0 AND 100)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
        Schema::dropIfExists('respuestas_requisito');
        Schema::dropIfExists('inscripciones');
    }
};
