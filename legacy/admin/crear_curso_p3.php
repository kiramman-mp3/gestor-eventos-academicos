<?php
require_once '../session.php';
require_once '../sql/conexion.php';
require_once '../upload_seguro.php';

if (!isLoggedIn() || getUserRole() !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_SESSION))
    session_start();
$nombre = getUserName();
$apellido = getUserLastname();

$errores = [];
$descripcion = '';
$ruta_imagen = $_SESSION['nuevo_curso']['ruta_imagen'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (strlen($descripcion) < 10) {
        $errores[] = "La descripción debe tener al menos 10 caracteres.";
    }

    // Procesar imagen si se cargó
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $ruta_imagen = "../uploads/" . guardarUploadSeguro($_FILES['imagen'], __DIR__ . '/../uploads', UPLOAD_IMAGENES);
        } catch (UploadException $e) {
            $errores[] = $e->getMessage();
        }
    }

    if (empty($errores)) {
        $_SESSION['nuevo_curso']['descripcion'] = $descripcion;
        if ($ruta_imagen) {
            $_SESSION['nuevo_curso']['ruta_imagen'] = $ruta_imagen;
        }
        header('Location: crear_curso_p4.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Curso - Paso 3</title>
    <link rel="stylesheet" href="../css/gestion-css.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous">
    <style>
        /* Contenedor principal para centrar y dar padding, reemplaza .container */
        .main-content-container {
            max-width: 900px;
            /* Ajusta el ancho según lo desees */
            margin: 0 auto;
            padding: 20px;
        }

        /* Estilos para el mensaje de error de PHP */
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert-danger li {
            list-style-type: disc;
        }

        /* Para el h2 que no está dentro de admin-section */
        .main-content-container>h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 1.8rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            display: inline-block;
        }

        /* Estilos para las acciones del formulario (botones volver/siguiente) */
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .form-actions .back-button {
            background-color: #6c757d;
            /* Color secundario para el botón de volver */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .form-actions .back-button:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        /* Estilo para el botón principal/siguiente */
        .form-actions .primary-button {
            background-color: var(--primary-color);
            /* Color primario para el botón de siguiente */
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .form-actions .primary-button:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-1px);
        }

        /* Estilos para el campo de texto (textarea) */
        .admin-form textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--input-border-color);
            border-radius: 5px;
            font-size: 1rem;
            color: var(--text-color);
            background-color: #fff;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .075);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            resize: vertical;
            /* Permite redimensionar verticalmente */
        }

        .admin-form textarea:focus {
            border-color: var(--input-focus-border-color);
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(176, 42, 55, .25);
        }

        /* Estilo para la imagen de previsualización */
        .admin-form .image-preview-container {
            margin-top: 15px;
            padding: 10px;
            border: 1px dashed #ced4da;
            border-radius: 5px;
            background-color: #f8f9fa;
            text-align: center;
        }

        .admin-form .image-preview-container p {
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-color);
        }

        .admin-form .image-preview-container img {
            max-width: 100%;
            /* Asegura que la imagen no desborde */
            height: auto;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: block;
            /* Para que margin auto funcione si quieres centrarla */
            margin: 0 auto;
        }
    </style>
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

    <div class="main-content-container">
        <h2>Crear Curso - Paso 3: Imagen y Descripción</h2>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <form class="admin-form" method="POST" enctype="multipart/form-data">
                <div class="admin-form-fields">
                    <div>
                        <label for="descripcion"><span class="rojo">*</span> Descripción del evento:</label>
                        <textarea name="descripcion" id="descripcion" rows="5"
                            placeholder="Escribe una descripción detallada del evento..."
                            required><?= htmlspecialchars($descripcion) ?></textarea>
                    </div>

                    <div>
                        <label for="imagen">Imagen del evento (opcional):</label>
                        <input type="file" name="imagen" id="imagen">
                        <?php if ($ruta_imagen): ?>
                            <div class="image-preview-container">
                                <p>Imagen actual:</p>
                                <img src="<?= $ruta_imagen ?>" alt="Previa">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="crear_curso_p2.php" class="back-button">&laquo; Anterior</a>
                    <button type="submit" class="primary-button">Siguiente &raquo;</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>