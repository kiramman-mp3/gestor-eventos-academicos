<?php
// upload_seguro.php — validación y guardado seguro de archivos subidos

class UploadException extends RuntimeException {}

// Lista blanca: extensión => MIME types reales aceptados (detectados con finfo)
const UPLOAD_TIPOS = [
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
    'pdf'  => ['application/pdf'],
];

const UPLOAD_IMAGENES = ['jpg', 'jpeg', 'png', 'webp'];
const UPLOAD_DOCUMENTOS = ['pdf', 'jpg', 'jpeg', 'png'];

const UPLOAD_MAX_BYTES = 5 * 1024 * 1024; // 5 MB

/**
 * Valida y mueve un archivo subido.
 *
 * @param array  $file        Entrada de $_FILES['campo']
 * @param string $dirDestino  Directorio absoluto de destino (se crea si no existe)
 * @param array  $permitidas  Extensiones permitidas (subconjunto de UPLOAD_TIPOS)
 * @param int    $maxBytes    Tamaño máximo
 * @return string Nombre final del archivo (aleatorio) dentro de $dirDestino
 * @throws UploadException
 */
function guardarUploadSeguro(array $file, string $dirDestino, array $permitidas, int $maxBytes = UPLOAD_MAX_BYTES): string
{
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new UploadException('Parámetros de subida inválidos.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new UploadException($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'El archivo excede el tamaño permitido.'
            : 'Error al subir el archivo.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new UploadException('Archivo no válido.');
    }
    if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
        throw new UploadException('El archivo excede el tamaño permitido (' . round($maxBytes / 1048576) . ' MB).');
    }

    // Extensión declarada: solo la última, en minúsculas, y dentro de la lista blanca
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $permitidas, true) || !isset(UPLOAD_TIPOS[$ext])) {
        throw new UploadException('Tipo de archivo no permitido. Permitidos: ' . implode(', ', $permitidas) . '.');
    }

    // MIME real según el contenido, no el enviado por el cliente
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, UPLOAD_TIPOS[$ext], true)) {
        throw new UploadException('El contenido del archivo no coincide con su extensión.');
    }

    // Las imágenes deben poder decodificarse como imagen
    if (str_starts_with($mime, 'image/') && @getimagesize($file['tmp_name']) === false) {
        throw new UploadException('La imagen está dañada o no es válida.');
    }

    if (!is_dir($dirDestino) && !mkdir($dirDestino, 0750, true) && !is_dir($dirDestino)) {
        throw new UploadException('No se pudo crear el directorio de destino.');
    }

    // Nombre aleatorio: nunca se reutiliza el nombre original
    $nombreFinal = bin2hex(random_bytes(16)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $destino = rtrim($dirDestino, '/\\') . DIRECTORY_SEPARATOR . $nombreFinal;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        throw new UploadException('Error al guardar el archivo.');
    }
    chmod($destino, 0640);

    return $nombreFinal;
}

// Elimina un archivo previo solo si está dentro de $dirBase (evita borrar rutas arbitrarias)
function eliminarUploadSeguro(?string $ruta, string $dirBase): void
{
    if (!$ruta) {
        return;
    }
    $real = realpath($ruta);
    $base = realpath($dirBase);
    if ($real && $base && str_starts_with($real, $base . DIRECTORY_SEPARATOR) && is_file($real)) {
        unlink($real);
    }
}
