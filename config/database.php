<?php
// ============================================
// Security Headers
// ============================================
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// ============================================
// Database Configuration
// ============================================
$host       = 'localhost';
$dbName     = 'quizacademy';
$dbUsername = 'root';
$dbPassword = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $dbUsername, $dbPassword, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Service temporarily unavailable.");
}
