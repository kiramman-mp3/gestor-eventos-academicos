<?php

use App\Enums\TipoContenido;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contenido editable del sitio (antes `info_fisei`, con JSON guardado en TEXT)
 * y configuración general clave/valor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contenidos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', TipoContenido::values());
            $table->string('titulo')->nullable();
            $table->string('subtitulo')->nullable();
            $table->text('cuerpo')->nullable();
            $table->string('imagen_path')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['tipo', 'orden']);
        });

        Schema::create('configuraciones', function (Blueprint $table) {
            $table->string('clave', 100)->primary();
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
        Schema::dropIfExists('contenidos');
    }
};
