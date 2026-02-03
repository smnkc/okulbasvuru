<?php

// Security: Clean Input
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Truncate Text
function textShorten($text, $limit = 400){
    $text = $text. " ";
    $text = substr($text, 0, $limit);
    $text = substr($text, 0, strrpos($text, ' '));
    return $text."...";
}

// Format Date (Tr)
function formatDateTr($date) {
    if (!$date) return '-';
    return date("d.m.Y H:i", strtotime($date));
}

// Redirect Helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Check Admin Login
function checkAdminAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ".BASE_URL."admin/login.php");
        exit();
    }
}

// Turkish Uppercase Helper
function str_to_upper_tr($str) {
    return mb_strtoupper(str_replace(array('i','ı','ğ','ü','ş','ö','ç'), array('İ','I','Ğ','Ü','Ş','Ö','Ç'), $str), 'utf-8');
}
?>
