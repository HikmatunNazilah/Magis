<?php
date_default_timezone_set('Asia/Jakarta');
// Configuration for Database Connection
$host = getenv('DB_HOST') ?: '103.30.147.68';
$user = getenv('DB_USER') ?: 'sekelikn_magis_usr';
$pass = getenv('DB_PASSWORD') ?: '[]pl--Xt3)0-!WP[';
$db   = getenv('DB_NAME') ?: 'sekelikn_magis_db';
$port = getenv('DB_PORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);

// Check Connection
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set Charset to ensure iconv and characters work correctly
$conn->set_charset("utf8mb4");

// Start Session globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
