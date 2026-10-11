<?php
require_once '../session.php';

requireLogin();
requirePost();

include_once '../sql/conexion.php';
require_once '../upload_seguro.php';

$cris = new Conexion();
$conn = $cris->conectar();

$titulo = $_POST['titulo'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$justificacion = $_POST['justificacion'] ?? '';
$contexto = $_POST['contexto'] ?? '';
// Los datos del solicitante salen de la sesión, no del formulario (evita suplantación)
$uid = (string) getUserId();
$uname = trim(getUserName() . ' ' . getUserLastname());
$uemail = getUserEmail() ?? '';
$urol = getUserRole() ?? '';

$imagenRuta = "";
if (isset($_FILES['captura']) && $_FILES['captura']['error'] !== UPLOAD_ERR_NO_FILE) {
    $directorioDestino = "../uploads/solicitudes/";
    try {
        $imagenRuta = $directorioDestino . guardarUploadSeguro($_FILES['captura'], __DIR__ . '/' . $directorioDestino, UPLOAD_IMAGENES);
    } catch (UploadException $e) {
        exit(e($e->getMessage()));
    }
}

try {
    $stmt = $conn->prepare("INSERT INTO solicitudes 
        (titulo, fecha, tipo, descripcion, justificacion, contexto, captura, uid, uname, uemail, urol) 
        VALUES (:titulo, :fecha, :tipo, :descripcion, :justificacion, :contexto, :captura, :uid, :uname, :uemail, :urol)");

    $stmt->execute([
        ':titulo' => $titulo,
        ':fecha' => $fecha,
        ':tipo' => $tipo,
        ':descripcion' => $descripcion,
        ':justificacion' => $justificacion,
        ':contexto' => $contexto,
        ':captura' => $imagenRuta,
        ':uid' => $uid,
        ':uname' => $uname,
        ':uemail' => $uemail,
        ':urol' => $urol
    ]);

    // Redirigir con éxito
    header("Location: solicitud_cambios.php?success=1");
    exit;
} catch (PDOException $e) {
    echo "❌ Error al guardar la solicitud: " . $e->getMessage();
}
