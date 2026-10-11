<?php
require_once '../session.php';

requireAdmin();
requirePost();
include('../sql/conexion.php');


$conexion = (new Conexion())->conectar();
$inscripcionId = $_POST['inscripcion_id'] ?? null;
$accion = $_POST['accion'] ?? null;

if (!is_numeric($inscripcionId) || !in_array($accion, ['aprobar', 'rechazar'])) {
    exit('Petición inválida.');
}

$estadoNuevo = $accion === 'aprobar' ? 'Pagado' : 'En espera de orden de pago';

$stmt = $conexion->prepare("UPDATE inscripciones SET estado = ? WHERE id = ?");
$stmt->execute([$estadoNuevo, $inscripcionId]);

header('Location: comprobantes_pendientes.php');
exit;