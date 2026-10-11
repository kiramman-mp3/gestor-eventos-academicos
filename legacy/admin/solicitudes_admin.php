<?php
require_once '../session.php';

requireAdmin();

$nombre = getUserName();
$apellido = getUserLastname();

include_once '../sql/conexion.php';
$cris = new Conexion();
$conn = $cris->conectar();

$sql = "SELECT id, titulo, fecha, tipo, descripcion FROM solicitudes ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Solicitudes de Cambio</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>

  </style>
</head>

<body>

  <header class="ctt-header">
    <div class="top-bar">
      <div class="logo"><img src="../uploads/logo.png" alt="Logo FISEI"></div>
      <div class="top-links">
        <div class="link-box">
          <i class="fa-solid fa-arrow-left"></i>
          <div>
            <span class="title">Regresar</span><br>
            <a href="javascript:history.back()">Regrega al Dashboard</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="card">
    <h1>Solicitudes de Cambios</h1>

    <?php if (count($solicitudes) > 0): ?>
      <div class="card-grid">
        <?php foreach ($solicitudes as $sol): ?>
          <div class="card-item">
            <h3 style="color: var(--maroon-dark);"><?= htmlspecialchars($sol['titulo']) ?></h3>
            <p><strong>Fecha:</strong>
              <?= ($sol['fecha'] !== '0000-00-00') ? date("d-m-Y", strtotime($sol['fecha'])) : 'Sin fecha' ?></p>
            <p><strong>Tipo:</strong> <?= $sol['tipo'] ?></p>
            <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($sol['descripcion'])) ?></p>
            <a href="detalle_solicitud.php?id=<?= $sol['id'] ?>" class="btn enviar"
              style="margin-top: 10px; text-decoration: none;">
              <i class="fa-solid fa-eye"></i> Ver detalles
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No hay solicitudes registradas.</p>
    <?php endif; ?>
  </main>



</body>

</html>