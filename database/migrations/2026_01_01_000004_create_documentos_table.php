<?php

use App\Enums\TipoDocumento;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentos personales del usuario (cédula, papeleta, matrícula).
 * Los archivos viven en el disco privado; solo se descargan por rutas protegidas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', TipoDocumento::values());
            $table->string('path');
            $table->string('nombre_original');
            $table->string('mime', 100);
            $table->unsignedInteger('tamano');
            $table->timestamps();

            $table->unique(['user_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
