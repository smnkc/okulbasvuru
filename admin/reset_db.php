<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdminAuth();

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("TRUNCATE TABLE student_answers");
        $pdo->exec("TRUNCATE TABLE students");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "<h1>Veritabanı Sıfırlandı</h1>";
        echo "<p>Tüm öğrenci ve cevap kayıtları silindi. ID sayacı 1'den başlayacak.</p>";
        echo "<a href='admin/dashboard.php'>Panele Dön</a>";
    } catch (PDOException $e) {
        echo "Hata: " . $e->getMessage();
    }
} else {
    echo "<h1>Dikkat!</h1>";
    echo "<p>Bu işlem TÜM ÖĞRENCİ BAŞVURULARINI silecek ve geri alınamaz.</p>";
    echo "<a href='reset_db.php?confirm=yes' style='color:red; font-size:20px; font-weight:bold;'>Evet, Her Şeyi Sil ve Sıfırla</a>";
}
?>
