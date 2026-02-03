<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) {
    redirect('students.php');
}

// Fetch Student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    redirect('students.php');
}

// Fetch Fields
$fields_stmt = $pdo->query("SELECT * FROM form_fields ORDER BY order_num ASC");
$fields = $fields_stmt->fetchAll();

// Fetch Existing Answers
$answers_stmt = $pdo->prepare("SELECT * FROM student_answers WHERE student_id = ?");
$answers_stmt->execute([$id]);
$answers_raw = $answers_stmt->fetchAll();
$s_answers = [];
foreach($answers_raw as $a) {
    $s_answers[$a['field_id']] = $a['answer']; // Map field_id => answer
}

// Update Logic
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unique_id = cleanInput($_POST['unique_id']);
    
    // Update TC (Check unique logic if changed, skipping for simplicity or basic check)
    $pdo->prepare("UPDATE students SET unique_id = ? WHERE id = ?")->execute([$unique_id, $id]);

    // Update Answers
    foreach($fields as $field) {
        $input_name = 'field_' . $field['id'];
        if (isset($_POST[$input_name])) {
            $val = is_array($_POST[$input_name]) ? implode(',', $_POST[$input_name]) : $_POST[$input_name];
            $val = cleanInput($val);
            $val = str_to_upper_tr($val); // Enforce uppercase on edit too

            // Check if exists
            if (isset($s_answers[$field['id']])) {
                $upd = $pdo->prepare("UPDATE student_answers SET answer = ? WHERE student_id = ? AND field_id = ?");
                $upd->execute([$val, $id, $field['id']]);
            } else {
                $ins = $pdo->prepare("INSERT INTO student_answers (student_id, field_id, answer) VALUES (?, ?, ?)");
                $ins->execute([$id, $field['id'], $val]);
            }
        }
    }
    $success_msg = "Bilgiler güncellendi.";
    
    // Refresh Data
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    // Refresh Answers
    $answers_stmt->execute([$id]);
    $answers_raw = $answers_stmt->fetchAll();
    $s_answers = [];
    foreach($answers_raw as $a) {
        $s_answers[$a['field_id']] = $a['answer'];
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Öğrenci Düzenle</h2>
        <a href="students.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Geri Dön</a>
    </div>

    <?php if ($success_msg): ?>
        <script>
            Swal.fire({ icon: 'success', title: 'Başarılı', text: '<?php echo $success_msg; ?>', timer: 1500, showConfirmButton: false });
        </script>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">T.C. Kimlik No</label>
                    <input type="text" name="unique_id" class="form-control" value="<?php echo htmlspecialchars($student['unique_id']); ?>" maxlength="11" required>
                </div>

                <hr>
                
                <?php foreach($fields as $field): ?>
                    <div class="mb-3">
                        <label class="form-label"><?php echo htmlspecialchars($field['label']); ?></label>
                        <?php 
                        $val = isset($s_answers[$field['id']]) ? $s_answers[$field['id']] : '';
                        
                        if ($field['input_type'] == 'textarea'): ?>
                            <textarea name="field_<?php echo $field['id']; ?>" class="form-control" rows="3"><?php echo htmlspecialchars($val); ?></textarea>
                        
                        <?php elseif ($field['input_type'] == 'select'): 
                            $opts = explode(',', $field['options']);
                        ?>
                            <select name="field_<?php echo $field['id']; ?>" class="form-select">
                                <option value="">Seçiniz</option>
                                <?php foreach($opts as $opt): 
                                    $opt = trim($opt);
                                    $selected = ($val == $opt) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $opt; ?>" <?php echo $selected; ?>><?php echo $opt; ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php else: ?>
                            <input type="<?php echo $field['input_type']; ?>" name="field_<?php echo $field['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($val); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Güncelle</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
