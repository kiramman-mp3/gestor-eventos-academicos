<?php
require_once '../session.php';
require_once '../sql/conexion.php';
require_once '../upload_seguro.php';
header('Content-Type: application/json');

$conn = (new Conexion())->conectar();
$action = $_GET['action'] ?? '';
$UPLOAD_DIR = '../uploads/cursos_imagenes/';

// Solo 'listar' y 'obtener' son públicos; crear/editar/eliminar requieren administrador
if (!in_array($action, ['listar', 'obtener'], true)) {
    requireAdmin();
}

// Guarda la imagen del curso validada; devuelve la ruta relativa o null si no se envió
function subirImagenCurso($dir) {
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    try {
        return $dir . guardarUploadSeguro($_FILES['imagen'], __DIR__ . '/' . $dir, UPLOAD_IMAGENES);
    } catch (UploadException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// ========== LISTAR ==========
if ($action === 'listar') {
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
            LEFT JOIN inscripciones i ON e.id = i.evento_id AND i.estado = 'activo'
            GROUP BY e.id, e.nombre_evento, e.cupos, t.nombre, c.nombre
            ORDER BY e.fecha_inicio DESC
        ");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ========== OBTENER UNO ==========
if ($action === 'obtener' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ========== CREAR ==========
if ($action === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $rutaImagen = subirImagenCurso($UPLOAD_DIR) ?? '';

        // Validar campos requeridos
        $campos = ['nombre_evento', 'tipo_evento_id', 'categoria_id', 'ponentes', 'fecha_inicio', 'fecha_fin', 'fecha_inicio_inscripciones', 'fecha_fin_inscripciones', 'horas', 'cupos', 'estado'];
        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                echo json_encode(['success' => false, 'error' => "Falta el campo: $campo"]);
                exit;
            }
        }

        $sql = "INSERT INTO eventos (
            nombre_evento, tipo_evento_id, categoria_id, ponentes,
            fecha_inicio, fecha_fin, fecha_inicio_inscripciones, fecha_fin_inscripciones,
            horas, cupos, ruta_imagen, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            $_POST['nombre_evento'],
            $_POST['tipo_evento_id'],
            $_POST['categoria_id'],
            $_POST['ponentes'],
            $_POST['fecha_inicio'],
            $_POST['fecha_fin'],
            $_POST['fecha_inicio_inscripciones'],
            $_POST['fecha_fin_inscripciones'],
            $_POST['horas'],
            $_POST['cupos'],
            $rutaImagen,
            $_POST['estado']
        ]);

        echo json_encode(['success' => $success]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ========== EDITAR ==========
if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['id'])) {
            echo json_encode(['success' => false, 'error' => 'Falta el ID del curso']);
            exit;
        }

        $cursoId = $_POST['id'];
        
        // Verificar si el curso existe y obtener sus fechas actuales
        $stmt = $conn->prepare("SELECT fecha_inicio, fecha_fin, fecha_inicio_inscripciones, fecha_fin_inscripciones FROM eventos WHERE id = ?");
        $stmt->execute([$cursoId]);
        $cursoActual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cursoActual) {
            echo json_encode(['success' => false, 'error' => 'El curso no existe']);
            exit;
        }
        
        // Verificar si el curso ya está en ejecución (después de la fecha de inicio)
        $fechaActual = date('Y-m-d');
        if ($fechaActual >= $cursoActual['fecha_inicio']) {
            echo json_encode(['success' => false, 'error' => 'No se puede editar un curso que ya está en ejecución']);
            exit;
        }
        
        // Si se están enviando nuevas fechas, validarlas
        if (isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin'])) {
            $fechaInicio = $_POST['fecha_inicio'];
            $fechaFin = $_POST['fecha_fin'];
            $fechaInicioInsc = $_POST['fecha_inicio_inscripciones'] ?? $cursoActual['fecha_inicio_inscripciones'];
            $fechaFinInsc = $_POST['fecha_fin_inscripciones'] ?? $cursoActual['fecha_fin_inscripciones'];
            
            // Validaciones de fechas
            if ($fechaInicio > $fechaFin) {
                echo json_encode(['success' => false, 'error' => 'La fecha de inicio del curso debe ser anterior a la fecha de fin']);
                exit;
            }
            
            if ($fechaInicioInsc > $fechaFinInsc) {
                echo json_encode(['success' => false, 'error' => 'La fecha de inicio de inscripciones debe ser anterior a la fecha de fin de inscripciones']);
                exit;
            }
            
            if ($fechaFinInsc >= $fechaInicio) {
                echo json_encode(['success' => false, 'error' => 'Las inscripciones deben terminar antes de que inicie el curso']);
                exit;
            }
            
            if ($fechaInicioInsc >= $fechaInicio) {
                echo json_encode(['success' => false, 'error' => 'Las inscripciones deben comenzar antes de que inicie el curso']);
                exit;
            }
        }

        $rutaImagen = subirImagenCurso($UPLOAD_DIR) ?? ($_POST['ruta_imagen_actual'] ?? '');

        $sql = "UPDATE eventos SET
            nombre_evento=?, tipo_evento_id=?, categoria_id=?, ponentes=?,
            fecha_inicio=?, fecha_fin=?, fecha_inicio_inscripciones=?, fecha_fin_inscripciones=?,
            horas=?, cupos=?, ruta_imagen=?, estado=?
            WHERE id=?";

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            $_POST['nombre_evento'],
            $_POST['tipo_evento_id'],
            $_POST['categoria_id'],
            $_POST['ponentes'],
            $_POST['fecha_inicio'],
            $_POST['fecha_fin'],
            $_POST['fecha_inicio_inscripciones'],
            $_POST['fecha_fin_inscripciones'],
            $_POST['horas'],
            $_POST['cupos'],
            $rutaImagen,
            $_POST['estado'],
            $_POST['id']
        ]);

        echo json_encode(['success' => $success, 'mensaje' => $success ? 'Curso actualizado correctamente' : 'Error al actualizar el curso']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ========== ELIMINAR ==========
if ($action === 'eliminar' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
        $success = $stmt->execute([$_GET['id']]);
        echo json_encode(['success' => $success]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
