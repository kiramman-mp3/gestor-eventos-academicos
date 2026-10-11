<?php
require_once '../session.php';
require_once '../sql/conexion.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$evento_id = $_GET['evento_id'] ?? null;
$usuario_id = getUserId();

if (!$evento_id || !$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $conn = (new Conexion())->conectar();

    // Obtener requisitos con campos
    $stmt = $conn->prepare("SELECT id, descripcion, tipo, campo_estudiante FROM requisitos_evento WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $requisitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar inscripción existente
    $stmt2 = $conn->prepare("SELECT id FROM inscripciones WHERE evento_id = ? AND usuario_id = ?");
    $stmt2->execute([$evento_id, $usuario_id]);
    $inscripcion = $stmt2->fetch(PDO::FETCH_ASSOC);
    $inscripcion_id = $inscripcion['id'] ?? null;

    $entregados = [];
    if ($inscripcion_id) {
        $stmt3 = $conn->prepare("SELECT requisito_id FROM requisitos_inscripcion WHERE inscripcion_id = ?");
        $stmt3->execute([$inscripcion_id]);
        $entregados = array_column($stmt3->fetchAll(PDO::FETCH_ASSOC), 'requisito_id');
    }

    // Datos del estudiante para validar requisitos tipo 'archivo'
    $stmt4 = $conn->prepare("SELECT cedula_path, matricula_path, papeleta_path FROM estudiantes WHERE id = ?");
    $stmt4->execute([$usuario_id]);
    $estudiante = $stmt4->fetch(PDO::FETCH_ASSOC) ?: [];

    // Marcar si cada requisito está cumplido
    foreach ($requisitos as &$req) {
        if ($req['tipo'] === 'archivo') {
            $campo = $req['campo_estudiante'];
            $req['cumplido'] = isset($estudiante[$campo]) && !empty($estudiante[$campo]);
        } elseif ($req['tipo'] === 'texto') {
            $req['cumplido'] = in_array($req['id'], $entregados);
        } else {
            $req['cumplido'] = false;
        }
    }

    // Traer también info del curso con cupos disponibles
    $stmt5 = $conn->prepare("
        SELECT e.*, 
               COUNT(i.id) as inscritos_actuales,
               (e.cupos - COUNT(i.id)) as cupos_disponibles
        FROM eventos e
        LEFT JOIN inscripciones i ON e.id = i.evento_id
        WHERE e.id = ?
        GROUP BY e.id
    ");
    $stmt5->execute([$evento_id]);
    $curso = $stmt5->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'requisitos' => $requisitos,
        'curso' => $curso
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en BD: ' . $e->getMessage()]);
}
