<?php
if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

require_once("../libs/fpdf/fpdf.php");
require_once '../session.php';

// Simulación de sesión y GET
$_SESSION['usuario_id'] = 1;
$_GET['id'] = 101;

$usuario_id = $_SESSION['usuario_id'];
$inscripcion_id = $_GET['id'];

// Simulación de datos
$datos = [
    'nombre_completo' => 'María Fernanda López',
    'cedula' => '0102030405',
    'nombre_evento' => 'Curso de Programación Web con PHP y MySQL',
    'modalidad' => 'PRESENCIAL',
    'horas' => 40,
    'ciudad' => 'Ambato',
    'fecha_inicio' => '2025-05-05',
    'fecha_fin' => '2025-05-20',
    'nota' => 9.5,
    'asistencia' => 90,
    'fecha_certificado' => 'Ambato, 28 de mayo de 2025',
    'codigo_certificado' => 'CERT-2025-0001'
];

// Generar certificado PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

$pdf->Image('../resource/logo-uta1.png', 10, 8, 28);
$pdf->Image('../resource/logo-uta.png', 260, 10, 20);

$pdf->SetY(15);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 10, utf8_decode("Certificado OC No. MDT-OC"), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode("Ministerio del Trabajo"), 0, 1, 'C');

$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, utf8_decode("CERTIFICADO DE CAPACITACIÓN"), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, utf8_decode("{$datos['nombre_completo']} - {$datos['cedula']}"), 0, 1, 'C');

$pdf->SetDrawColor(173, 0, 0);
$pdf->SetLineWidth(0.4);
$pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
$pdf->Ln(7);

$pdf->SetFont('Arial', '', 11);
$texto = "Por haber aprobado el curso: {$datos['nombre_evento']}    Modalidad: {$datos['modalidad']} de {$datos['horas']} horas de duración,
realizado en la ciudad de {$datos['ciudad']} del {$datos['fecha_inicio']} al {$datos['fecha_fin']} bajo el enfoque de CAPACITACIÓN CONTINUA.";
$pdf->MultiCell(0, 8, utf8_decode($texto), 0, 'L');
$pdf->Ln(12);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140, 10, utf8_decode('Firma'), 0, 0, 'C');
$pdf->Cell(0, 10, utf8_decode('Firma'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(140, 5, utf8_decode('Representante Legal del OC'), 0, 0, 'C');
$pdf->Cell(0, 5, utf8_decode('Coordinador Pedagógico'), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFillColor(27, 42, 83);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 10, utf8_decode("Código de Calificación: {$datos['codigo_certificado']}"), 0, 1, 'L', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, utf8_decode($datos['fecha_certificado']), 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 5, utf8_decode("El presente certificado es otorgado por un Operador de Capacitación calificado."), 0, 'L');

$pdf->Output();
