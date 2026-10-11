<?php
include '../session.php';

requireAdmin();
requirePost();
include '../sql/conexion.php';
require_once '../upload_seguro.php';
$conn = (new Conexion())->conectar();

$inscripcion_id = $_POST['inscripcion_id'];
$evento_id = $_POST['evento_id'];

$stmt = $conn->prepare("SELECT id FROM requisitos_evento WHERE evento_id = ?");
$stmt->execute([$evento_id]);
$requisitos = $stmt->fetchAll(PDO::FETCH_COLUMN);

$ruta_guardado = "../uploads/requisitos/";

// Procesar archivos subidos
foreach ($requisitos as $req_id) {
    $input_name = "requisito_" . $req_id;
    if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $destino = $ruta_guardado . guardarUploadSeguro($_FILES[$input_name], __DIR__ . '/' . $ruta_guardado, UPLOAD_DOCUMENTOS);
        } catch (UploadException $e) {
            continue;
        }

        $stmt2 = $conn->prepare("INSERT INTO requisitos_inscripcion (inscripcion_id, requisito_id, archivo) VALUES (?, ?, ?)");
        $stmt2->execute([$inscripcion_id, $req_id, $destino]);
    }
}

// Redirigir de vuelta al evento (sin redirect_url libre: evita open redirect)
header("Location: ../admin/administrar_evento.php?id=" . (int) $evento_id);
exit;
