<?php
require_once '../session.php';
require_once '../sql/conexion.php';

// Verificar que el usuario sea administrador
if (!isLoggedIn() || getUserRole() !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

$cris = new Conexion();
$conn = $cris->conectar();

$mensaje = '';
$tipo_mensaje = '';

// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $usuario_id = $_POST['usuario_id'] ?? '';

    try {
        switch ($accion) {
            case 'eliminar':
                $stmt = $conn->prepare("DELETE FROM estudiantes WHERE id = :id AND id != :current_id");
                $stmt->execute([':id' => $usuario_id, ':current_id' => getUserId()]);
                $mensaje = "Usuario eliminado correctamente.";
                $tipo_mensaje = "success";
                break;

            case 'cambiar_rol':
                $nuevo_rol = $_POST['nuevo_rol'] ?? '';
                if (in_array($nuevo_rol, ['estudiante', 'administrador'])) {
                    $stmt = $conn->prepare("UPDATE estudiantes SET rol = :rol WHERE id = :id AND id != :current_id");
                    $stmt->execute([':rol' => $nuevo_rol, ':id' => $usuario_id, ':current_id' => getUserId()]);
                    $mensaje = "Rol actualizado correctamente.";
                    $tipo_mensaje = "success";
                }
                break;

            case 'toggle_estado':
                $stmt = $conn->prepare("UPDATE estudiantes SET verificado = NOT verificado WHERE id = :id");
                $stmt->execute([':id' => $usuario_id]);
                $mensaje = "Estado del usuario actualizado.";
                $tipo_mensaje = "success";
                break;
        }
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener parámetros de filtro
$filtro_rol = $_GET['rol'] ?? '';
$buscar = $_GET['buscar'] ?? '';
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// Construir consulta
$where_conditions = [];
$params = [];

if ($filtro_rol) {
    $where_conditions[] = "rol = :rol";
    $params[':rol'] = $filtro_rol;
}

if ($buscar) {
    $where_conditions[] = "(nombre LIKE :buscar OR apellido LIKE :buscar OR correo LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Obtener usuarios
$query = "SELECT * FROM estudiantes $where_clause ORDER BY fecha_registro DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de usuarios para paginación
$count_query = "SELECT COUNT(*) as total FROM estudiantes $where_clause";
$stmt = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_usuarios / $por_pagina);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin FISEI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
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
    <div class="admin-container">
        <!-- Encabezado -->
        <div class="header-admin mb-4">
            <div>
                <h2>Gestión de Usuarios</h2>
                <small>Panel del Administrador</small>
            </div>
            <a href="crear_admin.php" class="btn enviar">
                <i class="fas fa-user-plus me-2"></i>Nuevo Admin
            </a>
        </div>

        <!-- Mensaje de resultado -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="content-panel mb-4">
            <form method="GET" class="admin-form">
                <div class="admin-form-fields w-100">
                    <div class="form-row">
                        <div>
                            <label for="buscar">Buscar usuario</label>
                            <input type="text" name="buscar" id="buscar" class="form-control-custom"
                                placeholder="Nombre, Apellido o Email" value="<?= htmlspecialchars($buscar) ?>">
                        </div>
                        <div>
                            <label for="rol">Filtrar por rol</label>
                            <select name="rol" id="rol" class="form-control-custom">
                                <option value="">Todos</option>
                                <option value="estudiante" <?= $filtro_rol === 'estudiante' ? 'selected' : '' ?>>Estudiante
                                </option>
                                <option value="administrador" <?= $filtro_rol === 'administrador' ? 'selected' : '' ?>>
                                    Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-submit align-right mt-3">
                        <button type="submit" class="btn enviar">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de usuarios -->
        <div class="content-panel">
            <h3 class="mb-4"><i class="fas fa-users me-2"></i>Usuarios registrados: <?= $total_usuarios ?></h3>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Carrera</th>
                            <th>Estado</th>
                            <th>Registrado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= $usuario['id'] ?></td>
                                <td><strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                <td>
                                    <span
                                        class="badge-admin bg-<?= $usuario['rol'] === 'administrador' ? 'danger' : 'primary' ?>">
                                        <?= ucfirst($usuario['rol']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($usuario['carrera'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $usuario['verificado'] ? 'success' : 'warning' ?>">
                                        <?= $usuario['verificado'] ? 'Verificado' : 'Pendiente' ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                <td>
                                    <?php if ($usuario['id'] !== getUserId()): ?>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <!-- Cambiar rol -->
                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#cambiarRolModal" data-user-id="<?= $usuario['id'] ?>"
                                                data-user-name="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>"
                                                data-current-role="<?= $usuario['rol'] ?>">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <!-- Toggle estado -->
                                            <form method="POST">
                                                <input type="hidden" name="accion" value="toggle_estado">
                                                <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                <button type="submit" class="btn btn-outline-secondary btn-sm"
                                                    onclick="return confirm('¿Cambiar estado de verificación?')">
                                                    <i
                                                        class="fas fa-<?= $usuario['verificado'] ? 'user-slash' : 'user-check' ?>"></i>
                                                </button>
                                            </form>
                                            <!-- Eliminar -->
                                            <form method="POST">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-info">Tu cuenta</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No se encontraron usuarios.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="form-submit align-right mt-4">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?pagina=<?= $pagina - 1 ?>&rol=<?= urlencode($filtro_rol) ?>&buscar=<?= urlencode($buscar) ?>">Anterior</a>
                            </li>
                            <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?pagina=<?= $i ?>&rol=<?= urlencode($filtro_rol) ?>&buscar=<?= urlencode($buscar) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $pagina >= $total_paginas ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?pagina=<?= $pagina + 1 ?>&rol=<?= urlencode($filtro_rol) ?>&buscar=<?= urlencode($buscar) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Cambiar Rol -->
    <div class="modal fade" id="cambiarRolModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="cambiar_rol">
                    <input type="hidden" name="usuario_id" id="modal-user-id">
                    <p>¿Cambiar el rol del usuario <strong id="modal-user-name"></strong>?</p>
                    <select name="nuevo_rol" id="nuevo_rol" class="form-control-custom" required>
                        <option value="estudiante">Estudiante</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn enviar">Cambiar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cambiarRolModal = document.getElementById('cambiarRolModal');
            cambiarRolModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                document.getElementById('modal-user-id').value = button.dataset.userId;
                document.getElementById('modal-user-name').textContent = button.dataset.userName;
                document.getElementById('nuevo_rol').value = button.dataset.currentRole;
            });
        });
    </script>
</body>

</html>