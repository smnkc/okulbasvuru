<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Fetch Settings
$stmt = $pdo->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch();

// Fetch Fields
$fields_stmt = $pdo->query("SELECT * FROM form_fields ORDER BY order_num ASC");
$fields = $fields_stmt->fetchAll();

// Handle AJAX Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'apply') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => ''];

    if (!$settings['system_active']) {
        echo json_encode(['status' => 'error', 'message' => 'Sistem şu anda başvuruya kapalıdır.']);
        exit;
    }

    try {
        $unique_id = cleanInput($_POST['unique_id']);
        
        // Validate TC (Simple check)
        if (strlen($unique_id) != 11 || !ctype_digit($unique_id)) {
            throw new Exception("TC Kimlik No 11 haneli ve rakamlardan oluşmalıdır.");
        }

        // Check Duplicate
        $check = $pdo->prepare("SELECT id FROM students WHERE unique_id = ?");
        $check->execute([$unique_id]);
        if ($check->rowCount() > 0) {
            throw new Exception("Bu TC Kimlik No ile daha önce başvuru yapılmış.");
        }

        // KVKK
        if (!isset($_POST['kvkk_approve'])) {
            throw new Exception("KVKK Metnini onaylamanız gerekmektedir.");
        }

        $pdo->beginTransaction();

        // Insert Student
        $ins = $pdo->prepare("INSERT INTO students (unique_id) VALUES (?)");
        $ins->execute([$unique_id]);
        $student_id = $pdo->lastInsertId();

        // Insert Answers
        foreach ($fields as $field) {
            $input_name = 'field_' . $field['id'];
            $val = '';
            if (isset($_POST[$input_name])) {
                if (is_array($_POST[$input_name])) {
                    $val = implode(',', $_POST[$input_name]);
                } else {
                    $val = $_POST[$input_name];
                }
            }
            $val = cleanInput($val);
            $val = str_to_upper_tr($val); // Uppercase

            if ($field['is_required'] && $val === '') {
                throw new Exception($field['label'] . " alanı boş bırakılamaz.");
            }

            $ans_ins = $pdo->prepare("INSERT INTO student_answers (student_id, field_id, answer) VALUES (?, ?, ?)");
            $ans_ins->execute([$student_id, $field['id'], $val]);
        }

        $pdo->commit();
        $response['status'] = 'success';
        $response['redirect'] = 'success.php?id=' . $student_id;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// Handle Query (Sorgula) via AJAX or POST
// Let's do simple POST for Query for simplicity since it's a redirect mainly
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'query') {
    $q_tc = cleanInput($_POST['query_tc']);
    $stmt = $pdo->prepare("SELECT id FROM students WHERE unique_id = ?");
    $stmt->execute([$q_tc]);
    $res = $stmt->fetch();
    
    if ($res) {
        redirect('generate_pdf.php?id=' . $res['id']);
    } else {
        $query_error = "Kayıt bulunamadı. Lütfen bilgilerinizi kontrol ediniz veya sorun yaşıyorsanız okul ile iletişime geçiniz.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['school_name']); ?> - Başvuru Sistemi</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- InputMask -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
        :root { --main-color: #6D4C41; --bg-color: #f8f9fa; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg-color); color: #333; }
        .header { background-color: white; padding: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-bottom: 5px solid var(--main-color); margin-bottom: 30px; }
        .school-logo { max-height: 80px; }
        .nav-tabs .nav-link { color: #555; font-weight: bold; border: none; border-bottom: 3px solid transparent; }
        .nav-tabs .nav-link.active { color: var(--main-color); border-bottom: 3px solid var(--main-color); background: none; }
        .nav-tabs .nav-link:hover { border-color: transparent; }
        .card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .btn-primary { background-color: var(--main-color); border-color: var(--main-color); padding: 12px 30px; font-weight: bold; }
        .btn-primary:hover { background-color: #5D4037; border-color: #5D4037; }
        .form-label { font-weight: 500; color: #444; }
        .required-star { color: red; }
        footer { margin-top: 50px; background: #333; color: #ccc; padding: 30px 0; }
    </style>
</head>
<body>

<div class="header text-center">
    <div class="container">
        <?php if ($settings['logo_path']): ?>
            <img src="<?php echo $settings['logo_path']; ?>" alt="Logo" class="school-logo mb-2">
        <?php endif; ?>
        <h2 class="m-0" style="color: var(--main-color);"><?php echo htmlspecialchars($settings['school_name']); ?></h2>
        <h5 class="text-muted mt-2">Öğrenci Kabul Sınavı Başvuru Sistemi</h5>
    </div>
</div>

<div class="container" style="max-width: 800px;">
    
    <?php if (!$settings['system_active']): ?>
        <div class="card bg-danger text-white mb-4 text-center">
            <div class="card-body py-5">
                <h1 class="display-4"><i class="fas fa-lock"></i></h1>
                <h3>Başvurular Sona Ermiştir</h3>
                <p class="lead mt-3"><?php echo nl2br(htmlspecialchars($settings['contact_info_text'])); ?></p>
            </div>
        </div>
    <?php else: ?>

        <ul class="nav nav-tabs nav-fill mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="apply-tab" data-bs-toggle="tab" data-bs-target="#apply" type="button" role="tab"><i class="fas fa-file-signature"></i> Başvuru Yap</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="query-tab" data-bs-toggle="tab" data-bs-target="#query" type="button" role="tab"><i class="fas fa-search"></i> Başvuru Sorgula / Belge İndir</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            
            <!-- APPLY TAB -->
            <div class="tab-pane fade show active" id="apply" role="tabpanel">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($settings['exam_date_text']); ?>
                        </div>

                        <form id="applyForm">
                            <input type="hidden" name="action" value="apply">
                            
                            <div class="mb-4">
                                <label class="form-label">T.C. Kimlik Numarası <span class="required-star">*</span></label>
                                <input type="text" name="unique_id" id="tcInfo" class="form-control form-control-lg" placeholder="11 haneli TC no giriniz" required>
                            </div>

                            <hr>

                            <?php foreach($fields as $field): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?php echo htmlspecialchars($field['label']); ?>
                                        <?php if($field['is_required']) echo '<span class="required-star">*</span>'; ?>
                                    </label>

                                    <?php 
                                    $req = $field['is_required'] ? 'required' : '';
                                    $uname = 'field_' . $field['id'];
                                    
                                    if ($field['input_type'] == 'textarea'): ?>
                                        <textarea name="<?php echo $uname; ?>" class="form-control" rows="3" <?php echo $req; ?>></textarea>
                                    
                                    <?php elseif ($field['input_type'] == 'select'): 
                                        $opts = explode(',', $field['options']);
                                    ?>
                                        <select name="<?php echo $uname; ?>" class="form-select" <?php echo $req; ?>>
                                            <option value="">Seçiniz</option>
                                            <?php foreach($opts as $opt): ?>
                                                <option value="<?php echo trim($opt); ?>"><?php echo trim($opt); ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php else: 
                                        // Detect Phone field by label to apply mask
                                        if (mb_stripos($field['label'], 'telefon') !== false || mb_stripos($field['label'], 'tel') !== false) {
                                            $class = 'form-control phone-mask';
                                        } else {
                                            $class = 'form-control uppercase-input';
                                        }
                                    ?>
                                        <input type="<?php echo $field['input_type']; ?>" name="<?php echo $uname; ?>" class="<?php echo $class; ?>" <?php echo $req; ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="mb-4 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="kvkk" name="kvkk_approve" required>
                                    <label class="form-check-label" for="kvkk">
                                        Kişisel verilerimin işlenmesini ve sınav süreçlerinde kullanılmasını onaylıyorum. (KVKK)
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg" id="submitBtn">BAŞVURUYU TAMAMLA</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- QUERY TAB -->
            <div class="tab-pane fade" id="query" role="tabpanel">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <h4 class="mb-4">Başvuru Durumu Sorgula</h4>
                        
                        <?php if(isset($query_error)): ?>
                            <div class="alert alert-danger mb-4">
                                <?php echo $query_error; ?>
                                <hr>
                                <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($settings['contact_info_text'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="index.php?tab=query">
                            <input type="hidden" name="action" value="query">
                            <div class="mb-3" style="max-width: 300px; margin: 0 auto;">
                                <input type="text" name="query_tc" class="form-control form-control-lg text-center" placeholder="T.C. Kimlik No" maxlength="11" required>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-search"></i> SORGULA VE BELGE İNDİR</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<footer class="text-center">
    <div class="container">
        <p class="mb-2"><?php echo htmlspecialchars($settings['school_name']); ?></p>
        <p class="small text-white-50">
            <?php echo nl2br(htmlspecialchars($settings['contact_info_text'])); ?>
        </p>
    </div>
</footer>

<script>
    $(document).ready(function(){
        // Input Masks
        $('#tcInfo').inputmask("99999999999"); // 11 digits
        $('.phone-mask').inputmask("(599) 999 99 99");

        // Uppercase Logic
        $('.uppercase-input, textarea').on('input', function() {
            var val = $(this).val();
            // Turkish aware upper
            var upper = val.replace(/i/g, "İ").toLocaleUpperCase('tr-TR');
            $(this).val(upper);
        });

        // Form Submit
        $('#applyForm').on('submit', function(e){
            e.preventDefault();
            var btn = $('#submitBtn');
            var originalText = btn.text();
            
            btn.prop('disabled', true).text('İşleniyor...');

            $.ajax({
                url: 'index.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if(res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başvuru Alındı!',
                            text: 'Giriş belgesi sayfasına yönlendiriliyorsunuz...',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = res.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: res.message
                        });
                        btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    Swal.fire('Hata', 'Sunucu hatası oluştu.', 'error');
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });

        <?php if(isset($query_error) || isset($_GET['tab']) && $_GET['tab']=='query'): ?>
            var queryTab = new bootstrap.Tab(document.querySelector('#query-tab'));
            queryTab.show();
        <?php endif; ?>
    });
</script>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Bootstrap Bundle JS (Required for Tabs etc) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</body>
</html>
