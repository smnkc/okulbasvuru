<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) {
    die("Geçersiz istek.");
}

// Fetch Student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    die("Öğrenci bulunamadı.");
}

// Fetch Answers
$stmt2 = $pdo->prepare("SELECT a.answer, f.label, f.order_num, f.show_in_pdf FROM student_answers a JOIN form_fields f ON a.field_id = f.id WHERE a.student_id = ? ORDER BY f.order_num ASC");
$stmt2->execute([$id]);
$answers = $stmt2->fetchAll();

// Fetch Settings
$stmt3 = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt3->fetch();

// Try to include tFPDF, fallback to looking for FPDF or die if missing
$tfpdf_path = 'libs/tfpdf/tfpdf.php';
// Provide a helpful error if library is missing
if (!file_exists($tfpdf_path)) {
    die("PDF oluşturulamadı: tFPDF kütüphanesi 'libs/tfpdf/tfpdf.php' yolunda bulunamadı. Lütfen kütüphaneyi yükleyiniz.");
}
require_once($tfpdf_path);

class PDF extends tFPDF
{
    function Header()
    {
        // No global header here, we will custom build the page content
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
        $this->SetFont('DejaVu','',8);
        $this->Cell(0,10,'Bu belge bilgisayar ortamında oluşturulmuştur.',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf',true);

// 1. HEADER SECTION with Logo and School Name
$pdf->SetFont('DejaVu','B',16);
if ($settings['logo_path'] && file_exists($settings['logo_path'])) {
    $pdf->Image($settings['logo_path'], 10, 10, 30);
    $pdf->SetX(45);
    $pdf->MultiCell(0, 10, $settings['school_name'], 0, 'C');
    $pdf->SetX(45);
    $pdf->SetFont('DejaVu','B',12);
    $pdf->Cell(0, 10, 'ÖĞRENCİ KABUL SINAVI GİRİŞ BELGESİ', 0, 1, 'C');
} else {
    $pdf->MultiCell(0, 10, $settings['school_name'], 0, 'C');
    $pdf->SetFont('DejaVu','B',12);
    $pdf->Cell(0, 10, 'ÖĞRENCİ KABUL SINAVI GİRİŞ BELGESİ', 0, 1, 'C');
}

$pdf->Ln(20);

// 2. EXAM INFO BOX
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(0, 10, 'SINAV BİLGİLERİ', 1, 1, 'C', true);

$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(50, 10, 'Sınav Tarihi ve Saati:', 1, 0, 'L');
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(0, 10, $settings['exam_date_text'], 1, 1, 'L');

$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(50, 10, 'Sınav Yeri:', 1, 0, 'L');
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(0, 10, 'Okul girişinde asılan listelerden öğrenilecektir.', 1, 1, 'L');

$pdf->Ln(10);

// 3. STUDENT INFO BOX
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(0, 10, 'ÖĞRENCİ BİLGİLERİ', 1, 1, 'C', true);

$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(50, 10, 'TC Kimlik No:', 1, 0, 'L');
$pdf->SetFont('DejaVu','',10);
$pdf->Cell(0, 10, $student['unique_id'], 1, 1, 'L');

foreach ($answers as $ans) {
    // Check if allowed to show in PDF
    if (!$ans['show_in_pdf']) continue;
    
    $pdf->SetFont('DejaVu','B',10);
    $pdf->Cell(50, 10, $ans['label'] . ':', 1, 0, 'L');
    $pdf->SetFont('DejaVu','',10);
    // Handle long text
    if (strlen($ans['answer']) > 50) {
        $pdf->MultiCell(0, 10, $ans['answer'], 1, 'L');
    } else {
        $pdf->Cell(0, 10, $ans['answer'], 1, 1, 'L');
    }
}

$pdf->Ln(10);

// 4. RULES
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(0, 10, 'SINAV KURALLARI', 0, 1, 'L');
$pdf->SetFont('DejaVu','',9);
$pdf->MultiCell(0, 6, $settings['exam_rules_text'], 0, 'L');

$pdf->Ln(20);

// 5. SIGNATURE
$pdf->SetX(130);
$pdf->SetFont('DejaVu','B',10);
$pdf->Cell(60, 6, 'Okul Müdürü', 0, 1, 'C');
$pdf->SetX(130);
$pdf->Cell(60, 6, 'İmza - Mühür', 0, 1, 'C');

$pdf->Output();
?>
