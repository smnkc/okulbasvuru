<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';
require_once '../includes/functions.php';

// Auth Check
checkAdminAuth();

// Get School Info for Title
$stmt = $pdo->prepare("SELECT school_name FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - <?php echo htmlspecialchars($settings['school_name']); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #6D4C41; color: white; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; }
        .sidebar i { width: 25px; }
        .card { border: none; shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 250px;">
        <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Yönetim Paneli</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Başvurular
                </a>
            </li>
            <li>
                <a href="form_builder.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'form_builder.php' ? 'active' : ''; ?>">
                    <i class="fas fa-edit"></i> Form Yönetimi
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i> Ayarlar
                </a>
            </li>
             <li>
                <a href="../index.php" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Siteye Git
                </a>
            </li>
        </ul>
        <hr>
        <div>
            <a href="logout.php" class="text-white"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
