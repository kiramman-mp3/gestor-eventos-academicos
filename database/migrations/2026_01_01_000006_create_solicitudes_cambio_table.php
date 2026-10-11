<?php

use App\Enums\EstadoSolicitud;
use App\Enums\Prioridad;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitudes de cambio (gestión de la configuración) y su resolución.
 * El solicitante es una FK a users; antes eran campos uid/uname/uemail/urol enviados por el formulario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_cambio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('titulo');
            $table->string('tipo', 100);
            $table->text('descripcion');
            $table->text('justificacion')->nullable();
            $table->text('contexto')->nullable();
            $table->string('captura_path')->nullable();
            $table->timestamps();
        });

        Schema::create('resoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_cambio_id')->unique()->constrained('solicitudes_cambio')->cascadeOnDelete();
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('prioridad', Prioridad::values());
            $table->enum('estado', EstadoSolicitud::values())->default(EstadoSolicitud::Pendiente->value);
            $table->text('comentario');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resoluciones');
        Schema::dropIfExists('solicitudes_cambio');
    }
};
