<?php
require_once 'sql/conexion.php';
require_once 'session.php'; // Asegúrate de que esta ruta sea correcta

$conexion = (new Conexion())->conectar();
$mensaje = '';
$correoValor = $_GET['correo'] ?? '';

// Lógica para obtener imágenes del carrusel, similar a login.php
// Asumo que 'info_fisei' y 'tipo = carrusel' existen y contienen URLs de imágenes.
// Si no quieres que 'verificar.php' dependa de la base de datos para el carrusel,
// puedes hardcodear las rutas de las imágenes aquí.
$stmt_carousel = $conexion->prepare("SELECT contenido FROM info_fisei WHERE tipo = 'carrusel'");
$stmt_carousel->execute();
$imagenes_carousel = [];
while ($row_carousel = $stmt_carousel->fetch(PDO::FETCH_ASSOC)) {
    $contenido = json_decode($row_carousel['contenido'], true);
    if (isset($contenido['img'])) {
        $imagenes_carousel[] = $contenido['img'];
    }
}
// Si $imagenes_carousel está vacío, puedes poner imágenes por defecto para evitar errores:
if (empty($imagenes_carousel)) {
    // Reemplaza con rutas a tus imágenes por defecto si no se cargan de la DB
    $imagenes_carousel = [
        'uploads/default_carousel_1.jpg', 
        'uploads/default_carousel_2.jpg'
    ];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $codigo = $_POST['codigo'];

    $stmt = $conexion->prepare("SELECT id, codigo_verificacion, verificado FROM estudiantes WHERE correo = :correo LIMIT 1");
    $stmt->bindValue(':correo', $correo);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if ($usuario['verificado']) {
            $mensaje = "La cuenta ya fue verificada.";
        } elseif ($usuario['codigo_verificacion'] === $codigo) {
            // Actualizar estado
            $update = $conexion->prepare("UPDATE estudiantes SET verificado = 1, codigo_verificacion = NULL WHERE id = :id");
            $update->bindValue(':id', $usuario['id']);
            $update->execute();

            $mensaje = "Cuenta verificada exitosamente. Ya puedes iniciar sesión.";
        } else {
            $mensaje = "Código incorrecto. Verifica el correo recibido.";
        }
    } else {
        $mensaje = "Correo no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código</title>
    <link rel="stylesheet" href="css/registro-estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Ajustes para el body para que el carrusel ocupe toda la altura debajo del header */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .ctt-header {
            /* Asegura que el header no interfiera con el posicionamiento absoluto del wrapper */
            position: relative; /* O cualquier valor que lo saque del flujo del carrusel */
            z-index: 1000;
        }

        /* SOBRESCRIBE EL POSICIONAMIENTO DEL FORMULARIO PARA QUE NO SE CORTE EN MÓVILES */
        /* registro-estilos.css tiene 'position: absolute' en .registro-wrapper.
           Para este caso, donde hay un header y el contenido del carrusel,
           podemos ajustar el top o dejar que flexbox lo centre si lo envolvemos.
           Dado que queremos el mismo efecto visual de `login.php`, mantenemos el absolute,
           pero nos aseguramos que el `.carousel-fondo` tenga la altura adecuada.
           El `min-height: 100vh` en `.carousel-fondo` de `registro-estilos.css` ya lo hace.
        */
        .registro-wrapper {
             /* Si el top: 50% y transform: translate(-50%, -50%) causan problemas en móvil
                después de añadir el header, considera usar flexbox para el centrado
                del main dentro del carousel-fondo y eliminar el position: absolute.
                Por ahora, replicamos lo de login.php.
             */
            position: absolute; /* Como está en registro-estilos.css */
            top: 50%; /* Como está en registro-estilos.css */
            left: 50%; /* Como está en registro-estilos.css */
            transform: translate(-50%, -50%); /* Como está en registro-estilos.css */
            z-index: 10; /* Para que esté sobre el carrusel */
            width: 100%; /* Para que ocupe todo el ancho disponible si se reduce la pantalla */
            max-width: 500px; /* Ancho máximo para este formulario específico */
            padding: 20px;
        }
        .card-custom h3.card-title {
            font-size: 32px; /* Usa el tamaño de h1 de card-custom para el h3 */
            font-weight: 800;
            color: var(--primary-color);
        }
        .link-secundario-verificar { /* Estilo para "Volver al inicio de sesión" */
            display: block;
            margin-top: 12px;
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.95rem;
            text-decoration: none;
            text-align: center;
        }
        .link-secundario-verificar:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <header class="ctt-header">
        <div class="top-bar">
            <div class="logo">
                <img src="uploads/logo.png" alt="Logo CTT"> </div>
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

    <div id="carruselFondo" class="carousel slide carousel-fade carousel-fondo" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-inner">
            <?php foreach ($imagenes_carousel as $i => $img): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($img) ?>" alt="Fondo <?= $i + 1 ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="registro-wrapper"> <main class="card-custom"> <h3 class="card-title text-center mb-4">Verificación de Cuenta</h3>
                <?php if (!empty($mensaje)): ?>
                    <div class="alert <?= strpos($mensaje, 'exitosamente') !== false ? 'alert-success' : 'alert-info' ?> text-center">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" required
                            value="<?= htmlspecialchars($correoValor) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código de Verificación</label>
                        <input type="text" class="form-control" name="codigo" required maxlength="10">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="boton-grande">Verificar</button>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <a href="login.php" class="link-secundario-verificar">Volver al inicio de sesión</a>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>