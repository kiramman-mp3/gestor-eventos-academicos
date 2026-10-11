<?php
require_once '../session.php';
include '../sql/conexion.php';

// Verificar que el usuario sea administrador
if (!isLoggedIn() || getUserRole() !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

$conn = (new Conexion())->conectar();

// Cargar contenido por tipo
$tipos = ['carrusel', 'nosotros', 'autoridad', 'resena', 'mision', 'vision'];
$datos = [];

foreach ($tipos as $tipo) {
  $stmt = $conn->prepare("SELECT id, contenido FROM info_fisei WHERE tipo = ?");
  $stmt->execute([$tipo]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (in_array($tipo, ['mision', 'vision'])) {
      $datos[$tipo] = [
        'id' => $row['id'],
        'texto' => $row['contenido']
      ];
    } else {
      $contenido = json_decode($row['contenido'], true);
      $contenido['id'] = $row['id'];
      $datos[$tipo][] = $contenido;
    }
  }
}

// Cargar imagen del logo
$logoPath = '../uploads/logo.png';
$currentLogo = file_exists($logoPath) ? $logoPath : '';

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="../css/panel-estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <div id="toast" class="toast"><i class="fas fa-check-circle"></i><span id="toast-text">Actualizado con éxito</span>
  </div>

  <header class="ctt-header">
    <div class="top-bar">
      <div class="logo"><img src="<?= $currentLogo ?>" alt="Logo FISEI"></div>
      <div class="top-links">
        <div class="link-box">
          <i class="fa-solid fa-arrow-left"></i>
          <div>
            <span class="title">Regresar</span><br>
            <a href="../admin/panel_admin.php">Panel de Administración</a>
          </div>
        </div>
        <div class="link-box">
          <i class="fa-solid fa-user"></i>
          <div>
            <span class="title"><?= htmlspecialchars(getUserName() . ' ' . getUserLastname()) ?></span><br>
            <a href="../logout.php">Cerrar Sesión</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="admin-panel">
    <h1 class="titulo-formulario"><i class="fas fa-globe"></i> Administración de Contenido Web</h1>
    <p class="text-muted mb-4">Gestiona el contenido que se muestra en la página principal del sitio web FISEI.</p>

    <ul class="pestanas">
      <li><button class="active" data-tab="carrusel"><i class="fas fa-images"></i> Carrusel</button></li>
      <li><button data-tab="nosotros"><i class="fas fa-users"></i> Nosotros</button></li>
      <li><button data-tab="autoridades"><i class="fas fa-user-tie"></i> Autoridades</button></li>
      <li><button data-tab="resenas"><i class="fas fa-comment-dots"></i> Reseñas</button></li>
      <li><button data-tab="mision-vision"><i class="fas fa-bullseye"></i> Misión y Visión</button></li>
      <li><button data-tab="logo-section"><i class="fas fa-image"></i> Logo</button></li>
    </ul>

    <div class="contenedor-secciones">

      <section id="carrusel" class="tab-content active">
        <div class="admin-section">
          <h2>Carrusel</h2>
          <?php foreach ($datos['carrusel'] ?? [] as $c): ?>
            <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <input type="hidden" name="tipo" value="carrusel">
              <input type="hidden" name="imagen_actual" value="<?= $c['img'] ?>">
              <div class="imagen-editable">
                <img src="<?= '../' . $c['img'] ?>" alt="">
                <label for="imgUpload<?= $c['id'] ?>" class="editar-icono"><i class="fas fa-pen"></i></label>
                <input type="file" name="nueva_img" id="imgUpload<?= $c['id'] ?>" accept="image/*">
              </div>
              <div class="admin-form-fields">
                <input type="text" name="titulo" value="<?= $c['titulo'] ?>" required>
                <textarea name="descripcion" required><?= $c['descripcion'] ?></textarea>
                <button type="submit">Actualizar</button>
                <button type="submit" name="delete" value="<?= (int) $c['id'] ?>" formnovalidate class="btn-eliminar"
                  onclick="if (!confirm('¿Eliminar este elemento?')) return false; localStorage.setItem('toastMsg','eliminado')">Eliminar</button>
              </div>
            </form>
          <?php endforeach; ?>

          <h3>Nuevo Carrusel</h3>
          <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="tipo" value="carrusel">
            <div class="imagen-editable">
              <img src="" style="display:none;">
              <label for="imgNuevoCarrusel" class="editar-icono"><i class="fas fa-plus"></i></label>
              <input type="file" name="nueva_img" id="imgNuevoCarrusel" accept="image/*" required>
            </div>
            <div class="admin-form-fields">
              <input type="text" name="titulo" placeholder="Título" required>
              <textarea name="descripcion" placeholder="Descripción" required></textarea>
              <button type="submit" onclick="localStorage.setItem('toastMsg','creado')">Agregar</button>
            </div>
          </form>
        </div>
      </section>

      <section id="nosotros" class="tab-content">
        <div class="admin-section">
          <h2>Nosotros</h2>
          <?php foreach ($datos['nosotros'] ?? [] as $n): ?>
            <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
              <input type="hidden" name="id" value="<?= $n['id'] ?>">
              <input type="hidden" name="tipo" value="nosotros">
              <input type="hidden" name="imagen_actual" value="<?= $n['img'] ?>">
              <div class="imagen-editable">
                <img src="<?= '../' . $n['img'] ?>" alt="">
                <label for="imgNosotros<?= $n['id'] ?>" class="editar-icono"><i class="fas fa-pen"></i></label>
                <input type="file" name="nueva_img" id="imgNosotros<?= $n['id'] ?>" accept="image/*">
              </div>
              <div class="admin-form-fields">
                <input type="text" name="titulo" value="<?= $n['titulo'] ?>" required>
                <textarea name="descripcion" required><?= $n['descripcion'] ?></textarea>
                <button type="submit">Actualizar</button>
              </div>
            </form>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="autoridades" class="tab-content">
        <div class="admin-section">
          <h2>Autoridades</h2>
          <?php foreach ($datos['autoridad'] ?? [] as $a): ?>
            <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <input type="hidden" name="tipo" value="autoridad">
              <input type="hidden" name="imagen_actual" value="<?= $a['img'] ?>">
              <div class="imagen-editable">
                <img src="<?= '../' . $a['img'] ?>" alt="">
                <label for="imgAutoridad<?= $a['id'] ?>" class="editar-icono"><i class="fas fa-pen"></i></label>
                <input type="file" name="nueva_img" id="imgAutoridad<?= $a['id'] ?>" accept="image/*">
              </div>
              <div class="admin-form-fields">
                <input type="text" name="nombre" value="<?= $a['nombre'] ?>" required>
                <input type="text" name="cargo" value="<?= $a['cargo'] ?>" required>
                <button type="submit">Actualizar</button>
                <button type="submit" name="delete" value="<?= (int) $a['id'] ?>" formnovalidate class="btn-eliminar"
                  onclick="if (!confirm('¿Eliminar este elemento?')) return false; localStorage.setItem('toastMsg','eliminado')">Eliminar</button>
              </div>
            </form>
          <?php endforeach; ?>
          
          <h3>Nueva Autoridad</h3>
          <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="tipo" value="autoridad">
            <div class="imagen-editable">
              <img src="" style="display:none;">
              <label for="imgNuevaAutoridad" class="editar-icono"><i class="fas fa-plus"></i></label>
              <input type="file" name="nueva_img" id="imgNuevaAutoridad" accept="image/*" required>
            </div>
            <div class="admin-form-fields">
              <input type="text" name="nombre" placeholder="Nombre completo" required>
              <input type="text" name="cargo" placeholder="Cargo" required>
              <button type="submit" onclick="localStorage.setItem('toastMsg','creado')">Agregar Autoridad</button>
            </div>
          </form>
        </div>
      </section>

      <section id="resenas" class="tab-content">
        <div class="admin-section">
          <h2>Reseñas</h2>
          <?php foreach ($datos['resena'] ?? [] as $r): ?>
            <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <input type="hidden" name="tipo" value="resena">
              <input type="hidden" name="imagen_actual" value="<?= $r['img'] ?>">
              <div class="imagen-editable">
                <img src="<?= '../' . $r['img'] ?>" alt="">
                <label for="imgResena<?= $r['id'] ?>" class="editar-icono"><i class="fas fa-pen"></i></label>
                <input type="file" name="nueva_img" id="imgResena<?= $r['id'] ?>" accept="image/*">
              </div>
              <div class="admin-form-fields">
                <input type="text" name="autor" value="<?= $r['autor'] ?>" required>
                <input type="text" name="rol" value="<?= $r['rol'] ?>" required>
                <textarea name="texto" required><?= $r['texto'] ?></textarea>
                <button type="submit">Actualizar</button>
                <button type="submit" name="delete" value="<?= (int) $r['id'] ?>" formnovalidate class="btn-eliminar"
                  onclick="if (!confirm('¿Eliminar este elemento?')) return false; localStorage.setItem('toastMsg','eliminado')">Eliminar</button>
              </div>
            </form>
          <?php endforeach; ?>
          
          <h3>Nueva Reseña</h3>
          <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="tipo" value="resena">
            <div class="imagen-editable">
              <img src="" style="display:none;">
              <label for="imgNuevaResena" class="editar-icono"><i class="fas fa-plus"></i></label>
              <input type="file" name="nueva_img" id="imgNuevaResena" accept="image/*" required>
            </div>
            <div class="admin-form-fields">
              <input type="text" name="autor" placeholder="Nombre del autor" required>
              <input type="text" name="rol" placeholder="Cargo o rol" required>
              <textarea name="texto" placeholder="Texto de la reseña" required></textarea>
              <button type="submit" onclick="localStorage.setItem('toastMsg','creado')">Agregar Reseña</button>
            </div>
          </form>
        </div>
      </section>

      <section id="mision-vision" class="tab-content">
        <div class="admin-section">
          <h2>Misión y Visión</h2>

          <form action="api_admin.php" method="post" class="admin-form">
            <input type="hidden" name="tipo" value="mision">
            <?php if (!empty($datos['mision']['id'])): ?>
              <input type="hidden" name="id" value="<?= $datos['mision']['id'] ?>">
            <?php endif; ?>
            <div class="admin-form-fields">
              <label>Misión</label>
              <textarea name="texto" required><?= htmlspecialchars($datos['mision']['texto'] ?? '') ?></textarea>
              <button type="submit">Guardar Misión</button>
            </div>
          </form>

          <form action="api_admin.php" method="post" class="admin-form">
            <input type="hidden" name="tipo" value="vision">
            <?php if (!empty($datos['vision']['id'])): ?>
              <input type="hidden" name="id" value="<?= $datos['vision']['id'] ?>">
            <?php endif; ?>
            <div class="admin-form-fields">
              <label>Visión</label>
              <textarea name="texto" required><?= htmlspecialchars($datos['vision']['texto'] ?? '') ?></textarea>
              <button type="submit">Guardar Visión</button>
            </div>
          </form>
        </div>
      </section>

      <section id="logo-section" class="tab-content">
        <div class="admin-section">
          <h2>Modificar Logo</h2>
          <form action="api_admin.php" method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="tipo" value="logo">
            <div class="imagen-editable">
              <?php if ($currentLogo): ?>
                <img src="<?= $currentLogo ?>" alt="Logo actual">
              <?php else: ?>
                <img src="" style="display:none;" alt="No hay logo">
              <?php endif; ?>
              <label for="imgLogoUpload" class="editar-icono"><i class="fas fa-pen"></i></label>
              <input type="file" name="nueva_img" id="imgLogoUpload" accept="image/png" required>
            </div>
            <p>Solo se aceptan imágenes en formato PNG. El archivo se guardará como "logo.png".</p>
            <div class="admin-form-fields">
              <button type="submit">Actualizar Logo</button>
            </div>
          </form>
        </div>
      </section>

    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tabs = document.querySelectorAll('.pestanas button');
      const contents = document.querySelectorAll('.tab-content');
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          contents.forEach(c => c.classList.remove('active'));
          tab.classList.add('active');
          document.getElementById(tab.dataset.tab).classList.add('active');
        });
      });

      const showToast = msg => {
        const toast = document.getElementById('toast');
        const toastText = document.getElementById('toast-text');
        toastText.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
      };

      document.querySelectorAll('input[type="file"][name="nueva_img"]').forEach(input => {
        input.addEventListener('change', e => {
          const f = e.target.files[0];
          // Validar tipo de archivo solo para el logo
          if (e.target.id === 'imgLogoUpload' && f && f.type !== 'image/png') {
              showToast('Error: Solo se permiten archivos PNG para el logo.');
              input.value = ''; // Limpiar el input
              const img = input.closest('.imagen-editable').querySelector('img');
              if (img) {
                  img.style.display = 'none'; // Ocultar si no hay imagen válida
              }
              return;
          }

          if (f && f.type.startsWith('image/')) {
            const img = input.closest('.imagen-editable').querySelector('img');
            img.src = URL.createObjectURL(f);
            img.style.display = 'block';
          }
        });
      });

      document.querySelectorAll('form.admin-form').forEach(f => {
        f.addEventListener('submit', () => localStorage.setItem('toastMsg', 'actualizado'));
      });

      const t = localStorage.getItem('toastMsg');
      if (t) {
        showToast(t === 'creado' ? 'Elemento creado' : t === 'eliminado' ? 'Eliminado con éxito' : 'Actualizado con éxito');
        localStorage.removeItem('toastMsg');
      }

      // Check for error message from logo upload
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('error') && urlParams.get('error') === 'not_png') {
          showToast('Error al subir el logo: Solo se permiten archivos PNG.');
      }
    });
  </script>
</body>

</html>