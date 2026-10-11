<?php
require_once '../session.php';
include('../sql/conexion.php');

requireAdmin();

$nombre = getUserName();
$apellido = getUserLastname();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: lista_eventos.php');
    exit;
}

$id = (int) $_GET['id'];
$conexion = (new Conexion())->conectar();

$stmt = $conexion->prepare("
    SELECT e.*, t.nombre AS tipo_evento, c.nombre AS categoria
    FROM eventos e
    JOIN tipos_evento t ON e.tipo_evento_id = t.id
    JOIN categorias_evento c ON e.categoria_id = c.id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$evento = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$evento) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Evento no encontrado</title>
        <link rel='stylesheet' href='../css/estilos.css'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'>
    </head>
    <body>
        <div class='admin-container content-panel' style='text-align: center;'>
            <i class='fas fa-exclamation-triangle' style='font-size: 3em; color: var(--primary-color); margin-bottom: 15px;'></i>
            <h2>Evento no encontrado</h2>
            <p>El evento que buscas no existe o ha sido eliminado.</p>
            <a href='lista_eventos.php' class='btn-outline-primary'>
                <i class='fas fa-arrow-left'></i> Volver a la lista de eventos
            </a>
        </div>
    </body>
    </html>";
    exit;
}

$reqStmt = $conexion->prepare("SELECT descripcion, tipo FROM requisitos_evento WHERE evento_id = ?");
$reqStmt->execute([$id]);
$requisitos = $reqStmt->fetchAll(PDO::FETCH_ASSOC);

$insStmt = $conexion->prepare("
    SELECT i.id, i.estado, i.nota, i.asistencia,
           e.nombre, e.apellido, e.cedula, e.correo
    FROM inscripciones i
    JOIN estudiantes e ON i.usuario_id = e.id
    WHERE i.evento_id = ?
");
$insStmt->execute([$id]);
$inscritos = $insStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administrar Evento</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                        <span class="title">Regresar</span>
                        <br>
                        <a href="javascript:history.back()">Página anterior</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="admin-container">
        <div class="content-panel">
            <h2 class="mb-4"><?= htmlspecialchars($evento['nombre_evento']) ?></h2>

            <div class="mb-4">
                <p><strong>Tipo:</strong> <?= htmlspecialchars($evento['tipo_evento']) ?></p>
                <p><strong>Categoría:</strong> <?= htmlspecialchars($evento['categoria']) ?></p>
                <p><strong>Fechas:</strong> <?= $evento['fecha_inicio'] ?> al <?= $evento['fecha_fin'] ?></p>
                <p><strong>Inscripciones:</strong> <?= $evento['fecha_inicio_inscripciones'] ?> al
                    <?= $evento['fecha_fin_inscripciones'] ?>
                </p>
                <p><strong>Horas académicas:</strong> <?= $evento['horas'] ?></p>
                <p><strong>Ponente:</strong> <?= htmlspecialchars($evento['ponentes']) ?></p>
                <p><strong>Cupos disponibles:</strong> <?= $evento['cupos'] ?></p>
                <p><strong>Estado:</strong> <?= $evento['estado'] ?></p>

                <!-- Botón para finalizar curso -->
                <?php if (strtolower($evento['estado']) !== 'cerrado'): ?>
                    <a href="finalizar_evento.php?evento_id=<?= $evento['id'] ?>" class="btn btn-danger"
                        onclick="return confirm('¿Finalizar este evento? No se podrá revertir.');">
                        Finalizar evento
                    </a>

                <?php endif; ?>


                <div class="info-box mt-3">
                    <h6><i class="fas fa-info-circle"></i> Requisitos del Evento:</h6>
                    <ul class="mb-0">
                        <li><strong>Calificación:</strong>
                            <?= $evento['requiere_nota'] ? 'Obligatoria (mínimo: ' . ($evento['nota_minima'] ?? 7.0) . ')' : 'Opcional' ?>
                        </li>
                        <li><strong>Asistencia:</strong>
                            <?= $evento['requiere_asistencia'] ? 'Obligatoria (mínimo: ' . ($evento['asistencia_minima'] ?? 70.0) . '%)' : 'Opcional' ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="text-right mb-4">
                <a href="pdf_evento.php?id=<?= $id ?>" target="_blank" class="btn-outline-primary">
                    <i class="fa fa-file-pdf"></i> Imprimir PDF
                </a>
            </div>

            <h5>Requisitos del Evento</h5>
            <?php if (count($requisitos) > 0): ?>
                <ul class="requirements-list mb-4">
                    <?php foreach ($requisitos as $req): ?>
                        <li class="list-item-custom"><?= htmlspecialchars($req['descripcion']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">Este evento no tiene requisitos registrados.</p>
            <?php endif; ?>

            <h5 class="mt-5">Inscritos (<?= count($inscritos) ?>)</h5>

<?php if (count($inscritos) > 0): ?>
    <form id="formNotas" method="POST" action="actualizar_notas.php">
        <input type="hidden" name="evento_id" value="<?= $id ?>">

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estudiante</th>
                        <th>Cédula</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Nota <?= $evento['requiere_nota'] ? '(mín. ' . $evento['nota_minima'] . ')' : '' ?></th>
                        <th>Asistencia <?= $evento['requiere_asistencia'] ? '(mín. ' . $evento['asistencia_minima'] . '%)' : '' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inscritos as $i => $ins): 
                        $editable = $evento['estado'] === 'cerrado' && strtolower($ins['estado']) === 'pagado';
                    ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($ins['nombre'] . ' ' . $ins['apellido']) ?></td>
                            <td><?= htmlspecialchars($ins['cedula']) ?></td>
                            <td><?= htmlspecialchars($ins['correo']) ?></td>
                            <td><?= htmlspecialchars($ins['estado']) ?></td>
                            <td>
                                <input type="number" name="notas[<?= $ins['id'] ?>]" class="form-control-custom"
                                    value="<?= is_null($ins['nota']) ? '' : $ins['nota'] ?>" step="0.01" min="0" max="10"
                                    <?= $evento['requiere_nota'] ? 'required' : '' ?>
                                    <?= $editable ? '' : 'readonly' ?>>
                                <?php if ($evento['requiere_nota']): ?>
                                    <small class="text-danger-small">* Mínimo requerido: <?= $evento['nota_minima'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" name="asistencias[<?= $ins['id'] ?>]" class="form-control-custom"
                                    value="<?= is_null($ins['asistencia']) ? '' : $ins['asistencia'] ?>" step="0.01"
                                    min="0" max="100" <?= $evento['requiere_asistencia'] ? 'required' : '' ?>
                                    <?= $editable ? '' : 'readonly' ?>>
                                <?php if ($evento['requiere_asistencia']): ?>
                                    <small class="text-danger-small">* Mínimo requerido: <?= $evento['asistencia_minima'] ?>%</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($evento['estado'] === 'cerrado'): ?>
            <div class="text-right mt-3">
                <button type="submit" class="btn-primary-custom">Guardar Cambios</button>
            </div>
        <?php else: ?>
            <p class="text-warning mt-3">
                <strong>Este evento aún no ha sido finalizado.</strong> Finalízalo para habilitar el registro y edición de notas y asistencias.
            </p>
        <?php endif; ?>
    </form>
<?php else: ?>
    <p class="text-muted">No hay inscritos en este evento.</p>
<?php endif; ?>

        </div>
    </main>
</body>

</html>