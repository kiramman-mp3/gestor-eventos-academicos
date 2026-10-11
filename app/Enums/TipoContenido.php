<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

/** Bloques editables de la página de inicio (antes tabla info_fisei). */
enum TipoContenido: string
{
    use HasValues;

    case Carrusel = 'carrusel';
    case Nosotros = 'nosotros';
    case Autoridad = 'autoridad';
    case Resena = 'resena';
    case Mision = 'mision';
    case Vision = 'vision';

    public function label(): string
    {
        return match ($this) {
            self::Carrusel => 'Carrusel',
            self::Nosotros => 'Nosotros',
            self::Autoridad => 'Autoridades',
            self::Resena => 'Reseñas',
            self::Mision => 'Misión',
            self::Vision => 'Visión',
        };
    }

    /** Misión y visión son bloques únicos; el resto admite varios elementos. */
    public function esUnico(): bool
    {
        return in_array($this, [self::Mision, self::Vision], true);
    }

    public function admiteImagen(): bool
    {
        return ! $this->esUnico();
    }

    /** Etiquetas de los campos genéricos (titulo, subtitulo, cuerpo) según el tipo. */
    public function campos(): array
    {
        return match ($this) {
            self::Carrusel, self::Nosotros => ['titulo' => 'Título', 'cuerpo' => 'Descripción'],
            self::Autoridad => ['titulo' => 'Nombre', 'subtitulo' => 'Cargo'],
            self::Resena => ['titulo' => 'Autor', 'subtitulo' => 'Rol', 'cuerpo' => 'Opinión'],
            self::Mision, self::Vision => ['cuerpo' => 'Texto'],
        };
    }
}
