<?php
require_once '../session.php';
require_once '../sql/conexion.php';

if (!isLoggedIn() || getUserRole() !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['evento_id']) || !is_numeric($_GET['evento_id'])) {
    header('Location: panel_admin.php?error=ID de evento inválido');
    exit;
}

$evento_id = (int) $_GET['evento_id'];

try {
    $conexion = (new Conexion())->conectar();

    // Verificar que el evento existe
    $stmt = $conexion->prepare("SELECT estado FROM eventos WHERE id = ?");
    $stmt->execute([$evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        header("Location: panel_admin.php?error=Evento no encontrado");
        exit;
    }

    // Si ya está cerrado, evitar duplicar
    if ($evento['estado'] === 'cerrado') {
        header("Location: administrar_evento.php?id=$evento_id&mensaje=El evento ya estaba cerrado");
        exit;
    }

    // Actualizar estado
    $stmt = $conexion->prepare("UPDATE eventos SET estado = 'cerrado' WHERE id = ?");
    $stmt->execute([$evento_id]);

    header("Location: administrar_evento.php?id=$evento_id&mensaje=Evento finalizado correctamente");
    exit;

} catch (PDOException $e) {
    header("Location: administrar_evento.php?id=$evento_id&error=Error al finalizar el evento");
    exit;
}
