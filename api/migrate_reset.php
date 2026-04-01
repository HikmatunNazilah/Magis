<?php
$conn = new mysqli('localhost', 'root', '', 'magis_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "CREATE TABLE IF NOT EXISTS Password_Resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "Table Password_Resets created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
$conn->close();
?>
