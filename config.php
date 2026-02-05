<?php
// PHP Error Reporting for Debugging (Disable in production)
// PHP Error Reporting for Debugging (Disable in production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(0);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'exam_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Project URL (Update according to your server)
// Project URL (Auto-detect)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Determine path relative to document root using config.php location
$scriptDir = str_replace('\\', '/', __DIR__);
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$path = str_replace($docRoot, '', $scriptDir);

// Ensure path ends with slash
$path = rtrim($path, '/');
if ($path == '') $path = '/';
else $path .= '/';

define('BASE_URL', $protocol . "://" . $host . $path);

// Set Timezone
date_default_timezone_set('Europe/Istanbul');

// Establish PDO Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // In production, log this error instead of showing it
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
