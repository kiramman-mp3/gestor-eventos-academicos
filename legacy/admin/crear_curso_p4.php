<?php
require_once '../session.php';
require_once '../sql/conexion.php';

if (!isLoggedIn() || getUserRole() !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['nuevo_curso'])) {
    header('Location: crear_curso_p1.php');
    exit;
}

$errores = [];
$requisito_tipo = $_POST['tipo'] ?? '';
$requisito_descripcion = trim($_POST['descripcion'] ?? '');
$requisito_campo = $_POST['campo'] ?? '';
$accion = $_POST['accion'] ?? null;

if (!isset($_SESSION['nuevo_curso']['requisitos'])) {
    $_SESSION['nuevo_curso']['requisitos'] = [];
}

// Agregar requisito
if ($accion === 'agregar') {
    if ($requisito_tipo === 'documento') {
        if (empty($requisito_campo)) {
            $errores[] = "Debe seleccionar un documento del perfil.";
        } else {
            $_SESSION['nuevo_curso']['requisitos'][] = [
                'tipo' => 'archivo',
                'descripcion' => ucfirst(str_replace('ruta_', 'Ruta_', $requisito_campo)),
                'campo_estudiante' => $requisito_campo
            ];
        }
    } elseif ($requisito_tipo === 'texto') {
        if (strlen($requisito_descripcion) < 5) {
            $errores[] = "El requisito de texto debe tener al menos 5 caracteres.";
        } else {
            $_SESSION['nuevo_curso']['requisitos'][] = [
                'tipo' => 'texto',
                'descripcion' => $requisito_descripcion,
                'campo_estudiante' => null
            ];
        }
    } else {
        $errores[] = "Debe seleccionar un tipo de requisito válido.";
    }
}

// Eliminar requisito
if ($accion === 'eliminar' && isset($_POST['indice'])) {
    $indice = (int) $_POST['indice'];
    if (isset($_SESSION['nuevo_curso']['requisitos'][$indice])) {
        array_splice($_SESSION['nuevo_curso']['requisitos'], $indice, 1);
    }
}

// Continuar
if ($accion === 'continuar') {
    header('Location: crear_curso_confirmar.php');
    exit;
}

