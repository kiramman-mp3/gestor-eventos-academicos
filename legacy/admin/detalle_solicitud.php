<?php
require_once '../session.php';
requireAdmin();

$nombre = getUserName();
$apellido = getUserLastname();

include_once '../sql/conexion.php';
$cris = new Conexion();
$conn = $cris->conectar();

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID inválido";
  exit;
}

$stmt = $conn->prepare("SELECT * FROM solicitudes WHERE id = ?");
$stmt->execute([$id]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {
  echo "Solicitud no encontrada.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Detalle de Solicitud</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .detalle-item {
      margin-bottom: 16px;
    }

    .detalle-item span {
      font-weight: bold;
      color: var(--gray-900);
    }

    .seccion-resolucion {
      margin-top: 40px;
      border-top: 2px solid var(--gray-200);
      padding-top: 32px;
    }

    .seccion-resolucion label {
      display: block;
      margin-top: 16px;
      font-weight: bold;
      color: var(--gray-900);
    }

    .seccion-resolucion textarea,
    .seccion-resolucion select {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid var(--gray-200);
      margin-top: 8px;
    }

    .solicitud-img {
      max-width: 300px;
      height: auto;
      display: block;
      margin-top: 16px;
      border: 1px solid var(--gray-200);
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }


    .btn.enviar {
      margin-top: 24px;
    }

    .icono {
      margin-right: 6px;
      color: var(--maroon-dark);
    }
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

    <h1>Detalle de la Solicitud</h1>
    <input type="hidden" name="id_solicitud" value="<?= $solicitud['id'] ?>">
    <div class="detalle-item"><span>Título:</span> <?= htmlspecialchars($solicitud['titulo']) ?></div>
    <div class="detalle-item"><span>Fecha:</span> <?= date("d-m-Y", strtotime($solicitud['fecha'])) ?></div>
    <div class="detalle-item"><span>Tipo:</span> <?= $solicitud['tipo'] ?></div>
    <div class="detalle-item"><span>Descripción:</span><br><?= nl2br(htmlspecialchars($solicitud['descripcion'])) ?>
    </div>
    <div class="detalle-item">
      <span>Justificación:</span><br><?= nl2br(htmlspecialchars($solicitud['justificacion'])) ?>
    </div>
    <div class="detalle-item"><span>Contexto:</span><br><?= nl2br(htmlspecialchars($solicitud['contexto'])) ?></div>
    <div class="detalle-item"><span>Usuario:</span> <?= $solicitud['uname'] ?> (<?= $solicitud['urol'] ?>)</div>

    <?php if ($solicitud['captura']): ?>
      <div class="detalle-item">
        <span>Captura de pantalla:</span><br>
        <img src="<?= $solicitud['captura'] ?>" alt="Captura" class="solicitud-img">
      </div>
    <?php endif; ?>

    <div class="seccion-resolucion">
      <h2><i class="fa-solid fa-pen-to-square icono"></i> Resolver solicitud</h2>
      <form method="POST" action="guardar_resolucion.php">
        <input type="hidden" name="id_solicitud" value="<?= $solicitud['id'] ?>">

        <label>Prioridad:</label>
        <select name="prioridad" required>
          <option value="" disabled selected>Seleccione</option>
          <option value="Alta">Alta</option>
          <option value="Media">Media</option>
          <option value="Baja">Baja</option>
        </select>

        <label>Comentario:</label>
        <textarea name="comentario" rows="4" required placeholder="Describe tu evaluación..."></textarea>

        <label>Estado:</label>
        <select name="estado" required>
          <option value="En revisión">En revisión</option>
          <option value="Aprobado">Aprobado</option>
          <option value="Rechazado">Rechazado</option>
        </select>

        <button type="submit" class="btn enviar"><i class="fa-solid fa-check"></i> Guardar resolución</button>
      </form>
    </div>
  </main>
</body>

</html>