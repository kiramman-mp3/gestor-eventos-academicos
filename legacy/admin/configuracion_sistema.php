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

// Manejar actualización de configuraciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        switch ($accion) {
            case 'actualizar_info_general':
                $nombre_institucion = $_POST['nombre_institucion'] ?? '';
                $email_contacto = $_POST['email_contacto'] ?? '';
                $telefono_contacto = $_POST['telefono_contacto'] ?? '';

                // Actualizar o insertar configuración general
                $stmt = $conn->prepare("
                    INSERT INTO configuracion (clave, valor) VALUES 
                    ('nombre_institucion', :nombre),
                    ('email_contacto', :email),
                    ('telefono_contacto', :telefono)
                    ON DUPLICATE KEY UPDATE 
                    valor = VALUES(valor)
                ");
                $stmt->execute([
                    ':nombre' => $nombre_institucion,
                    ':email' => $email_contacto,
                    ':telefono' => $telefono_contacto
                ]);

                $mensaje = "Información general actualizada correctamente.";
                $tipo_mensaje = "success";
                break;

            case 'actualizar_limites':
                $max_inscripciones = (int) ($_POST['max_inscripciones'] ?? 0);
                $dias_limite_cancelacion = (int) ($_POST['dias_limite_cancelacion'] ?? 0);

                $stmt = $conn->prepare("
                    INSERT INTO configuracion (clave, valor) VALUES 
                    ('max_inscripciones_estudiante', :max_insc),
                    ('dias_limite_cancelacion', :dias_limit)
                    ON DUPLICATE KEY UPDATE 
                    valor = VALUES(valor)
                ");
                $stmt->execute([
                    ':max_insc' => $max_inscripciones,
                    ':dias_limit' => $dias_limite_cancelacion
                ]);

                $mensaje = "Límites del sistema actualizados correctamente.";
                $tipo_mensaje = "success";
                break;
        }
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener configuraciones actuales
$configuraciones = [];
try {
    $stmt = $conn->query("SELECT clave, valor FROM configuracion");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $configuraciones[$row['clave']] = $row['valor'];
    }
} catch (PDOException $e) {
    // Si no existe la tabla configuracion, la creamos
    try {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS configuracion (
                id INT AUTO_INCREMENT PRIMARY KEY,
                clave VARCHAR(100) NOT NULL UNIQUE,
                valor TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    } catch (PDOException $e2) {
        $mensaje = "Error al crear tabla de configuración: " . $e2->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener estadísticas del sistema
$estadisticas = [];
try {
    $queries = [
        'total_eventos_activos' => "SELECT COUNT(*) as count FROM eventos WHERE fecha_fin >= CURDATE()",
        'total_inscripciones_mes' => "SELECT COUNT(*) as count FROM inscripciones",
        'espacio_uploads' => "SELECT ROUND(SUM(LENGTH(contenido))/1024/1024, 2) as size_mb FROM info_fisei WHERE contenido IS NOT NULL"
    ];

    foreach ($queries as $key => $query) {
        $stmt = $conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $estadisticas[$key] = $result['count'] ?? $result['size_mb'] ?? 0;
    }
} catch (PDOException $e) {
    // Si hay error, usar valores por defecto
    $estadisticas = [
        'total_eventos_activos' => 0,
        'total_inscripciones_mes' => 0,
        'espacio_uploads' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema - Admin FISEI</title>
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
        <!-- Header personalizado -->
        <div class="header-admin mb-4">
            <div>
                <h2>Configuración del Sistema</h2>
                <small>Panel del Administrador</small>
            </div>
        </div>

        <!-- Mensaje -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="content-panel mb-4">
            <h4><i class="fas fa-chart-bar me-2"></i>Estadísticas del Sistema</h4>
            <div class="row text-center">
                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <h3><?= $estadisticas['total_eventos_activos'] ?? 0 ?></h3>
                        <p>Eventos Activos</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <h3><?= $estadisticas['total_inscripciones_mes'] ?? 0 ?></h3>
                        <p>Total de Inscripciones</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="info-box">
                        <h3><?= $estadisticas['espacio_uploads'] ?? 0 ?> MB</h3>
                        <p>Espacio de Archivos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información General -->
        <div class="content-panel mb-4">
            <h4><i class="fas fa-building me-2"></i>Información General</h4>
            <form method="POST" class="admin-form">
                <input type="hidden" name="accion" value="actualizar_info_general">
                <div class="form-row">
                    <div>
                        <label for="nombre_institucion">Institución</label>
                        <input type="text" class="form-control-custom" name="nombre_institucion" id="nombre_institucion"
                            value="<?= htmlspecialchars($configuraciones['nombre_institucion'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="email_contacto">Email</label>
                        <input type="email" class="form-control-custom" name="email_contacto" id="email_contacto"
                            value="<?= htmlspecialchars($configuraciones['email_contacto'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="telefono_contacto">Teléfono</label>
                        <input type="text" class="form-control-custom" name="telefono_contacto" id="telefono_contacto"
                            value="<?= htmlspecialchars($configuraciones['telefono_contacto'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-submit mt-3">
                    <button type="submit" class="btn enviar"><i class="fas fa-save me-2"></i>Guardar
                        Información</button>
                </div>
            </form>
        </div>

        <!-- Límites del Sistema -->
        <div class="content-panel mb-4">
            <h4><i class="fas fa-sliders-h me-2"></i>Límites del Sistema</h4>
            <form method="POST" class="admin-form">
                <input type="hidden" name="accion" value="actualizar_limites">
                <div class="form-row">
                    <div>
                        <label for="max_inscripciones">Máx. inscripciones</label>
                        <input type="number" class="form-control-custom" name="max_inscripciones" id="max_inscripciones"
                            min="1" max="20"
                            value="<?= htmlspecialchars($configuraciones['max_inscripciones_estudiante'] ?? '5') ?>">
                    </div>
                    <div>
                        <label for="dias_limite_cancelacion">Días límite cancelación</label>
                        <input type="number" class="form-control-custom" name="dias_limite_cancelacion"
                            id="dias_limite_cancelacion" min="0" max="30"
                            value="<?= htmlspecialchars($configuraciones['dias_limite_cancelacion'] ?? '3') ?>">
                    </div>
                </div>
                <div class="form-submit mt-3">
                    <button type="submit" class="btn enviar"><i class="fas fa-save me-2"></i>Actualizar Límites</button>
                </div>
            </form>
        </div>

        <!-- Herramientas -->
        <div class="content-panel mb-4">
            <h4><i class="fas fa-tools me-2"></i>Herramientas de Mantenimiento</h4>
            <div class="d-grid gap-3">
                <button type="button" class="btn btn-outline-warning" onclick="limpiarCacheSistema()">
                    <i class="fas fa-broom me-2"></i>Limpiar Caché
                </button>
                <button type="button" class="btn btn-outline-info" onclick="exportarConfiguracion()">
                    <i class="fas fa-download me-2"></i>Exportar Configuración
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="verLogsActividad()">
                    <i class="fas fa-list me-2"></i>Ver Logs
                </button>
                <hr>
                <div class="alert alert-warning small">
                    <strong>Zona de Peligro:</strong> Estas acciones pueden afectar el sistema.
                </div>
                <button type="button" class="btn btn-danger" onclick="confirmarReinicioSistema()">
                    <i class="fas fa-sync-alt me-2"></i>Reiniciar Sistema
                </button>
            </div>
        </div>

        <!-- Información de Versión -->
        <div class="content-panel text-center">
            <p><strong>Versión del Sistema:</strong> <span class="badge bg-primary">v2.0.0</span></p>
            <p><strong>Última Actualización:</strong> <?= date('d/m/Y') ?></p>
            <p><strong>Base de Datos:</strong> <span class="badge bg-success">MySQL
                    <?= $conn->getAttribute(PDO::ATTR_SERVER_VERSION) ?></span></p>
            <p><strong>PHP:</strong> <span class="badge bg-info">v<?= PHP_VERSION ?></span></p>
        </div>
    </div>

    <script>
        function limpiarCacheSistema() {
            if (confirm('¿Está seguro de que desea limpiar el caché del sistema?')) {
                alert('Función de limpieza de caché ejecutada (simulado)');
            }
        }
        function exportarConfiguracion() {
            alert('Exportando configuración... (función por implementar)');
        }
        function verLogsActividad() {
            alert('Mostrando logs de actividad... (función por implementar)');
        }
        function confirmarReinicioSistema() {
            if (confirm('¿ESTÁ SEGURO de que desea reiniciar el sistema? Esto afectará a todos los usuarios conectados.')) {
                if (confirm('Esta acción es irreversible. ¿Continuar?')) {
                    alert('Reiniciando sistema... (función por implementar)');
                }
            }
        }
    </script>
</body>

</html>