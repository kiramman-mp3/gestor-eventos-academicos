<?php
require_once '../session.php';

requireAdmin();
require_once '../sql/conexion.php';

$cris = new Conexion();
$conn = $cris->conectar();

echo "<h2>Prueba de Consultas del Panel de Administración</h2>";

// Consultas a probar
$consultas = [
    'Total de eventos' => "SELECT COUNT(*) as total FROM eventos",
    'Total de estudiantes' => "SELECT COUNT(*) as total FROM estudiantes WHERE rol = 'estudiante'",
    'Total de administradores' => "SELECT COUNT(*) as total FROM estudiantes WHERE rol = 'administrador'",
    'Inscripciones pendientes' => "SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'Esperando aprobación del admin'",
    'Resoluciones pendientes' => "SELECT COUNT(*) as total FROM resoluciones WHERE estado != 'Terminado'",
    'Eventos activos' => "SELECT COUNT(*) as total FROM eventos WHERE fecha_fin >= CURDATE()",
    'Total inscripciones' => "SELECT COUNT(*) as total FROM inscripciones"
];

foreach ($consultas as $nombre => $query) {
    try {
        $stmt = $conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>$nombre:</strong> " . ($result['total'] ?? 0) . "</p>";
    } catch (PDOException $e) {
        echo "<p><strong>$nombre:</strong> <span style='color: red;'>Error: " . $e->getMessage() . "</span></p>";
    }
}

echo "<h3>Estructura de tabla estudiantes:</h3>";
try {
    $stmt = $conn->query("DESCRIBE estudiantes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error al obtener estructura: " . $e->getMessage() . "</p>";
}

echo "<h3>Estructura de tabla inscripciones:</h3>";
try {
    $stmt = $conn->query("DESCRIBE inscripciones");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error al obtener estructura: " . $e->getMessage() . "</p>";
}
?>
