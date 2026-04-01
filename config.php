<?php
date_default_timezone_set('Asia/Jakarta');
// Configuration for Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "magis_db";

// Create Connection
$conn = new mysqli($host, $username, $password, $database);

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
