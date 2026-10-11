<?php
require_once '../session.php';

requireAdmin();
requirePost();
include_once '../sql/conexion.php';

$cris = new Conexion();
$conn = $cris->conectar();

// Recibir datos
$id = $_POST['id'] ?? null; // este solo vendrá en modo "editar"
$id_solicitud = $_POST['id_solicitud'] ?? null;
$prioridad = $_POST['prioridad'] ?? '';
$comentario = $_POST['comentario'] ?? '';
$estado = $_POST['estado'] ?? '';

// Validar campos
if ((!$id && !$id_solicitud) || !$prioridad || !$comentario || !$estado) {
    echo "❌ Todos los campos son obligatorios.";
    exit;
}

try {
    if ($id) {
        // ACTUALIZAR resolución existente
        $stmt = $conn->prepare("UPDATE resoluciones SET prioridad = :prioridad, comentario = :comentario, estado = :estado WHERE id = :id");
        $stmt->execute([
            ':prioridad' => $prioridad,
            ':comentario' => $comentario,
            ':estado' => $estado,
            ':id' => $id
        ]);
    } else {
        // INSERTAR nueva resolución
        $stmt = $conn->prepare("INSERT INTO resoluciones (id_solicitud, prioridad, comentario, estado) VALUES (:id_solicitud, :prioridad, :comentario, :estado)");
        $stmt->execute([
            ':id_solicitud' => $id_solicitud,
            ':prioridad' => $prioridad,
            ':comentario' => $comentario,
            ':estado' => $estado
        ]);
    }

    // Redirigir
    header("Location: ver_resoluciones.php?success=1");
    exit;

} catch (PDOException $e) {
    echo "❌ Error al guardar: " . $e->getMessage();
}
?>
