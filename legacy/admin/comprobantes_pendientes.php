<?php
require_once '../session.php';
include('../sql/conexion.php');

requireAdmin();

$nombre = getUserName();
$apellido = getUserLastname();


$cris = new Conexion();
$conexion = $cris->conectar();

$stmt = $conexion->prepare("
    SELECT i.id, i.usuario_id, i.evento_id, i.comprobante_pago, i.estado,
           e.nombre_evento,
           es.nombre, es.apellido, es.cedula
    FROM inscripciones i
    JOIN eventos e ON i.evento_id = e.id
    JOIN estudiantes es ON i.usuario_id = es.id
    WHERE i.estado = 'Esperando aprobación del admin'
");
$stmt->execute();
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Comprobantes Pendientes</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
  <main>
    <div class="card">
      <h1>Comprobantes Pendientes</h1>

      <?php if (count($pendientes) > 0): ?>
        <table class="table table-custom">
          <thead>
            <tr>
              <th>Estudiante</th>
              <th>Cédula</th>
              <th>Curso</th>
              <th>Archivo</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pendientes as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
                <td><?= htmlspecialchars($row['cedula']) ?></td>
                <td><?= htmlspecialchars($row['nombre_evento']) ?></td>
                <td>
                  <a href="<?= $row['comprobante_pago'] ?>" target="_blank" class="btn btn-white btn-sm">Ver archivo</a>
                </td>
                <td>
                  <form action="aprobar_comprobante.php" method="POST" class="d-inline">
                    <input type="hidden" name="inscripcion_id" value="<?= $row['id'] ?>">
                    <button name="accion" value="aprobar" class="btn btn-primary btn-sm">Aprobar</button>
                    <button name="accion" value="rechazar" class="btn btn-outline-secondary btn-sm">Rechazar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted">No hay comprobantes pendientes.</p>
      <?php endif; ?>
    </div>
  </main>



</body>

</html>