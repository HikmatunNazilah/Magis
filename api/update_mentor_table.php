<?php
require_once 'config.php';

$conn->query("ALTER TABLE Mentor ADD COLUMN no_telepon VARCHAR(20) NULL AFTER jabatan");
echo "Database updated: no_telepon added to Mentor table.";
?>
