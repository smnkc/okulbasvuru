<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) {
    redirect('index.php');
}

// Check if student exists
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    die("Öğrenci bulunamadı.");
}

// Get School Info
$stmt2 = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt2->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvuru Başarılı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: sans-serif; }
        .success-card { max-width: 600px; width: 90%; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; text-align: center; }
        .top-icon { background-color: #4CAF50; color: white; padding: 40px; }
        .top-icon i { font-size: 80px; }
        .content { padding: 40px; }
        .btn-print { background-color: #6D4C41; color: white; padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: bold; display: inline-block; transition: all 0.3s; }
        .btn-print:hover { background-color: #5D4037; transform: translateY(-2px); color: white; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

<div class="success-card">
    <div class="top-icon">
        <i class="fas fa-check-circle"></i>
    </div>
    <div class="content">
        <h2 class="mb-3">Başvurunuz Alındı!</h2>
        <p class="text-muted mb-4">
            Sayın aday, başvurunuz <strong><?php echo htmlspecialchars($settings['school_name']); ?></strong> sistemine başarıyla kaydedilmiştir.<br>
            Sınava giriş belgenizi <?php echo $settings['allow_download'] ? 'aşağıdaki butona tıklayarak hemen indirebilirsiniz.' : 'okul idaresinden temin edebilirsiniz.'; ?>
        </p>

        <?php if ($settings['allow_download']): ?>
            <a href="generate_pdf.php?id=<?php echo $id; ?>" target="_blank" class="btn-print">
                <i class="fas fa-file-pdf me-2"></i> GİRİŞ BELGESİ İNDİR
            </a>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                <?php 
                    $msg = $settings['download_disabled_text'];
                    echo !empty($msg) ? htmlspecialchars($msg) : "Giriş Belgenizi Okul İdaresinden Almayı Unutmayın."; 
                ?>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="index.php" class="text-muted small text-decoration-none">Ana Sayfaya Dön</a>
        </div>
    </div>
</div>

</body>
</html>
