<?php
require_once 'enviar_codigo_verificacion.php';
require_once 'sql/conexion.php';
require_once 'session.php';

$cris = new Conexion();
$conexion = $cris->conectar();

// Obtener imágenes del carrusel desde la BD
$stmt = $conexion->prepare("SELECT contenido FROM info_fisei WHERE tipo = 'carrusel'");
$stmt->execute();
$imagenes = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $contenido = json_decode($row['contenido'], true);
  if (isset($contenido['img'])) {
    $imagenes[] = $contenido['img'];
  }
}

function validarCedulaEcuatoriana($cedula)
{
  if (!preg_match("/^\d{10}$/", $cedula))
    return false;
  $digitos = str_split($cedula);
  $provincia = intval(substr($cedula, 0, 2));
  $tercerDigito = intval($cedula[2]);
  if ($provincia < 1 || $provincia > 24 || $tercerDigito >= 6)
    return false;

  $suma = 0;
  for ($i = 0; $i < 9; $i++) {
    $val = intval($digitos[$i]);
    if ($i % 2 === 0) {
      $val *= 2;
      if ($val > 9)
        $val -= 9;
    }
    $suma += $val;
  }
  $verificador = (10 - ($suma % 10)) % 10;
  return $verificador == intval($digitos[9]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $correo = $_POST['correo'];
  $cedula = $_POST['cedula'];
  $telefono = $_POST['telefono'] ?? null;
  $confirmar = $_POST['confirmar_password'];
  $carrera = $_POST['carrera'] ?? null;

  // Validar cédula
  if (!validarCedulaEcuatoriana($cedula)) {
    $error = "Número de cédula ecuatoriana inválido.";
  }

  // Validar correo
  elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $error = "Correo electrónico inválido.";
  }

  // Validar teléfono celular (solo números que empiecen con 09 y tengan 10 dígitos)
  elseif (!preg_match("/^09\d{8}$/", $telefono)) {
    $error = "Número de celular inválido. Debe comenzar con 09 y tener 10 dígitos.";
  }

  // Validar edad (mayor a 17 años)
  else {
    $fecha_nacimiento = $_POST['anio'] . '-' . $_POST['mes'] . '-' . $_POST['dia'];
    $fecha_actual = new DateTime();
    $fecha_nac = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    $edad = $fecha_actual->diff($fecha_nac)->y;

    if ($edad < 17) {
      $error = "Debes ser mayor de 17 años para registrarte.";
    }
  }

  // Validación de contraseña
  $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d\s\'\"%;\\\\])(?=.*\.).{8,}$/';
  if (!isset($error) && !preg_match($regex, $_POST['password'])) {
    $error = "La contraseña no cumple los requisitos: mínimo 8 caracteres, una mayúscula, una minúscula, un número, un carácter especial válido y un punto (.).";
  } elseif (!isset($error) && $_POST['password'] !== $confirmar) {
    $error = "Las contraseñas no coinciden.";
  } else {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  }

  // Tipo de correo y carrera
  if (!isset($error)) {
    if (str_ends_with($correo, '@uta.edu.ec')) {
      $tipo = 'institucional';
      if (empty($carrera)) {
        $error = "Debe seleccionar una carrera si usa correo institucional.";
      }
    } elseif (preg_match('/@.+\..+/', $correo)) {
      $tipo = 'publico';
      $carrera = 0; // No aplica
    } else {
      $error = "Correo inválido.";
    }
  }

  // Insertar en base si no hay error
  if (!isset($error)) {
    try {
      $stmt = $conexion->prepare("INSERT INTO estudiantes 
        (nombre, apellido, correo, password, cedula, genero, fecha_nacimiento, tipo, rol, telefono, carrera)
        VALUES (:nombre, :apellido, :correo, :password, :cedula, :genero, :fecha_nacimiento, :tipo, 'estudiante', :telefono, :carrera)");

      $stmt->bindValue(':nombre', $nombre);
      $stmt->bindValue(':apellido', $apellido);
      $stmt->bindValue(':correo', $correo);
      $stmt->bindValue(':password', $password);
      $stmt->bindValue(':cedula', $cedula);
      $stmt->bindValue(':genero', $_POST['genero']);
      $stmt->bindValue(':fecha_nacimiento', $fecha_nacimiento);
      $stmt->bindValue(':tipo', $tipo);
      $stmt->bindValue(':telefono', $telefono);
      $stmt->bindValue(':carrera', $carrera);

      if ($stmt->execute()) {
        // Generar y guardar el código
        $codigo = generarCodigoVerificacion();
        $stmtCodigo = $conexion->prepare("UPDATE estudiantes SET codigo_verificacion = :codigo WHERE correo = :correo");
        $stmtCodigo->bindValue(':codigo', $codigo);
        $stmtCodigo->bindValue(':correo', $correo);
        $stmtCodigo->execute();

        // Enviar correo
        if (!enviarCodigoCorreo($correo, $codigo)) {
          $error = "Usuario registrado pero error al enviar el correo de verificación.";
        }

        // No se inicia sesión hasta verificar el correo
        if (!isset($error)) {
          header("Location: verificar.php?correo=" . urlencode($correo));
          exit;
        }
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
  <title>Registro de Estudiante</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/registro-estilos.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

    <!-- FORMULARIO CENTRADO -->
    <div class="registro-wrapper">
      <main class="card-custom registro-card">
        <h1 class="text-center">Crea una cuenta</h1>
        <p class="text-center text-muted mb-4">Es rápido y fácil.</p>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="row mb-3">
            <div class="col">
              <input type="text" name="nombre" placeholder="Nombre" required>
            </div>
            <div class="col">
              <input type="text" name="apellido" placeholder="Apellido" required>
            </div>
          </div>

          <div class="mb-3">
            <input type="text" name="cedula" placeholder="Número de cédula" pattern="\d{10}"
              title="Debe contener 10 dígitos numéricos" required>
          </div>

          <!-- Campo nuevo: Teléfono celular -->
          <div class="mb-3">
            <input type="text" name="telefono" placeholder="Número de celular" pattern="09[0-9]{8}"
              title="El número debe comenzar con 09 y tener 10 dígitos en total" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Fecha de nacimiento:</label>
            <div class="row">
              <div class="col">
                <select name="dia" required>
                  <?php for ($d = 1; $d <= 31; $d++): ?>
                    <option value="<?= $d ?>"><?= $d ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col">
                <select name="mes" required>
                  <?php
                  $meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
                  foreach ($meses as $num => $mes): ?>
                    <option value="<?= $num ?>"><?= $mes ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col">
                <select name="anio" required>
                  <?php for ($y = date('Y') - 10; $y >= 1960; $y--): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Género:</label><br>
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
            <input type="email" name="correo" placeholder="Correo electrónico" required>
          </div>

          <!-- Campo carrera, solo visible si el correo es institucional -->
          <div class="mb-3 d-none" id="carrera-container">
            <label for="carrera" class="form-label">Carrera:</label>
            <select name="carrera" id="carrera" class="form-select">
              <option value="">Seleccione una carrera</option>
              <?php
              $cat = $conexion->query("SELECT id, nombre FROM categorias_evento ORDER BY nombre ASC");
              while ($fila = $cat->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value=\"{$fila['id']}\">{$fila['nombre']}</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3 position-relative">
            <input type="password" name="password" id="password" placeholder="Contraseña nueva" required
              class="form-control">
            <div id="password-box" class="password-popup d-none">
              <p class="mb-2 fw-bold">Debe contener:</p>
              <ul class="list-unstyled small">
                <li id="length" class="invalid">❌ Al menos 8 caracteres</li>
                <li id="uppercase" class="invalid">❌ Una letra mayúscula</li>
                <li id="lowercase" class="invalid">❌ Una letra minúscula</li>
                <li id="number" class="invalid">❌ Un número</li>
                <li id="special" class="invalid">❌ Un símbolo válido (*!@#...)</li>
                <li id="dot" class="invalid">❌ Contiene un punto (.)</li>
              </ul>
            </div>
          </div>

          <div class="mb-3">
            <input type="password" name="confirmar_password" id="confirmar_password" placeholder="Confirmar contraseña"
              required class="form-control">
          </div>

          <div id="match-error" class="alert alert-danger d-none">Las contraseñas no coinciden.</div>

          <div class="text-center">
            <button type="submit" class="boton-grande">Crear cuenta</button>
          </div>
        </form>


        <div class="text-center mt-3">
          <a href="login.php" class="link-secundario">¿Ya tienes cuenta? Inicia sesión aquí</a>
        </div>
      </main>
    </div>
  </div>
  <script>

    function ajustarAlturaCarrusel() {
      const wrapper = document.querySelector('.registro-wrapper');
      const carrusel = document.querySelector('.carousel-fondo');
      if (wrapper && carrusel) {
        const altoForm = wrapper.offsetHeight;
        const altoHeader = document.querySelector('.ctt-header')?.offsetHeight || 0;
        carrusel.style.height = (altoForm + altoHeader + 60) + 'px';
        document.querySelectorAll('.carousel-item, .carousel-item img, .carousel-inner').forEach(el => {
          el.style.height = carrusel.style.height;
        });
      }
    }

    window.addEventListener('load', ajustarAlturaCarrusel);
    window.addEventListener('resize', ajustarAlturaCarrusel);

    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmar_password');
    const passwordBox = document.getElementById('password-box');
    const matchError = document.getElementById('match-error');

    const requirements = {
      length: /.{8,}/,
      uppercase: /[A-Z]/,
      lowercase: /[a-z]/,
      number: /\d/,
      special: /[^a-zA-Z0-9\s'"%;\\]/,
      dot: /\./,
    };

    passwordInput.addEventListener('input', () => {
      if (passwordInput.value.length > 0) {
        passwordBox.classList.remove('d-none');
      } else {
        passwordBox.classList.add('d-none');
      }

      for (const [id, regex] of Object.entries(requirements)) {
        const el = document.getElementById(id);
        if (regex.test(passwordInput.value)) {
          el.textContent = '✔️ ' + el.textContent.slice(2);
          el.classList.add('valid');
          el.classList.remove('invalid');
        } else {
          el.textContent = '❌ ' + el.textContent.slice(2);
          el.classList.add('invalid');
          el.classList.remove('valid');
        }
      }
    });

    passwordInput.addEventListener('blur', () => {
      setTimeout(() => passwordBox.classList.add('d-none'), 150); // Evita que desaparezca al clickear
    });

    document.querySelector('form').addEventListener('submit', function (e) {
      const pass = passwordInput.value;
      const confirm = confirmInput.value;
      const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d\s'"%;\\])(?=.*\.).{8,}$/;

      let valid = true;
      for (const key in requirements) {
        if (!requirements[key].test(pass)) valid = false;
      }

      if (!valid) {
        alert("La contraseña no cumple todos los requisitos.");
        e.preventDefault();
        return;
      }

      if (pass !== confirm) {
        matchError.classList.remove('d-none');
        e.preventDefault();
      } else {
        matchError.classList.add('d-none');
      }
    });

    const correoInput = document.querySelector('input[name="correo"]');
    const carreraContainer = document.getElementById('carrera-container');

    correoInput.addEventListener('input', () => {
      const value = correoInput.value.trim();
      if (value.endsWith('@uta.edu.ec')) {
        carreraContainer.classList.remove('d-none');
      } else {
        carreraContainer.classList.add('d-none');
      }
    });
  </script>
</body>

</html>