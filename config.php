<?php
date_default_timezone_set('Asia/Jakarta');
// Configuration for Database Connection
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

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
