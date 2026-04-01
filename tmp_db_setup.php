<?php
$conn = new mysqli('103.30.147.68', 'sekelikn_magis_usr', '[]pl--Xt3)0-!WP[', 'sekelikn_magis_db');
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$sql = "CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    data TEXT,
    expires INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "Tabel 'sessions' berhasil dibuat.\n";
} else {
    echo "Error mambuat tabel: " . $conn->error . "\n";
}
$conn->close();
?>
