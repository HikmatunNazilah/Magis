<?php
require_once 'config.php';

echo "Memulai pembaruan database...\n";

// 1. Tambah kolom catatan_mentor ke tabel Logbook jika belum ada
$check_column = $conn->query("SHOW COLUMNS FROM `Logbook` LIKE 'catatan_mentor'");
if ($check_column->num_rows == 0) {
    $sql = "ALTER TABLE `Logbook` ADD COLUMN `catatan_mentor` TEXT NULL AFTER `status_validasi`";
    if ($conn->query($sql)) {
        echo "✅ Kolom 'catatan_mentor' berhasil ditambahkan ke tabel Logbook.\n";
    } else {
        echo "❌ Gagal menambahkan kolom: " . $conn->error . "\n";
    }
} else {
    echo "ℹ️ Kolom 'catatan_mentor' sudah ada.\n";
}

echo "Pembaruan selesai!\n";
?>
