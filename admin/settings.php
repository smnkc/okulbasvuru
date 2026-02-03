<?php
require_once 'includes/header.php';

$success_msg = '';
$error_msg = '';

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. General Settings Update
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'general_settings') {
        $school_name = cleanInput($_POST['school_name']);
        $exam_date_text = cleanInput($_POST['exam_date_text']);
        $exam_rules_text = $_POST['exam_rules_text']; // Allow HTML or newlines
        $contact_info_text = $_POST['contact_info_text'];
        $system_active = isset($_POST['system_active']) ? 1 : 0;

        // Handle Logo Upload
        $logo_path = $_POST['current_logo'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $target_dir = "../img/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
            $new_filename = "logo_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                $logo_path = "img/" . $new_filename;
            } else {
                $error_msg = "Logo yüklenirken hata oluştu.";
            }
        }

        if (empty($error_msg)) {
            $stmt = $pdo->prepare("UPDATE settings SET 
                school_name = ?, 
                logo_path = ?, 
                exam_date_text = ?, 
                exam_rules_text = ?, 
                contact_info_text = ?, 
                system_active = ? 
                WHERE id = 1");
            
            if ($stmt->execute([$school_name, $logo_path, $exam_date_text, $exam_rules_text, $contact_info_text, $system_active])) {
                $success_msg = "Genel ayarlar başarıyla güncellendi.";
            } else {
                $error_msg = "Veritabanı hatası.";
            }
        }
    }

    // 2. Account Settings Update
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'account_settings') {
        $new_username = cleanInput($_POST['new_username']);
        $new_password = $_POST['new_password'];

        if (empty($new_username)) {
            $error_msg = "Kullanıcı adı boş olamaz.";
        } else {
            // Check username uniqueness if needed (skipping for single admin simplicity or check self)
            // Ideally assume we are updating ID 1 or current session ID
            $admin_id = $_SESSION['admin_id'];

            if (!empty($new_password)) {
                // Update both
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
                $res = $stmt->execute([$new_username, $hash, $admin_id]);
            } else {
                // Update username only
                $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                $res = $stmt->execute([$new_username, $admin_id]);
            }

            if ($res) {
                $success_msg = "Hesap bilgileri güncellendi.";
                $_SESSION['admin_username'] = $new_username; // Update session
            } else {
                $error_msg = "Güncelleme sırasında hata oluştu.";
            }
        }
    }
}

// Get Current Settings
$stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt->fetch();
?>

<div class="container-fluid">
    <h2 class="mb-4">Genel Ayarlar</h2>

    <?php if ($success_msg): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Başarılı',
                text: '<?php echo $success_msg; ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- General Settings -->
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Genel Ayarlar</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="general_settings">
                        <input type="hidden" name="current_logo" value="<?php echo $settings['logo_path']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Okul Adı</label>
                                <input type="text" name="school_name" class="form-control" value="<?php echo htmlspecialchars($settings['school_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sınav Tarihi Metni</label>
                                <input type="text" name="exam_date_text" class="form-control" value="<?php echo htmlspecialchars($settings['exam_date_text']); ?>" placeholder="Örn: 15 Mart 2026 Pazar, Saat 10:00" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Okul Logosu</label>
                            <input type="file" name="logo" class="form-control">
                            <?php if ($settings['logo_path']): ?>
                                <div class="mt-2">
                                    <img src="../<?php echo $settings['logo_path']; ?>" alt="Logo" style="max-height: 50px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sınav Kuralları</label>
                            <textarea name="exam_rules_text" class="form-control" rows="5" required><?php echo htmlspecialchars($settings['exam_rules_text']); ?></textarea>
                            <small class="text-muted">Giriş belgesinin altında görünecek kurallar.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">İletişim / Yardım Metni</label>
                            <textarea name="contact_info_text" class="form-control" rows="3"><?php echo htmlspecialchars($settings['contact_info_text']); ?></textarea>
                            <small class="text-muted">Sistem kapalıyken veya footerda görünecek metin.</small>
                        </div>

                        <div class="mb-3 form-check form-switch ps-5">
                            <input class="form-check-input" type="checkbox" id="system_active" name="system_active" style="transform: scale(1.5);" <?php echo $settings['system_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label ms-2" for="system_active"><h5>Sistemi Aktif Et</h5></label>
                            <div class="form-text">Pasif yapıldığında "Başvurular Sona Ermiştir" uyarısı çıkar.</div>
                        </div>

                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Kaydet</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Admin Account Settings -->
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Yönetici Bilgileri</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="form_type" value="account_settings">
                        
                        <div class="mb-3">
                            <label class="form-label">Yeni Kullanıcı Adı</label>
                            <input type="text" name="new_username" class="form-control" value="<?php echo $_SESSION['admin_username'] ?? ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Yeni Şifre</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                        </div>

                        <button type="submit" class="btn btn-warning"><i class="fas fa-user-edit"></i> Güncelle</button>
                    </form>
                </div>
            </div>
            
            <div class="card bg-warning text-dark mb-4">
                <div class="card-body">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Dikkat: Şifreyi değiştirdikten sonra bir sonraki girişinizde yeni şifreyi kullanmalısınız.
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Tehlikeli Bölge</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Tüm öğrenci ve başvuru verilerini siler. Geri alınamaz!</p>
                    <a href="reset_db.php" class="btn btn-outline-danger w-100"><i class="fas fa-trash-alt"></i> Veritabanını Sıfırla</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
