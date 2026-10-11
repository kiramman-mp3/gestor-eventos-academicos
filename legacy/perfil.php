<?php
require_once 'session.php';
include('sql/conexion.php');
require_once 'upload_seguro.php';

if (!isLoggedIn()) {
  header('Location: login.php');
  exit;
}

$nombre = getUserName();
$apellido = getUserLastname();

$usuarioId = getUserId();

$cris = new Conexion();
$conexion = $cris->conectar();
$stmt = $conexion->prepare("SELECT * FROM estudiantes WHERE id = ?");
$stmt->bindValue(1, $usuarioId, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  echo "Usuario no encontrado.";
  exit;
}

$catStmt = $conexion->query("SELECT id, nombre FROM categorias_evento ORDER BY nombre ASC");
$carrerasDisponibles = $catStmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $carrera = $usuario['tipo'] === 'institucional' && empty($usuario['carrera']) ? ($_POST['carrera'] ?? null) : $usuario['carrera'];
  // Carpeta por ID de usuario (no por correo) con nombres de archivo aleatorios
  $uploadDir = 'uploads/documentos/' . (int) $usuarioId . '/';

  $rutas = [
    'cedula_path' => $usuario['cedula_path'],
    'papeleta_path' => $usuario['papeleta_path'],
    'matricula_path' => $usuario['matricula_path']
  ];

  $erroresSubida = [];
  foreach ($rutas as $key => &$ruta) {
    $campo = str_replace('_path', '', $key);
    if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] !== UPLOAD_ERR_NO_FILE) {
      try {
        $nombre = guardarUploadSeguro($_FILES[$campo], __DIR__ . '/' . $uploadDir, UPLOAD_DOCUMENTOS);
        eliminarUploadSeguro($ruta ? __DIR__ . '/' . $ruta : null, __DIR__ . '/uploads/documentos');
        $ruta = $uploadDir . $nombre;
      } catch (UploadException $e) {
        $erroresSubida[] = ucfirst($campo) . ': ' . $e->getMessage();
      }
    }
  }
  unset($ruta);

  if ($erroresSubida) {
    $_SESSION['perfil_errores'] = $erroresSubida;
  }

  $update = $conexion->prepare("
    UPDATE estudiantes SET 
        carrera = ?, 
        cedula_path = ?, 
        papeleta_path = ?, 
        matricula_path = ?
    WHERE id = ?
  ");
  $update->bindValue(1, $carrera);
  $update->bindValue(2, $rutas['cedula_path']);
  $update->bindValue(3, $rutas['papeleta_path']);
  $update->bindValue(4, $rutas['matricula_path']);
  $update->bindValue(5, $usuarioId);
  $update->execute();

  header("Location: perfil.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi Perfil</title>
  <link rel="stylesheet" href="css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <!-- HEADER -->
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


  <main>
    <div class="card">
      <h1><i class="fas fa-user"></i> Mi Perfil</h1>

      <div style="line-height: 1.9;">
        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></p>
        <p><strong>Cédula:</strong> <?= htmlspecialchars($usuario['cedula']) ?></p>
        <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
        <p><strong>Tipo:</strong> <?= $usuario['tipo'] === 'institucional' ? 'Universitario UTA' : 'Público' ?></p>
        <p><strong>Género:</strong> <?= htmlspecialchars($usuario['genero']) ?></p>
        <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($usuario['fecha_nacimiento']) ?></p>
      </div>

      <?php foreach ($_SESSION['perfil_errores'] ?? [] as $errorSubida): ?>
        <p style="color: red;"><?= e($errorSubida) ?></p>
      <?php endforeach; unset($_SESSION['perfil_errores']); ?>

      <form method="POST" enctype="multipart/form-data" style="margin-top: 2rem;">
        <p>
          <strong><i class="fas fa-graduation-cap"></i> Carrera:</strong>
          <?php
          $nombreCarrera = 'No asignada';
          foreach ($carrerasDisponibles as $c) {
            if ($c['id'] == $usuario['carrera']) {
              $nombreCarrera = $c['nombre'];
              break;
            }
          }
          ?>
          <?= htmlspecialchars($nombreCarrera) ?>
        </p>

        <fieldset style="margin-top: 2rem;">
          <legend><i class="fas fa-folder-open"></i> Documentación</legend>

          <?php
          $docs = [
            'cedula' => 'Cédula escaneada',
            'papeleta' => 'Papeleta de votación',
            'matricula' => 'Matrícula'
          ];
          foreach ($docs as $campo => $etiqueta):
            $path = $usuario[$campo . '_path'];
            $previewId = "preview-$campo";
            ?>
            <div class="mb-3">
              <label for="<?= $campo ?>"><i class="fas fa-upload"></i><?= $etiqueta ?>:</label>
              <input type="file" name="<?= $campo ?>" id="<?= $campo ?>" accept="application/pdf,image/*"
                onchange="previewPDF(this, '<?= $previewId ?>')">
              <?php if (!empty($path)): ?>
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $path)): ?>
                  <img src="<?= e($path) ?>" class="thumbnail">
                <?php elseif (preg_match('/\.pdf$/i', $path)): ?>
                  <embed src="<?= e($path) ?>" type="application/pdf" width="50%" height="400px" class="thumbnail">
                <?php else: ?>
                  <p style="color: red;">Archivo no soportado.</p>
                <?php endif; ?>
              <?php endif; ?>
              <div id="<?= $previewId ?>"></div>
            </div>
          <?php endforeach; ?>
        </fieldset>

        <div class="form-submit align-right">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar cambios
          </button>
        </div>

      </form>
    </div>
  </main>

  <script>
    function previewPDF(input, previewId) {
      const file = input.files[0];
      const preview = document.getElementById(previewId);
      preview.innerHTML = '';
      if (!file) return;

      if (file.type.includes('image')) {
        const reader = new FileReader();
        reader.onload = () => {
          preview.innerHTML = `<img src="${reader.result}" class="thumbnail">`;
        };
        reader.readAsDataURL(file);
      } else if (file.type === 'application/pdf') {
        const url = URL.createObjectURL(file);
        preview.innerHTML = `<embed src="${url}" type="application/pdf" width="50%" height="400px" class="thumbnail">`;
      } else {
        preview.innerHTML = `<p style="color: red; margin-top: 8px;">Formato no soportado para vista previa.</p>`;
      }
    }
  </script>
</body>

</html>