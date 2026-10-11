<?php
require_once '../session.php';

requireAdmin();


$nombre = getUserName();
$apellido = getUserLastname();


include_once '../sql/conexion.php';

$cris = new Conexion();
$conn = $cris->conectar();

// Obtener todas las resoluciones con info de la solicitud
$sql = "SELECT r.*, s.titulo, s.tipo, s.fecha 
        FROM resoluciones r
        JOIN solicitudes s ON r.id_solicitud = s.id
        ORDER BY r.fecha_resolucion DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$resoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Todas las Resoluciones</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .res-card {
      border: 1px solid var(--gray-200);
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 20px;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .res-card h4 {
      margin-top: 0;
      color: var(--maroon-dark);
    }

    .res-info {
      margin-bottom: 8px;
    }

    .btn-editar {
      margin-top: 12px;
      display: inline-block;
    }
  </style>
</head>

<body>
  <header class="ctt-header">
    <div class="top-bar">
      <div class="logo">
        <img src="../uploads/logo.png" alt="Logo FISEI">
      </div>
      <div class="top-links">
        <div class="link-box">
          <i class="fa-solid fa-arrow-left"></i>
          <div>
            <span class="title">Regresar</span><br>
            <a href="javascript:history.back()">Regresa al Dashboard</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="card">
    <h1>Todas las Resoluciones</h1>

    <?php if (count($resoluciones) > 0): ?>
      <?php foreach ($resoluciones as $res): ?>
        <div class="res-card">
          <h4><?= htmlspecialchars($res['titulo']) ?> (<?= $res['tipo'] ?>)</h4>
          <div class="res-info"><strong>Estado:</strong> <?= $res['estado'] ?></div>
          <div class="res-info"><strong>Prioridad:</strong> <?= $res['prioridad'] ?></div>
          <div class="res-info"><strong>Comentario:</strong><br><?= nl2br(htmlspecialchars($res['comentario'])) ?>
          </div>
          <div class="res-info"><strong>Fecha de resolución:</strong>
            <?= date('d-m-Y', strtotime($res['fecha_resolucion'])) ?></div>
          <a href="editar_resolucion.php?id=<?= $res['id'] ?>" class="btn enviar btn-editar" style="text-decoration: none;">
            <i class="fa-solid fa-pen-to-square"></i> Editar
          </a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay resoluciones registradas.</p>
    <?php endif; ?>
  </main> 

</body>

</html>