<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdminAuth();

// Headers for download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="basvurular_'.date('Y-m-d').'.xls"');

// Fetch Fields
$fields_stmt = $pdo->query("SELECT * FROM form_fields ORDER BY order_num ASC");
$fields = $fields_stmt->fetchAll();

// Table Head
echo "ID\tTC No\tKayit Tarihi";
foreach($fields as $field) {
    echo "\t" . mb_convert_encoding($field['label'], "ISO-8859-9", "UTF-8"); // Excel encoding fix attempt for old xls
}
echo "\n";

// Fetch Students
$students_stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
$students = $students_stmt->fetchAll();

// Cache answers
$answers_cache = [];
$stmt = $pdo->query("SELECT * FROM student_answers");
$all_answers = $stmt->fetchAll();
foreach($all_answers as $a) {
    $answers_cache[$a['student_id']][$a['field_id']] = $a['answer'];
}

foreach ($students as $student) {
    echo $student['id'] . "\t";
    echo $student['unique_id'] . "\t";
    echo $student['created_at'];

    foreach($fields as $field) {
        $val = isset($answers_cache[$student['id']][$field['id']]) ? $answers_cache[$student['id']][$field['id']] : '';
        // Sanitize for Excel (avoid CSV injection or basic newlines breaking row)
        $val = str_replace(array("\t", "\n", "\r"), " ", $val);
        echo "\t" . mb_convert_encoding($val, "ISO-8859-9", "UTF-8");
    }
    echo "\n";
}
exit();
?>
