<?php
require_once '../session.php';

requireAdmin();
include '../sql/conexion.php';
$conn = (new Conexion())->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->query("SELECT tipo, contenido FROM info_fisei");
    $datos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $contenido = $row['contenido'];
        if (in_array($row['tipo'], ['autoridad', 'resena', 'carrusel', 'nosotros'])) {
            $contenido = json_decode($contenido, true);
        }
        $datos[$row['tipo']][] = $contenido;
    }
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? '';
    $contenido = '';

    if (!$tipo) {
        http_response_code(400);
        echo "Tipo no especificado.";
        exit;
    }

    try {
        $conn->beginTransaction();

        // Para tipos estructurados con campos individuales
        if (in_array($tipo, ['carrusel', 'nosotros'])) {
            $img = $_POST['img'] ?? '';
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';

            if (!$img || !$titulo || !$descripcion) {
                http_response_code(400);
                echo "Todos los campos de $tipo son obligatorios.";
                exit;
            }

            $contenido = json_encode([
                "img" => $img,
                "titulo" => $titulo,
                "descripcion" => $descripcion
            ]);
        } else {
            $contenido = $_POST['contenido'] ?? '';
            if (!$contenido) {
                http_response_code(400);
                echo "Contenido no puede estar vacío.";
                exit;
            }

            // Tipos únicos (sobrescriben)
            if (!in_array($tipo, ['autoridad', 'resena', 'evento', 'imagen', 'carrusel', 'nosotros'])) {
                $conn->prepare("DELETE FROM info_fisei WHERE tipo = ?")->execute([$tipo]);
            }
        }

        $stmt = $conn->prepare("INSERT INTO info_fisei (tipo, contenido) VALUES (?, ?)");
        $stmt->execute([$tipo, $contenido]);
        $conn->commit();
        echo "OK";
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
