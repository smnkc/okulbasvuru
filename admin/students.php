<?php
require_once 'includes/header.php';

// Delete Logic
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    redirect('students.php');
}

// Fetch Students and some key details (Name/Surname assumed as Field 1 and 2 for columns, but we will fetch all to be safe)
// Optimized approach: Fetch students, then fetch answers for these students.
$students_stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
$students = $students_stmt->fetchAll();

// Fetch all fields to display specific important ones in columns (e.g. First specific fields)
$fields_stmt = $pdo->query("SELECT * FROM form_fields ORDER BY order_num ASC");
$all_fields = $fields_stmt->fetchAll();

// Helper to get answer
function getAnswer($student_id, $field_id, $pdo) {
    // This is N+1 problem potential, but for this scale it's acceptable. 
    // For optimization, one would pre-fetch all answers.
    // Let's pre-fetch all answers in one go for better performance if list is long.
    // But for simplicity of code structure in "Mid-senior" without complex ORM:
    global $answers_cache;
    if (!isset($answers_cache)) {
        $stmt = $pdo->query("SELECT * FROM student_answers");
        $all_answers = $stmt->fetchAll();
        foreach($all_answers as $a) {
            $answers_cache[$a['student_id']][$a['field_id']] = $a['answer'];
        }
    }
    return isset($answers_cache[$student_id][$field_id]) ? $answers_cache[$student_id][$field_id] : '-';
}

?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Başvuru Listesi</h2>
        <div>
            <a href="export_excel.php" class="btn btn-success"><i class="fas fa-file-excel"></i> Excel İndir</a>
            <!-- PDF export link can be added here or per student -->
           <a href="export_pdf_list.php" class="btn btn-danger"><i class="fas fa-file-pdf"></i> PDF Liste</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="studentsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>TC Kimlik</th>
                            <!-- Assume first 2 fields are Name and Surname for display purposes -->
                            <?php 
                            $count = 0;
                            foreach($all_fields as $field) {
                                if ($count < 3) { // Show first 3 dynamic fields as columns
                                    echo "<th>" . htmlspecialchars($field['label']) . "</th>";
                                }
                                $count++;
                            }
                            ?>
                            <th>Başvuru Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $student): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo htmlspecialchars($student['unique_id']); ?></td>
                            <?php 
                            $count = 0;
                            foreach($all_fields as $field) {
                                if ($count < 3) {
                                    echo "<td>" . htmlspecialchars(getAnswer($student['id'], $field['id'], $pdo)) . "</td>";
                                }
                                $count++;
                            }
                            ?>
                            <td><?php echo formatDateTr($student['created_at']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm" title="Düzenle"><i class="fas fa-edit"></i></a>
                                    <a href="../generate_pdf.php?id=<?php echo $student['id']; ?>" target="_blank" class="btn btn-info btn-sm text-white" title="Giriş Belgesi"><i class="fas fa-print"></i></a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $student['id']; ?>)" class="btn btn-danger btn-sm" title="Sil"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#studentsTable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json"
            },
            "order": [[ 0, "desc" ]]
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'SİLİNECEK!',
            text: "Bu işlem geri alınamaz! Öğrenci kaydı silinsin mi?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?delete=' + id;
            }
        })
    }
</script>

<?php require_once 'includes/footer.php'; ?>
