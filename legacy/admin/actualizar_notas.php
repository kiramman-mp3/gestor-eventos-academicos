<?php
require_once '../session.php';
include('../sql/conexion.php');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$conexion = (new Conexion())->conectar();

// Obtener el evento_id desde el POST
$evento_id = $_POST['evento_id'] ?? null;
if (!$evento_id) {
    echo "Error: ID del evento no proporcionado.";
    exit;
}

// Obtener los requisitos del evento
$stmt = $conexion->prepare("
    SELECT e.requiere_nota, e.requiere_asistencia, e.nota_minima, e.asistencia_minima, e.nombre_evento
    FROM eventos e
    WHERE e.id = ?
");
$stmt->execute([$evento_id]);
$evento_requisitos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evento_requisitos) {
    echo "Error: No se pudo obtener información del evento.";
    exit;
}

$notas = $_POST['notas'] ?? [];
$asistencias = $_POST['asistencias'] ?? [];
$legalizados = $_POST['legalizados'] ?? [];
$pagos = $_POST['pagos'] ?? [];

// Validar campos obligatorios según la categoría
$errores = [];

foreach ($notas as $inscripcionId => $nota) {
    if ($evento_requisitos['requiere_nota'] && ($nota === '' || $nota === null)) {
        $errores[] = "La nota es obligatoria para el evento '{$evento_requisitos['nombre_evento']}'.";
    } elseif ($evento_requisitos['requiere_nota'] && $nota !== '' && $nota !== null) {
        // Validar nota mínima si está configurada
        $nota_minima = $evento_requisitos['nota_minima'] ?? 0;
        if ((float)$nota < $nota_minima) {
            $errores[] = "La nota debe ser al menos {$nota_minima} para el evento '{$evento_requisitos['nombre_evento']}'.";
        }
    }
    if ($nota !== '' && $nota !== null) {
        $stmt = $conexion->prepare("UPDATE inscripciones SET nota = ? WHERE id = ?");
        $stmt->execute([$nota, $inscripcionId]);
    }
}

foreach ($asistencias as $inscripcionId => $asistencia) {
    if ($evento_requisitos['requiere_asistencia'] && ($asistencia === '' || $asistencia === null)) {
        $errores[] = "La asistencia es obligatoria para el evento '{$evento_requisitos['nombre_evento']}'.";
    } elseif ($evento_requisitos['requiere_asistencia'] && $asistencia !== '' && $asistencia !== null) {
        // Validar asistencia mínima si está configurada
        $asistencia_minima = $evento_requisitos['asistencia_minima'] ?? 0;
        if ((float)$asistencia < $asistencia_minima) {
            $errores[] = "La asistencia debe ser al menos {$asistencia_minima}% para el evento '{$evento_requisitos['nombre_evento']}'.";
        }
    }
    if ($asistencia !== '' && $asistencia !== null) {
        $stmt = $conexion->prepare("UPDATE inscripciones SET asistencia = ? WHERE id = ?");
        $stmt->execute([$asistencia, $inscripcionId]);
    }
}

// Marcar como legalizado (si está presente en el POST)
$stmtLegalizado = $conexion->prepare("UPDATE inscripciones SET legalizado = ? WHERE id = ?");
foreach ($notas as $inscripcionId => $_) {
    $valor = isset($legalizados[$inscripcionId]) ? 1 : 0;
    $stmtLegalizado->execute([$valor, $inscripcionId]);
}

// Marcar como pago confirmado (si está presente en el POST)
$stmtPago = $conexion->prepare("UPDATE inscripciones SET pago_confirmado = ? WHERE id = ?");
foreach ($notas as $inscripcionId => $_) {
    $valor = isset($pagos[$inscripcionId]) ? 1 : 0;
    $stmtPago->execute([$valor, $inscripcionId]);
}

$eventoId = $_POST['evento_id'] ?? 0;
header("Location: administrar_evento.php?id=$eventoId");
exit;