$nombreUsuario = getUserName();
$apellidoUsuario = getUserLastname();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Curso - Paso 4</title>
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

        /* Estilos para el formulario de añadir requisito */
        .add-requirement-form {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            /* Responsivo para los campos */
            gap: 20px 30px;
            align-items: end;
            /* Alinea los elementos al final de su celda para que los botones queden abajo */
        }

        /* Estilos específicos para los elementos dentro del formulario de añadir requisito */
        .add-requirement-form>div {
            display: flex;
            flex-direction: column;
        }

        .add-requirement-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .add-requirement-form select,
        .add-requirement-form input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--input-border-color);
            border-radius: 5px;
            font-size: 1rem;
            color: var(--text-color);
            background-color: #fff;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .075);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .add-requirement-form select:focus,
        .add-requirement-form input[type="text"]:focus {
            border-color: var(--input-focus-border-color);
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(176, 42, 55, .25);
        }

        .add-requirement-form button[type="submit"] {
            background-color: #c82333;
            /* Verde para agregar */
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .add-requirement-form button[type="submit"]:hover {
            background-color: #8d1f2d;
            transform: translateY(-1px);
        }

        /* Estilos para la lista de requisitos actuales */
        .requirements-list {
            list-style: none;
            /* Eliminar los puntos de la lista */
            padding: 0;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            /* Para que los bordes internos queden bien */
        }

        .requirements-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
            background-color: #fff;
        }

        .requirements-list li:last-child {
            border-bottom: none;
        }

        .requirements-list li:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .03);
            /* Fondo para filas impares */
        }

        .requirements-list li p {
            margin: 0;
            color: var(--text-color);
            font-size: 1rem;
            flex-grow: 1;
            /* Permite que el texto ocupe espacio */
        }

        .requirements-list li form {
            margin: 0;
            line-height: 1;
            /* Eliminar espacio extra del formulario */
        }

        .requirements-list li button {
            background-color: var(--delete-button-color);
            /* Rojo para eliminar */
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .requirements-list li button:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* Estilos para el texto "No se han agregado requisitos" */
        .no-requirements-text {
            color: #6c757d;
            font-style: italic;
            margin-bottom: 20px;
        }

        /* Estilos para las acciones del formulario principal (botones volver/siguiente) */
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

        .form-actions .primary-button {
            background-color: var(--primary-color);
            /* Color primario para el botón de confirmar */
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
        <h2>Crear Curso - Paso 4: Requisitos</h2>

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
            <form method="POST" class="add-requirement-form">
                <div>
                    <label for="tipo">Tipo de requisito:</label>
                    <select name="tipo" id="tipo" required onchange="actualizarCampos()">
                        <option value="">Seleccionar</option>
                        <option value="documento" <?= $requisito_tipo === 'documento' ? 'selected' : '' ?>>Documento del
                            perfil</option>
                        <option value="texto" <?= $requisito_tipo === 'texto' ? 'selected' : '' ?>>Texto libre</option>
                    </select>
                </div>

                <div id="campoDocumento" style="display:<?= $requisito_tipo === 'documento' ? 'block' : 'none'; ?>;">
                    <label for="campo">Documento requerido:</label>
                    <select name="campo" id="campo">
                        <option value="">Seleccione</option>
                        <option value="cedula_path" <?= $requisito_campo === 'cedula_path' ? 'selected' : '' ?>>Cédula
                        </option>
                        <option value="matricula_path" <?= $requisito_campo === 'matricula_path' ? 'selected' : '' ?>>
                            Matrícula</option>
                        <option value="papeleta_path" <?= $requisito_campo === 'papeleta_path' ? 'selected' : '' ?>>
                            Papeleta de votación</option>
                    </select>
                </div>

                <div id="campoTexto" style="display:<?= $requisito_tipo === 'texto' ? 'block' : 'none'; ?>;">
                    <label for="descripcion-requisito">Descripción del requisito:</label>
                    <input type="text" name="descripcion" id="descripcion-requisito"
                        placeholder="Ej. Carta de motivación" value="<?= htmlspecialchars($requisito_descripcion) ?>">
                </div>

                <div style="grid-column: span 1; /* Ocupa una columna */">
                    <input type="hidden" name="accion" value="agregar">
                    <button type="submit">Agregar</button>
                </div>
            </form>

            <h5>Requisitos actuales:</h5>
            <?php if (empty($_SESSION['nuevo_curso']['requisitos'])): ?>
                <p class="no-requirements-text">No se han agregado requisitos aún.</p>
            <?php else: ?>
                <ul class="requirements-list">
                    <?php foreach ($_SESSION['nuevo_curso']['requisitos'] as $i => $req): ?>
                        <li>
                            <p>
                                <?= htmlspecialchars($req['descripcion']) ?>
                                <small style="color: gray;">
                                    (<?= $req['tipo'] === 'archivo' ? 'Documento: ' . htmlspecialchars($req['campo_estudiante']) : 'Texto libre' ?>)
                                </small>
                            </p>
                            <form method="POST">
                                <input type="hidden" name="indice" value="<?= $i ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <button>Eliminar</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>

            <div class="form-actions">
                <a href="crear_curso_p3.php" class="back-button">&laquo; Volver</a>
                <form method="POST">
                    <input type="hidden" name="accion" value="continuar">
                    <button type="submit" class="primary-button">Confirmar &raquo;</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function actualizarCampos() {
            const tipo = document.getElementById('tipo').value;
            const campoDocumentoDiv = document.getElementById('campoDocumento');
            const campoTextoDiv = document.getElementById('campoTexto');
            const selectCampo = document.getElementById('campo');
            const inputDescripcion = document.getElementById('descripcion-requisito');

            if (tipo === 'documento') {
                campoDocumentoDiv.style.display = 'block';
                campoTextoDiv.style.display = 'none';
                inputDescripcion.value = ''; // Limpiar campo de texto si se cambia a documento
                selectCampo.setAttribute('required', 'required');
                inputDescripcion.removeAttribute('required');
            } else if (tipo === 'texto') {
                campoDocumentoDiv.style.display = 'none';
                campoTextoDiv.style.display = 'block';
                selectCampo.value = ''; // Limpiar selección de documento si se cambia a texto
                selectCampo.removeAttribute('required');
                inputDescripcion.setAttribute('required', 'required');
            } else {
                campoDocumentoDiv.style.display = 'none';
                campoTextoDiv.style.display = 'none';
                selectCampo.value = '';
                inputDescripcion.value = '';
                selectCampo.removeAttribute('required');
                inputDescripcion.removeAttribute('required');
            }
        }

        // Ejecutar al cargar la página para reflejar el estado inicial si hay datos de sesión
        document.addEventListener('DOMContentLoaded', actualizarCampos);
    </script>
</body>

</html>