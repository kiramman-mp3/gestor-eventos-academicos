<?php

namespace Database\Seeders;

use App\Enums\TipoContenido;
use App\Models\Contenido;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Contenido inicial de la página de inicio (tomado del sistema anterior).
 * Solo se ejecuta si aún no hay contenido de ese tipo; las imágenes se copian al disco público.
 */
class ContenidoSeeder extends Seeder
{
    public function run(): void
    {
        $this->sembrar(TipoContenido::Mision, [[
            'cuerpo' => 'Formar profesionales líderes y competentes en ingeniería, con una sólida visión humanista, científica y tecnológica, comprometidos con el desarrollo sostenible del país.',
        ]]);

        $this->sembrar(TipoContenido::Vision, [[
            'cuerpo' => 'Ser reconocida a nivel nacional e internacional por la excelencia en la formación de profesionales en ingeniería con un enfoque integral.',
        ]]);

        $this->sembrar(TipoContenido::Carrusel, [
            ['titulo' => 'Cursos virtuales y presenciales', 'cuerpo' => 'Disponibles para todas las personas que deseen expandir sus conocimientos.', 'imagen' => 'carrusel-2.jpg'],
            ['titulo' => 'Conoce nuestro campus', 'cuerpo' => 'Compromiso con el desarrollo tecnológico.', 'imagen' => 'carrusel-1.jpg'],
        ]);

        $this->sembrar(TipoContenido::Nosotros, [[
            'titulo' => 'El inicio de una nueva forma de aprendizaje',
            'cuerpo' => 'El 20 de octubre de 2002 se crea el Centro de Transferencia y Desarrollo de Tecnologías en las áreas de Ingenierías en Sistemas, Electrónica e Industrial de la Universidad Técnica de Ambato, para brindar a la comunidad cursos, asesorías, investigaciones y seminarios.',
            'imagen' => 'nosotros.jpg',
        ]]);

        $this->sembrar(TipoContenido::Autoridad, [
            ['titulo' => 'Ing. Franklin Mayorga, Mg.', 'subtitulo' => 'Decano', 'imagen' => 'autoridad.png'],
        ]);

        $this->sembrar(TipoContenido::Resena, [
            ['titulo' => 'Chris Changoluisa', 'subtitulo' => 'Estudiante', 'cuerpo' => 'Una de las cosas destacables es la calidad de los cursos: son instruidos por profesionales altamente preparados.', 'imagen' => 'resena.jpg'],
        ]);
    }

    /** @param list<array<string, string>> $elementos */
    private function sembrar(TipoContenido $tipo, array $elementos): void
    {
        if (Contenido::query()->where('tipo', $tipo)->exists()) {
            return;
        }

        foreach ($elementos as $orden => $datos) {
            $contenido = new Contenido([
                'tipo' => $tipo,
                'titulo' => $datos['titulo'] ?? null,
                'subtitulo' => $datos['subtitulo'] ?? null,
                'cuerpo' => $datos['cuerpo'] ?? null,
                'orden' => $orden,
            ]);

            if (isset($datos['imagen']) && is_file($origen = __DIR__.'/imagenes/'.$datos['imagen'])) {
                $contenido->imagen_path = Storage::disk('public')->putFile('contenidos', $origen);
            }

            $contenido->save();
        }
    }
}
