<?php
require_once '../session.php';
include('../sql/conexion.php');

$nombre = getUserName();
$apellido = getUserLastname();


if (!isLoggedIn()) {
  header('Location: login.php');
  exit;
}

$usuarioId = getUserId();
$cris = new Conexion();
$conexion = $cris->conectar();

$stmt = $conexion->prepare("
    SELECT e.id AS evento_id, e.nombre_evento, e.fecha_inicio, e.fecha_fin, e.ponentes,
           e.horas, e.requiere_nota, e.requiere_asistencia, e.nota_minima, e.asistencia_minima,
           i.estado, i.nota, i.asistencia, i.comprobante_pago
    FROM inscripciones i
    JOIN eventos e ON i.evento_id = e.id
    WHERE i.usuario_id = ?
");
$stmt->execute([$usuarioId]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Mis Cursos</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

  <header class="ctt-header">
    <div class="top-bar">
      <div class="logo">
        <img src="../uploads/logo.png" alt="Logo CTT">
      </div>
      <div class="top-links">
        <div class="link-box">
          <i class="fa-solid fa-arrow-left"></i>
          <div>
            <span class="title">Regresar</span><br>
            <a href="javascript:history.back()">Página anterior</a>
          </div>
        </div>
      </div>
    </div>
  </header>


  <main>
    <div class="card">
      <h1>Mis Cursos Inscritos</h1>
      <?php if (count($cursos) > 0): ?>
        <div class="table-responsive">
          <table class="table table-custom">
            <thead>
              <tr>
                <th>Curso</th>
                <th>Fechas</th>
                <th>Ponente</th>
                <th>Horas</th>
                <th>Nota</th>
                <th>Asistencia</th>
                <th>Estado</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cursos as $curso): ?>
                <tr>
                  <td><?= htmlspecialchars($curso['nombre_evento']) ?></td>
                  <td><?= $curso['fecha_inicio'] ?> al <?= $curso['fecha_fin'] ?></td>
                  <td><?= htmlspecialchars($curso['ponentes']) ?></td>
                  <td><?= $curso['horas'] ?></td>
                  <td><?= is_null($curso['nota']) ? '-' : $curso['nota'] ?></td>
                  <td><?= is_null($curso['asistencia']) ? '-' : $curso['asistencia'] . '%' ?></td>
                  <td><?= htmlspecialchars($curso['estado']) ?></td>
                  <td class="text-center">
                    <?php
                    // Verificación dinámica basada en los requisitos del evento
                    $nota_minima = $curso['nota_minima'] ?? 7.0;
                    $asistencia_minima = $curso['asistencia_minima'] ?? 70.0;
                    
                    $cumple_nota = !$curso['requiere_nota'] || ($curso['nota'] !== null && $curso['nota'] >= $nota_minima);
                    $cumple_asistencia = !$curso['requiere_asistencia'] || ($curso['asistencia'] !== null && $curso['asistencia'] >= $asistencia_minima);
                    
                    $aptoCertificado = (
                      $curso['estado'] === 'Pagado' &&
                      $cumple_nota &&
                      $cumple_asistencia
                    );
                    ?>
                    <?php if ($aptoCertificado): ?>
                      <a href="certificado_pdf.php?evento_id=<?= $curso['evento_id'] ?>" target="_blank"
                        class="btn btn-outline-primary btn-sm">
                        Generar PDF
                      </a>
                    <?php elseif ($curso['estado'] === 'En espera de orden de pago'): ?>
                      <form method="POST" action="subir_comprobante.php" enctype="multipart/form-data" class="d-inline">
                        <input type="hidden" name="evento_id" value="<?= $curso['evento_id'] ?>">
                        <input type="file" name="comprobante" accept="application/pdf,image/*" required style="display: none;"
                          onchange="this.form.submit()">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                          onclick="this.previousElementSibling.click()">
                          Subir comprobante
                        </button>
                      </form>
                    <?php elseif (!empty($curso['comprobante_pago'])): ?>
                      <a href="<?= $curso['comprobante_pago'] ?>" target="_blank" class="btn btn-white btn-sm">
                        Ver comprobante
                      </a>
                    <?php else: ?>
                      <span class="text-muted">No disponible</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted">No estás inscrito en ningún curso.</p>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>