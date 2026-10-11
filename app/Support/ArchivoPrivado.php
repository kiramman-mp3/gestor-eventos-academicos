<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Respuesta segura para servir archivos del disco privado tras pasar una política.
 */
final class ArchivoPrivado
{
    public static function respuesta(string $disk, string $path, string $nombre, string $mime): StreamedResponse
    {
        abort_unless(Storage::disk($disk)->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $nombreSeguro = Str::slug(pathinfo($nombre, PATHINFO_FILENAME)) ?: 'archivo';

        return Storage::disk($disk)->response($path, $nombreSeguro.'.'.$extension, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
