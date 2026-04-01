-- Skema Database MAGIS
-- File ini dapat di-import langsung ke MySQL, MariaDB, atau phpMyAdmin

CREATE DATABASE IF NOT EXISTS magis_db;
USE magis_db;

-- --------------------------------------------------------
-- 1. Table: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id_user` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(255) NOT NULL,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'mentor', 'mahasiswa') NOT NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Table: periode_magang
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `periode_magang` (
    `id_periode` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama_periode` VARCHAR(255) NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `kuota` INT(11) NOT NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Table: mentor
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mentor` (
    `id_mentor` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `jabatan` VARCHAR(255) NULL,
    `no_telepon` VARCHAR(20) NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Table: mahasiswa
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mahasiswa` (
    `id_mahasiswa` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `nim` VARCHAR(50) NOT NULL,
    `universitas` VARCHAR(255) NULL,
    `jurusan` VARCHAR(255) NULL,
    `no_telepon` VARCHAR(20) NULL,
    `alamat` TEXT NULL,
    `mentor_id` BIGINT(20) UNSIGNED NULL,
    `periode_id` BIGINT(20) UNSIGNED NULL,
    `status` ENUM('pending', 'aktif', 'ditolak', 'selesai') DEFAULT 'pending',
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `cv_path` VARCHAR(255) NULL,
    `transkrip_path` VARCHAR(255) NULL,
    `surat_path` VARCHAR(255) NULL,
    `proposal_path` VARCHAR(255) NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
    FOREIGN KEY (`mentor_id`) REFERENCES `mentor`(`id_mentor`) ON DELETE SET NULL,
    FOREIGN KEY (`periode_id`) REFERENCES `periode_magang`(`id_periode`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Table: presensi
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `presensi` (
    `id_presensi` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mahasiswa_id` BIGINT(20) UNSIGNED NOT NULL,
    `tanggal` DATE NOT NULL,
    `jam_masuk` TIME NULL,
    `jam_keluar` TIME NULL,
    `foto_selfie` VARCHAR(255) NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id_mahasiswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Table: logbook
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logbook` (
    `id_logbook` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mahasiswa_id` BIGINT(20) UNSIGNED NOT NULL,
    `tanggal` DATE NOT NULL,
    `kegiatan` TEXT NOT NULL,
    `bukti_file` VARCHAR(255) NULL,
    `status_validasi` ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    `catatan_mentor` TEXT NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id_mahasiswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -- --------------------------------------------------------
-- 7. Table: pendaftaran
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pendaftaran` (
    `id_pendaftaran` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mahasiswa_id` BIGINT(20) UNSIGNED NOT NULL,
    `periode_id` BIGINT(20) UNSIGNED NOT NULL,
    `tanggal_daftar` DATE NOT NULL,
    `status` ENUM('pending', 'diterima', 'ditolak', 'selesai') DEFAULT 'pending',
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id_mahasiswa`) ON DELETE CASCADE,
    FOREIGN KEY (`periode_id`) REFERENCES `periode_magang`(`id_periode`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. Table: berkas_pendaftaran
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `berkas_pendaftaran` (
    `id_berkas` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pendaftaran_id` BIGINT(20) UNSIGNED NOT NULL,
    `jenis_berkas` VARCHAR(100) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran`(`id_pendaftaran`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 9. Table: penilaian
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `penilaian` (
    `id_penilaian` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mahasiswa_id` BIGINT(20) UNSIGNED NOT NULL,
    `mentor_id` BIGINT(20) UNSIGNED NOT NULL,
    `nilai_analisis` INT(11) NULL,
    `nilai_komunikasi` INT(11) NULL,
    `nilai_kerjasama` INT(11) NULL,
    `nilai_disiplin` INT(11) NULL,
    `nilai_akhir` DOUBLE NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id_mahasiswa`) ON DELETE CASCADE,
    FOREIGN KEY (`mentor_id`) REFERENCES `mentor`(`id_mentor`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 10. Table: sertifikat
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sertifikat` (
    `id_sertifikat` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mahasiswa_id` BIGINT(20) UNSIGNED NOT NULL,
    `nomor_sertifikat` VARCHAR(100) NOT NULL UNIQUE,
    `tanggal_terbit` DATE NOT NULL,
    `file_sertifikat` VARCHAR(255) NOT NULL,
    `qr_code` VARCHAR(255) NULL,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id_mahasiswa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
