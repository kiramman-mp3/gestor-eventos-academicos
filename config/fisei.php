<?php

/*
|--------------------------------------------------------------------------
| Configuración propia del sistema de eventos FISEI
|--------------------------------------------------------------------------
*/

return [

    // Administrador inicial que crea el seeder
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@uta.edu.ec'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    // Edad mínima para registrarse
    'edad_minima' => 17,

    // Subida de archivos (KB)
    'uploads' => [
        'documento_max_kb' => 5120,
        'comprobante_max_kb' => 5120,
        'imagen_max_kb' => 4096,
        'documento_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
        'imagen_mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    // Valores por defecto cuando el evento exige calificación/asistencia sin fijar mínimos
    'nota_minima_defecto' => 7.0,
    'asistencia_minima_defecto' => 70.0,

];
