<?php
require_once '../session.php';
require_once '../sql/conexion.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() !== 'estudiante') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$evento_id = isset($data['evento_id']) ? (int)$data['evento_id'] : 0;
$textos = $data['textos_requisitos'] ?? [];

if ($evento_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID del evento no válido.']);
    exit;
}

$conexion = (new Conexion())->conectar();
$usuario_id = getUserId();

// Obtener requisitos del evento
$stmtReq = $conexion->prepare("SELECT id, tipo, campo_estudiante, descripcion FROM requisitos_evento WHERE evento_id = ?");
$stmtReq->execute([$evento_id]);
$requisitos = $stmtReq->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos del estudiante
$stmtEst = $conexion->prepare("SELECT * FROM estudiantes WHERE id = ?");
$stmtEst->execute([$usuario_id]);
$estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

$errores = [];

// Validar requisitos
foreach ($requisitos as $req) {
    $tipo = strtolower(trim($req['tipo']));
    $campo = $req['campo_estudiante'];
    $descripcion = $req['descripcion'];

    if ($tipo === 'archivo') {
        if (!isset($estudiante[$campo]) || empty($estudiante[$campo])) {
            $errores[] = "Falta subir: $descripcion";
        }
    }

    if ($tipo === 'texto') {
        $id = $req['id'];
        $valor = trim($textos[$id] ?? '');
        if ($valor === '') {
            $errores[] = "Debes completar: $descripcion";
        }
    }
}


// Validar si ya está inscrito
$stmtExiste = $conexion->prepare("SELECT 1 FROM inscripciones WHERE usuario_id = ? AND evento_id = ?");
$stmtExiste->execute([$usuario_id, $evento_id]);
if ($stmtExiste->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Ya estás inscrito en este curso.']);
    exit;
}

// Validar si el curso está abierto
$stmtEvento = $conexion->prepare("SELECT estado, fecha_inicio_inscripciones, fecha_fin_inscripciones FROM eventos WHERE id = ?");
$stmtEvento->execute([$evento_id]);
$evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

$hoy = date('Y-m-d');
if (!$evento || strtolower($evento['estado']) !== 'abierto' || $hoy < $evento['fecha_inicio_inscripciones'] || $hoy > $evento['fecha_fin_inscripciones']) {
    echo json_encode(['success' => false, 'message' => 'Este evento no está disponible para inscripciones.']);
    exit;
}

if (count($errores) > 0) {
    echo json_encode(['success' => false, 'message' => implode("\n", $errores)]);
    exit;
}

$texto_adicional = implode("\n\n", array_map(function($k, $v) {
    return "Requisito ID $k: $v";
}, array_keys($textos), $textos));


// Insertar inscripción
$stmtInsert = $conexion->prepare("INSERT INTO inscripciones (usuario_id, evento_id, estado, texto_adicional) VALUES (?, ?, 'En espera de orden de pago', ?)");
$stmtInsert->execute([$usuario_id, $evento_id, $texto_adicional]);


echo json_encode(['success' => true, 'message' => 'Te has inscrito correctamente en el curso.']);
