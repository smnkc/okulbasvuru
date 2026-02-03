<?php require_once 'includes/header.php'; ?>

<?php
// Get Stats
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$todays_students = $pdo->query("SELECT COUNT(*) FROM students WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Get recent applications
$stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
$recents = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white h-100" style="background-color: #6D4C41 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Toplam Başvuru</h6>
                            <h1 class="display-4 fw-bold"><?php echo $total_students; ?></h1>
                        </div>
                        <div>
                            <i class="fas fa-users fa-4x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Bugünkü Başvuru</h6>
                            <h1 class="display-4 fw-bold"><?php echo $todays_students; ?></h1>
                        </div>
                        <div>
                            <i class="fas fa-calendar-check fa-4x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Son 5 Başvuru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>TC Kimlik</th>
                            <th>Başvuru Tarihi</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recents) > 0): ?>
                            <?php foreach($recents as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['unique_id']); ?></td>
                                <td><?php echo formatDateTr($row['created_at']); ?></td>
                                <td>
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">Henüz başvuru yok.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
