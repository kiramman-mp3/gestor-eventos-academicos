<?php
require_once '../session.php';
require_once '../sql/conexion.php';

header('Content-Type: application/json');

$conn = (new Conexion())->conectar();
$action = $_GET['action'] ?? '';

// ========== OBTENER INFORMACIÓN DE CUPOS ==========
if ($action === 'info_cupos') {
    $curso_id = $_GET['id'] ?? null;
    
    if (!$curso_id) {
        echo json_encode(['success' => false, 'error' => 'ID del curso requerido']);
        exit;
    }
    
    try {
        // Usar el procedimiento almacenado
        $stmt = $conn->prepare("CALL ObtenerInfoCupos(?)");
        $stmt->execute([$curso_id]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($info) {
            echo json_encode(['success' => true, 'data' => $info]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Curso no encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ========== VERIFICAR SI TIENE CUPOS DISPONIBLES ==========
if ($action === 'verificar_cupos') {
    $curso_id = $_GET['id'] ?? null;
    
    if (!$curso_id) {
        echo json_encode(['success' => false, 'error' => 'ID del curso requerido']);
        exit;
    }
    
    try {
        // Intentar usar la función personalizada del trigger
        $stmt = $conn->prepare("SELECT TieneCuposDisponibles(?) as tiene_cupos");
        $stmt->execute([$curso_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'tiene_cupos' => (bool)$resultado['tiene_cupos']
        ]);
    } catch (PDOException $e) {
        // Si la función no existe, usar consulta manual como fallback
        try {
            $stmt = $conn->prepare("
                SELECT 
                    e.cupos,
                    COUNT(i.id) as inscripciones_actuales,
                    (e.cupos > COUNT(i.id)) as tiene_cupos
                FROM eventos e
                LEFT JOIN inscripciones i ON e.id = i.evento_id AND (i.estado = 'activo' OR i.estado IS NULL)
                WHERE e.id = ?
                GROUP BY e.id, e.cupos
            ");
            $stmt->execute([$curso_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'tiene_cupos' => (bool)$resultado['tiene_cupos'],
                    'fallback' => true,
                    'cupos_totales' => $resultado['cupos'],
                    'inscripciones_actuales' => $resultado['inscripciones_actuales']
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Curso no encontrado']);
            }
        } catch (PDOException $e2) {
            echo json_encode(['success' => false, 'error' => $e2->getMessage()]);
        }
    }
    exit;
}

// ========== LISTAR CURSOS CON INFORMACIÓN DE CUPOS ==========
if ($action === 'listar_con_cupos') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                e.*,
                t.nombre AS tipo_nombre,
                c.nombre AS categoria_nombre,
                COUNT(i.id) as inscripciones_actuales,
                (e.cupos - COUNT(i.id)) as cupos_disponibles,
                CASE 
                    WHEN (e.cupos - COUNT(i.id)) <= 0 THEN 'LLENO'
                    WHEN (e.cupos - COUNT(i.id)) <= 5 THEN 'POCOS_CUPOS'
                    ELSE 'DISPONIBLE'
                END as estado_cupos
            FROM eventos e
            JOIN tipos_evento t ON e.tipo_evento_id = t.id
            JOIN categorias_evento c ON e.categoria_id = c.id
            LEFT JOIN inscripciones i ON e.id = i.evento_id
            GROUP BY e.id, e.nombre_evento, e.cupos, t.nombre, c.nombre
            ORDER BY e.fecha_inicio DESC
        ");
        $stmt->execute();
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $cursos]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
?>
