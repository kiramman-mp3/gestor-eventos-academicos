<?php
require_once '../session.php';
include('../sql/conexion.php');
require_once '../upload_seguro.php';

requireRole(ROL_ESTUDIANTE);
requirePost();

$usuarioId = getUserId();
$eventoId = $_POST['evento_id'] ?? null;

if (!is_numeric($eventoId) || !isset($_FILES['comprobante'])) {
    exit('Datos inválidos.');
}

$conexion = (new Conexion())->conectar();

// Solo se acepta comprobante para una inscripción propia que aún espera pago
$stmt = $conexion->prepare("SELECT id, comprobante_pago FROM inscripciones WHERE usuario_id = ? AND evento_id = ? AND estado = 'En espera de orden de pago'");
$stmt->execute([$usuarioId, $eventoId]);
$inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inscripcion) {
    exit('No tienes una inscripción pendiente de pago para este evento.');
}

// Subir archivo (MIME real validado con finfo, nombre aleatorio)
$dir = '../uploads/comprobantes/' . (int) $usuarioId . '/';
try {
    $nombreFinal = guardarUploadSeguro($_FILES['comprobante'], __DIR__ . '/' . $dir, UPLOAD_DOCUMENTOS);
} catch (UploadException $e) {
    exit(e($e->getMessage()));
}
$destino = $dir . $nombreFinal;
eliminarUploadSeguro($inscripcion['comprobante_pago'] ? __DIR__ . '/' . $inscripcion['comprobante_pago'] : null, __DIR__ . '/../uploads/comprobantes');

// Guardar ruta en base de datos
$stmt = $conexion->prepare("
    UPDATE inscripciones
    SET comprobante_pago = ?, estado = 'Esperando aprobación del admin'
    WHERE id = ?
");
$stmt->execute([$destino, $inscripcion['id']]);

header("Location: mis_cursos.php");
exit;
