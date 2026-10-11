<?php
require_once '../session.php';

requireAdmin();


include_once '../sql/conexion.php';
$cris = new Conexion();
$conn = $cris->conectar();

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "ID inválido.";
  exit;
}
$nombre = getUserName();
$apellido = getUserLastname();


// Obtener la resolución y la solicitud asociada
$stmt = $conn->prepare("
    SELECT r.*, s.titulo, s.tipo, s.descripcion, s.justificacion, s.contexto, s.fecha, s.uname, s.urol, s.captura 
    FROM resoluciones r 
    JOIN solicitudes s ON r.id_solicitud = s.id 
    WHERE r.id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
  echo "Resolución no encontrada.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Editar Resolución</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .detalle-item {
      margin-bottom: 12px;
    }

    .form-label {
      display: block;
      margin-top: 20px;
      font-weight: bold;
    }

    .form-control,
    select,
    textarea {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border: 1px solid var(--gray-200);
      border-radius: 6px;
    }

    .solicitud-img {
      max-width: 300px;
      border-radius: 6px;
      margin-top: 10px;
      border: 1px solid var(--gray-200);
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
    <h1><i class="fa-solid fa-pen-to-square icono"></i> Editar Resolución</h1>

    <section style="margin-bottom: 40px;">
      <h2>Datos de la solicitud</h2>
      <input type="hidden" name="id" value="<?= $data['id'] ?>">
      <div class="detalle-item"><strong>Título:</strong> <?= htmlspecialchars($data['titulo']) ?></div>
      <div class="detalle-item"><strong>Tipo:</strong> <?= $data['tipo'] ?></div>
      <div class="detalle-item"><strong>Fecha:</strong> <?= date('d-m-Y', strtotime($data['fecha'])) ?></div>
      <div class="detalle-item">
        <strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($data['descripcion'])) ?>
      </div>
      <div class="detalle-item">
        <strong>Justificación:</strong><br><?= nl2br(htmlspecialchars($data['justificacion'])) ?>
      </div>
      <div class="detalle-item"><strong>Contexto:</strong><br><?= nl2br(htmlspecialchars($data['contexto'])) ?>
      </div>
      <div class="detalle-item"><strong>Usuario:</strong> <?= $data['uname'] ?> (<?= $data['urol'] ?>)</div>

      <?php if ($data['captura']): ?>
        <div class="detalle-item">
          <strong>Captura de pantalla:</strong><br>
          <img src="<?= $data['captura'] ?>" class="solicitud-img" alt="Captura">
        </div>
      <?php endif; ?>
    </section>

    <h2>Modificar resolución</h2>

    <form action="guardar_resolucion.php" method="POST">
      <input type="hidden" name="id" value="<?= $data['id'] ?>">

      <label class="form-label">Prioridad:</label>
      <select name="prioridad" required>
        <option value="Alta" <?= $data['prioridad'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
        <option value="Media" <?= $data['prioridad'] === 'Media' ? 'selected' : '' ?>>Media</option>
        <option value="Baja" <?= $data['prioridad'] === 'Baja' ? 'selected' : '' ?>>Baja</option>
      </select>

      <label class="form-label">Estado:</label>
      <select name="estado" required>
        <option value="En revisión" <?= $data['estado'] === 'En revisión' ? 'selected' : '' ?>>En revisión</option>
        <option value="Aprobado" <?= $data['estado'] === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
        <option value="Rechazado" <?= $data['estado'] === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
        <option value="Terminado" <?= $data['estado'] === 'Terminado' ? 'selected' : '' ?>>Terminado</option>
      </select>

      <label class="form-label">Comentario:</label>
      <textarea name="comentario" rows="4" required><?= htmlspecialchars($data['comentario']) ?></textarea>

      <button type="submit" class="btn enviar" style="margin-top: 24px;">
        <i class="fa-solid fa-save"></i> Guardar cambios
      </button>
    </form>
  </main>

</body>

</html>