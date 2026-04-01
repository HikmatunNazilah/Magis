<?php
require_once 'config.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Drop existing failure if any (tables might be partially created without FK)
    // Actually CREATE TABLE IF NOT EXISTS is safe if it failed completely.

    // Table Penilaian
    $res = $conn->query("SHOW TABLES LIKE 'penilaian'");
    if ($res->num_rows === 0) {
        echo "Creating table 'penilaian'...\n";
        $sql_pen = "CREATE TABLE `penilaian` (
            `id_penilaian` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `mahasiswa_id` BIGINT(20) UNSIGNED NOT NULL,
            `mentor_id` BIGINT(20) UNSIGNED NOT NULL,
            `nilai_analisis` INT NULL,
            `nilai_komunikasi` INT NULL,
            `nilai_kerjasama` INT NULL,
            `nilai_disiplin` INT NULL,
            `nilai_akhir` FLOAT NULL,
            `created_by` INT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_by` INT NULL,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id_mahasiswa`) ON DELETE CASCADE,
            FOREIGN KEY (`mentor_id`) REFERENCES `mentor`(`id_mentor`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($sql_pen);
        echo "Table 'penilaian' created successfully.\n";
    }

    // Table Sertifikat
    $res_ser = $conn->query("SHOW TABLES LIKE 'sertifikat'");
    if ($res_ser->num_rows === 0) {
        echo "Creating table 'sertifikat'...\n";
        $sql_ser = "CREATE TABLE `sertifikat` (
            `id_sertifikat` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `mahasiswa_id` BIGINT(20) UNSIGNED NOT NULL,
            `nomor_sertifikat` VARCHAR(100) NOT NULL UNIQUE,
            `tanggal_terbit` DATE NOT NULL,
            `file_sertifikat` VARCHAR(255) NOT NULL,
            `qr_code` VARCHAR(255) NULL,
            `created_by` INT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_by` INT NULL,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id_mahasiswa`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($sql_ser);
        echo "Table 'sertifikat' created successfully.\n";
    }

    echo "Database setup complete.\n";

} catch (mysqli_sql_exception $e) {
    echo "MySQL Error: " . $e->getMessage() . "\n";
}
?>
