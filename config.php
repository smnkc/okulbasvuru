<?php
// PHP Error Reporting for Debugging (Disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'DB_NAME_HERE');
define('DB_USER', 'DB_USER_HERE');
define('DB_PASS', 'DB_PASSWORD_HERE');
define('DB_CHARSET', 'utf8mb4');

// Project URL (Update according to your server)
// Project URL (Auto-detect)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$path = dirname($script);

// Ensure path ends with slash and fix backslashes on Windows
$path = rtrim(str_replace('\\', '/', $path), '/'); 
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
