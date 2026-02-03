<?php
require_once 'includes/header.php';

// Delete Logic
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if used? Maybe just warn. For now, direct delete.
    $stmt = $pdo->prepare("DELETE FROM form_fields WHERE id = ?");
    $stmt->execute([$id]);
    redirect('form_builder.php'); // Redirect to clean URL
}

// Add/Edit Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $label = cleanInput($_POST['label']);
    $input_type = cleanInput($_POST['input_type']);
    $options = cleanInput($_POST['options']);
    $order_num = intval($_POST['order_num']);
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        // Update
        $id = intval($_POST['edit_id']);
        $stmt = $pdo->prepare("UPDATE form_fields SET label=?, input_type=?, options=?, order_num=?, is_required=? WHERE id=?");
        $stmt->execute([$label, $input_type, $options, $order_num, $is_required, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO form_fields (label, input_type, options, order_num, is_required) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$label, $input_type, $options, $order_num, $is_required]);
    }
    redirect('form_builder.php');
}

// Fetch All Fields
$stmt = $pdo->query("SELECT * FROM form_fields ORDER BY order_num ASC");
$fields = $stmt->fetchAll();

// Edit Mode Fetch
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM form_fields WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Form Builder Input -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><?php echo $edit_data ? 'Alanı Düzenle' : 'Yeni Alan Ekle'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if($edit_data): ?>
                            <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Etiket (Label)</label>
                            <input type="text" name="label" class="form-control" required value="<?php echo $edit_data ? $edit_data['label'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tip</label>
                            <select name="input_type" class="form-select" id="inputType" required>
                                <option value="text" <?php echo ($edit_data && $edit_data['input_type'] == 'text') ? 'selected' : ''; ?>>Metin (Text)</option>
                                <option value="number" <?php echo ($edit_data && $edit_data['input_type'] == 'number') ? 'selected' : ''; ?>>Sayı (Number)</option>
                                <option value="date" <?php echo ($edit_data && $edit_data['input_type'] == 'date') ? 'selected' : ''; ?>>Tarih (Date)</option>
                                <option value="select" <?php echo ($edit_data && $edit_data['input_type'] == 'select') ? 'selected' : ''; ?>>Seçim (Select)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="optionsDiv" style="display: <?php echo ($edit_data && $edit_data['input_type'] == 'select') ? 'block' : 'none'; ?>;">
                            <label class="form-label">Seçenekler (Virgülle Ayırın)</label>
                            <textarea name="options" class="form-control" placeholder="Örn: 5. Sınıf,6. Sınıf,7. Sınıf"><?php echo $edit_data ? $edit_data['options'] : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sıra No</label>
                            <input type="number" name="order_num" class="form-control" value="<?php echo $edit_data ? $edit_data['order_num'] : '0'; ?>">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_required" id="req" <?php echo (!$edit_data || $edit_data['is_required']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="req">Zorunlu Alan</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100"><?php echo $edit_data ? 'Güncelle' : 'Ekle'; ?></button>
                        <?php if($edit_data): ?>
                            <a href="form_builder.php" class="btn btn-secondary w-100 mt-2">İptal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Field List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Form Alanları</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Sıra</th>
                                    <th>Etiket</th>
                                    <th>Tip</th>
                                    <th>Zorunlu</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($fields as $field): ?>
                                <tr>
                                    <td><?php echo $field['order_num']; ?></td>
                                    <td><?php echo htmlspecialchars($field['label']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $field['input_type']; ?></span></td>
                                    <td>
                                        <?php if($field['is_required']): ?>
                                            <span class="badge bg-success">Evet</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Hayır</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?edit=<?php echo $field['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $field['id']; ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/Hide Options based on type
    document.getElementById('inputType').addEventListener('change', function() {
        var style = this.value == 'select' ? 'block' : 'none';
        document.getElementById('optionsDiv').style.display = style;
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu alan ve bu alana ait tüm öğrenci cevapları silinecek!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?delete=' + id;
            }
        })
    }
</script>

<?php require_once 'includes/footer.php'; ?>
