<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdminAuth();

// Try to include tFPDF, fallback to looking for FPDF or die if missing
$tfpdf_path = '../libs/tfpdf/tfpdf.php';
if (file_exists($tfpdf_path)) {
    require_once($tfpdf_path);
} else {
    // If user hasn't put libraries, we can't generate PDF.
    // Create a dummy PDF saying "Library Missing" or use a simple class if possible.
    die("PDF Kütüphanesi (libs/tfpdf/tfpdf.php) bulunamadı. Lütfen kurulumu yapınız.");
}

class PDF extends tFPDF
{
    function Header()
    {
        // Arial bold 15
        $this->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
        $this->SetFont('DejaVu','',14);
        // Title
        $this->Cell(0,10, 'Öğrenci Başvuru Listesi',0,1,'C');
        $this->SetFont('DejaVu','',10);
        $this->Cell(0,10, 'Tarih: '.date('d.m.Y H:i'),0,1,'C');
        $this->Ln(5);
        
        // Headers
        $this->SetFont('DejaVu','',8);
        $this->SetFillColor(200,220,255);
        $this->Cell(15,7, 'ID', 1, 0, 'C', true);
        $this->Cell(35,7, 'TC Kimlik', 1, 0, 'C', true);
        $this->Cell(45,7, 'Adı', 1, 0, 'C', true);
        $this->Cell(45,7, 'Soyadı', 1, 0, 'C', true);
        $this->Cell(50,7, 'İmza', 1, 1, 'C', true);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('DejaVu','',8);
        $this->Cell(0,10,'Sayfa '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
$pdf->SetFont('DejaVu','',8);

// Data
// Find Name and Surname Fields Dynamically
$name_field_id = 0;
$surname_field_id = 0;

$fields_stmt = $pdo->query("SELECT * FROM form_fields");
$all_fields = $fields_stmt->fetchAll();

foreach ($all_fields as $f) {
    $lbl = mb_strtolower($f['label'], 'UTF-8');
    
    // Ignore Parent info
    if (strpos($lbl, 'veli') !== false) continue;

    // Simple heuristic to find Name and Surname fields
    if ($name_field_id == 0 && (strpos($lbl, 'ad') !== false && strpos($lbl, 'soyad') === false)) { 
        $name_field_id = $f['id']; 
    }
    if ($surname_field_id == 0 && strpos($lbl, 'soyad') !== false) {
        $surname_field_id = $f['id'];
    }
}

// Fallback if not found (though unlikely if setup is correct)
if ($name_field_id == 0) $name_field_id = 1;
if ($surname_field_id == 0) $surname_field_id = 2;

$students_stmt = $pdo->query("SELECT * FROM students ORDER BY unique_id ASC"); // Sort by TC or Name preferred
$students = $students_stmt->fetchAll();

// Cache answers needing Name and Surname
$answers_cache = [];
if ($name_field_id && $surname_field_id) {
    $stmt = $pdo->query("SELECT * FROM student_answers WHERE field_id IN ($name_field_id, $surname_field_id)");
    $all_answers = $stmt->fetchAll();
    foreach($all_answers as $a) {
        $answers_cache[$a['student_id']][$a['field_id']] = $a['answer'];
    }
}

foreach($students as $row) {
    $id = $row['id'];
    $tc = $row['unique_id'];
    $name = isset($answers_cache[$id][$name_field_id]) ? $answers_cache[$id][$name_field_id] : '';
    $surname = isset($answers_cache[$id][$surname_field_id]) ? $answers_cache[$id][$surname_field_id] : '';

    $pdf->Cell(15,7, $id, 1);
    $pdf->Cell(35,7, $tc, 1);
    $pdf->Cell(45,7, $name, 1);
    $pdf->Cell(45,7, $surname, 1);
    $pdf->Cell(50,7, '', 1); // Signature space
    $pdf->Ln();
}

$pdf->Output();
?>
