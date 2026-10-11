<?php
require_once '../session.php';

$uid = getUserId() ?? '';
$uname = getUserName() . ' ' . getUserLastname() ?? '';
$uemail = getUserEmail() ?? '';
$urol = getUserRole() ?? '';

$sesion_activa = !empty($uid) && !empty($uname) && !empty($uemail) && !empty($urol);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solicitud de Cambios</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            <span class="title">Regresar</span><br>
            <a href="javascript:history.back()">Página anterior</a>
          </div>
        </div>
      </div>
    </div>
  </header>


  <main class="card">
    <form id="solicitudForm" action="guardar_solicitud.php" method="POST" enctype="multipart/form-data" novalidate>
      <h1 class="titulo-formulario" style="grid-column: 1 / -1;">Solicitud de cambios:</h1>
      <div class="main-grid">
        <!-- Columna izquierda -->
        <div>
          <div class="form-row">
            <label>
              <span class="rojo">Título:</span>
              <input type="text" id="titulo" name="titulo" required />
            </label>

            <label>
              <span class="rojo">Fecha:</span>
              <input type="text" id="fecha" name="fecha" readonly value="<?= date('Y-m-d') ?>" />
            </label>
          </div>

          <label>
            <span class="rojo">Tipo:</span>
            <select id="tipo" name="tipo" required>
              <option value="" disabled selected>Seleccione una opción</option>
              <option value="Corrección de Error">Corrección de Error</option>
              <option value="Interfaz">Interfaz</option>
              <option value="Funcional">Funcional</option>
              <option value="Otro">Otro</option>
            </select>
          </label>

          <label>
            <span class="rojo">Descripción:</span>
            <textarea id="descripcion" name="descripcion" rows="5" required
              placeholder="Describe el problema..."></textarea>
          </label>

          <label>
            <span class="rojo">Captura de pantalla:</span>
            <span style="color: var(--gray-600); font-style: italic;">(Opcional)</span>

            <input type="file" id="captureInput" name="captura" accept="image/*" style="display: none;" />
            <div class="capture-bar" id="captureBar" style="display: none;">
              <span class="file-name" id="fileName"></span>
              <button type="button" class="btn-capture tomar" id="takeAnotherBtn">Tomar otra</button>
              <button type="button" class="btn-capture eliminar" id="deleteCaptureBtn">Eliminar</button>
            </div>
            <button type="button" class="btn-capture tomar" id="addCaptureBtn">Subir imagen</button>
          </label>

          <label>
            <span class="rojo">¿Por qué debería solucionarse este problema?</span>
            <textarea id="justificacion" name="justificacion" rows="4"
              placeholder="Explica la importancia..."></textarea>
          </label>

          <label>
            <span class="rojo">¿Qué estaba haciendo cuando el problema surgió?</span>
            <textarea id="contexto" name="contexto" rows="4" placeholder="Describe el contexto..."></textarea>
          </label>

          <div class="form-submit align-right">
            <button type="reset" class="btn cancelar">Cancelar</button>
            <button type="submit" class="btn enviar">Enviar</button>
          </div>
        </div>

        <!-- Columna derecha -->
        <fieldset class="user-info" id="userFieldset">
          <legend>Información del usuario:</legend>
          <input id="uid" name="uid" type="text" placeholder="ID de usuario" required
            value="<?= htmlspecialchars($uid) ?>" <?= $sesion_activa ? 'readonly' : '' ?> />
          <input id="uname" name="uname" type="text" placeholder="Nombre completo" required
            value="<?= htmlspecialchars($uname) ?>" <?= $sesion_activa ? 'readonly' : '' ?> />
          <input id="uemail" name="uemail" type="email" placeholder="Correo" required
            value="<?= htmlspecialchars($uemail) ?>" <?= $sesion_activa ? 'readonly' : '' ?> />
          <input id="urol" name="urol" type="text" placeholder="Rol" required value="<?= htmlspecialchars($urol) ?>"
            <?= $sesion_activa ? 'readonly' : '' ?> />
        </fieldset>
      </div>
    </form>
  </main>


</body>

</html>