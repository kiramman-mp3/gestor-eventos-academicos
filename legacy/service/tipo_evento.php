<?php
require_once '../session.php';
require_once '../sql/conexion.php';

$cris = new Conexion();
$conn = $cris->conectar(); // Assuming this returns a PDO instance

// Configure PDO to throw exceptions on errors
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ensure the response is JSON
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Solo 'listar' es público; crear/editar/eliminar requieren administrador
if ($action !== 'listar') {
    requireAdmin();
}

// Initialize the response array for consistency
$response = ['success' => false, 'mensaje' => 'Operación no reconocida o fallida.'];

try {
    if ($action === 'listar') {
        $stmt = $conn->query("SELECT id, nombre FROM tipos_evento"); // Select specific columns
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit; // Terminate script after sending response for 'listar'
    }

    if ($action === 'crear') {
        if (!isset($_POST['nombre']) || empty(trim($_POST['nombre']))) {
            $response['mensaje'] = 'El nombre del tipo de evento no puede estar vacío.';
        } else {
            $nombre = trim($_POST['nombre']);
            $stmt = $conn->prepare("INSERT INTO tipos_evento (nombre) VALUES (?)");
            if ($stmt->execute([$nombre])) {
                $response['success'] = true;
                $response['mensaje'] = 'Tipo de evento creado correctamente.';
            } else {
                // This branch is less likely if PDO::ERRMODE_EXCEPTION is active
                $response['mensaje'] = 'Error al insertar el tipo de evento en la base de datos.';
            }
        }
    }

    if ($action === 'editar') {
        if (empty($_POST['id']) || empty(trim($_POST['nombre']))) {
            $response['mensaje'] = 'ID y nombre del tipo de evento son obligatorios para editar.';
        } else {
            $id = $_POST['id'];
            $nombre = trim($_POST['nombre']);
            $stmt = $conn->prepare("UPDATE tipos_evento SET nombre=? WHERE id=?");
            if ($stmt->execute([$nombre, $id])) {
                $response['success'] = true;
                $response['mensaje'] = 'Tipo de evento actualizado correctamente.';
            } else {
                // This branch is less likely if PDO::ERRMODE_EXCEPTION is active
                $response['mensaje'] = 'Error al actualizar el tipo de evento en la base de datos.';
            }
        }
    }

    // --- Added logic for 'eliminar' to complete CRUD operations ---
    if ($action === 'eliminar') {
        if (empty($_GET['id'])) {
            $response['mensaje'] = 'ID del tipo de evento no proporcionado para eliminar.';
        } else {
            $id = $_GET['id'];
            $stmt = $conn->prepare("DELETE FROM tipos_evento WHERE id=?");
            if ($stmt->execute([$id])) {
                $response['success'] = true;
                $response['mensaje'] = 'Tipo de evento eliminado correctamente.';
            } else {
                $response['mensaje'] = 'Error al eliminar el tipo de evento.';
            }
        }
    }
    // --- End of 'eliminar' logic ---

} catch (PDOException $e) {
    // Catch database-related errors
    $response['success'] = false;
    $response['mensaje'] = 'Error en la base de datos: ' . $e->getMessage();
    // In a production environment, log the error and provide a generic message
    error_log("PDO Error in tipo_evento.php: " . $e->getMessage());
} catch (Exception $e) {
    // Catch any other unexpected errors
    $response['success'] = false;
    $response['mensaje'] = 'Ocurrió un error inesperado: ' . $e->getMessage();
    error_log("General Error in tipo_evento.php: " . $e->getMessage());
}

// Always send a JSON response
echo json_encode($response);
?>