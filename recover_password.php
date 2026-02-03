<?php
require_once 'config.php';

// Güvenlik: Bu dosya sunucuda UNUTULMAMALIDIR.
// Çalıştırdıktan sonra siliniz.

echo "<h1>Yönetici Şifre Kurtarma</h1>";

$new_pass = '123456';
$hash = password_hash($new_pass, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    
    // Eğer admin kullanıcısı yoksa veya adı değişmişse diye ID 1'i de zorla güncelle
    $stmt2 = $pdo->prepare("UPDATE admins SET password = ? WHERE id = 1");
    $stmt2->execute([$hash]);

    echo "<div style='color: green; border: 2px solid green; padding: 20px; margin: 20px;'>";
    echo "<h3>BAŞARILI!</h3>";
    echo "<p>Admin şifresi <strong>123456</strong> olarak güncellendi.</p>";
    echo "<p>Lütfen giriş yaptıktan sonra güvenlik için <strong>bu dosyayı (recover_password.php) sunucudan SİLİNİZ.</strong></p>";
    echo "<a href='admin/login.php'>Giriş Yap</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>
