<?php
include('sql/conexion.php');
include('session.php');

$cris = new Conexion();
$conn = $cris->conectar();

$stmt = $conn->prepare("SELECT contenido FROM info_fisei WHERE tipo = 'carrusel'");
$stmt->execute();
$imagenes = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $contenido = json_decode($row['contenido'], true);
  if (isset($contenido['img'])) {
    $imagenes[] = $contenido['img'];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $correo = $_POST['correo'];
  $password = $_POST['password'];

  try {
    $query = $conn->prepare("SELECT * FROM estudiantes WHERE correo = :correo");
    $query->execute([':correo' => $correo]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Primero la contraseña: el estado de verificación solo se revela a quien conoce la clave
    if ($user && password_verify($password, $user['password'])) {
      if (!$user['verificado']) {
        header('Location: verificar.php?correo=' . urlencode($correo));
        exit;
      }

      loginUser($user);

      // Redirigir según el rol del usuario
      if ($user['rol'] === 'administrador') {
        header("Location: admin/panel_admin.php");
      } else {
        header("Location: ver_cursos.php");
      }
      exit;
    } else {
      $error = "Correo o contraseña incorrectos";
    }
  } catch (PDOException $e) {
    error_log('login.php: ' . $e->getMessage());
    $error = "Error al conectar a la base de datos. Inténtalo más tarde.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/login-estilos.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

  <!-- ENCABEZADO -->
  <header class="ctt-header">
    <div class="top-bar">
      <div class="logo">
        <img src="uploads/logo.png" alt="Logo CTT">
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

  <!-- CARRUSEL DE FONDO -->
  <div id="carruselFondo" class="carousel slide carousel-fade carousel-fondo" data-bs-ride="carousel"
    data-bs-interval="4000">
    <div class="carousel-inner">
      <?php foreach ($imagenes as $i => $img): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <img src="<?= htmlspecialchars($img) ?>" alt="Fondo <?= $i + 1 ?>">
        </div>
      <?php endforeach; ?>
    </div>

    <!-- FORMULARIO DE LOGIN CENTRADO -->
    <div class="login-wrapper">
      <main class="login-card">
        <div class="text-center mb-4">
          <h1>Iniciar Sesión</h1>
          <p class="text-muted">Accede con tu correo registrado</p>
        </div>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label for="correo">Correo:</label>
            <input type="email" name="correo" id="correo" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Ingresar</button>
          </div>

          <div class="form-register text-center">
            <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a>
          </div>
        </form>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>