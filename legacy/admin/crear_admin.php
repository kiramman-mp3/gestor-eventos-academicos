<?php
require_once '../sql/conexion.php';
require_once '../session.php';

requireAdmin();

$cris = new Conexion();
$conexion = $cris->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $correo = $_POST['correo'];
  $cedula = $_POST['cedula'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $genero = $_POST['genero'];
  $rol = $_POST['rol'] ?? '';
  $fecha_nacimiento = $_POST['anio'] . '-' . $_POST['mes'] . '-' . $_POST['dia'];

  if (!in_array($rol, [ROL_ADMIN, ROL_ESTUDIANTE], true)) {
    $error = "Rol inválido.";
  } elseif (str_ends_with($correo, '@uta.edu.ec')) {
    $tipo = 'institucional';
  } elseif (preg_match('/@.+\..+/', $correo)) {
    $tipo = 'publico';
  } else {
    $error = "Correo inválido.";
  }

  if (!isset($error)) {
    try {
      $stmt = $conexion->prepare("INSERT INTO estudiantes 
        (nombre, apellido, correo, password, cedula, genero, fecha_nacimiento, tipo, rol, verificado)
        VALUES (:nombre, :apellido, :correo, :password, :cedula, :genero, :fecha_nacimiento, :tipo, :rol, :verificado)");

      $stmt->bindValue(':nombre', $nombre);
      $stmt->bindValue(':apellido', $apellido);
      $stmt->bindValue(':correo', $correo);
      $stmt->bindValue(':password', $password);
      $stmt->bindValue(':cedula', $cedula);
      $stmt->bindValue(':genero', $genero);
      $stmt->bindValue(':fecha_nacimiento', $fecha_nacimiento);
      $stmt->bindValue(':tipo', $tipo);
      $stmt->bindValue(':rol', $rol);
      $stmt->bindValue(':verificado', 1, PDO::PARAM_INT);

      if ($stmt->execute()) {
        header("Location: panel_admin.php?mensaje=Admin creado con éxito");
        exit;
      } else {
        $error = "Este correo ya está registrado.";
      }
    } catch (PDOException $e) {
      $error = "Error en el registro: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Registrar Nuevo Administrador</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/registro-estilos.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    
    .crear-admin-wrapper { 
        position: static !important; 
        top: auto !important;
        left: auto !important;
        transform: none !important; 
        margin: auto; 
        z-index: 10;
        width: 100%;
        max-width: 650px;
        padding: 20px;
    }

    body {
        min-height: 100vh;
        display: flex; 
        flex-direction: column; 
    }
    .d-flex.justify-content-center.align-items-center {
        flex-grow: 1; 
    }

    .ctt-header {
      
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

  <div class="d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 130px);">
    <main class="registro-wrapper crear-admin-wrapper"> 
      <div class="card-custom">
        <h1 class="text-center mb-4">Agregar nuevo administrador</h1>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="row mb-3">
            <div class="col">
              <input type="text" name="nombre" placeholder="Nombre" class="form-control" required>
            </div>
            <div class="col">
              <input type="text" name="apellido" placeholder="Apellido" class="form-control" required>
            </div>
          </div>

          <div class="mb-3">
            <input type="text" name="cedula" placeholder="Cédula" pattern="\d{10}" class="form-control" required>
          </div>

          <div class="row mb-3">
            <div class="col">
              <select name="dia" class="form-select" required>
                <option disabled selected>Día</option>
                <?php for ($d = 1; $d <= 31; $d++): ?>
                  <option value="<?= $d ?>"><?= $d ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col">
              <select name="mes" class="form-select" required>
                <option disabled selected>Mes</option>
                <?php
                $meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
                foreach ($meses as $num => $mes): ?>
                  <option value="<?= $num ?>"><?= $mes ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col">
              <select name="anio" class="form-select" required>
                <option disabled selected>Año</option>
                <?php for ($y = date('Y') - 10; $y >= 1960; $y--): ?>
                  <option value="<?= $y ?>"><?= $y ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Género:</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="genero" value="Mujer" required>
              <label class="form-check-label">Mujer</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="genero" value="Hombre" required>
              <label class="form-check-label">Hombre</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="genero" value="Otro" required>
              <label class="form-check-label">Otro</label>
            </div>
          </div>

          <div class="mb-3">
            <input type="email" name="correo" placeholder="Correo" class="form-control" required>
          </div>

          <div class="mb-3">
            <input type="password" name="password" placeholder="Contraseña" class="form-control" required>
          </div>

          <div class="mb-4">
            <label for="rol" class="form-label">Rol del usuario:</label>
            <select name="rol" class="form-select" required>
              <option value="administrador">Administrador</option>
            </select>
          </div>

          <div class="text-center">
            <button type="submit" class="boton-grande">Crear administrador</button>
          </div>
        </form>

      </div>
    </main>
  </div>
</body>

</html>