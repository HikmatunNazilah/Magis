<?php
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-admin.php");
    exit();
}

if (isset($_GET['id'])) {
    $mahasiswa_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // 1. Verify existence and grade
    $sql_cek = "SELECT m.id_mahasiswa, m.status, p.nilai_akhir 
                FROM mahasiswa m 
                JOIN penilaian p ON p.mahasiswa_id = m.id_mahasiswa 
                WHERE m.id_mahasiswa = ?";
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("i", $mahasiswa_id);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result();

    if ($res_cek->num_rows === 0) {
        header("Location: admin-sertifikat.php?status=error_no_grade");
        exit();
    }

    $data = $res_cek->fetch_assoc();

    // 2. Check if already issued
    $sql_ser = "SELECT id_sertifikat FROM sertifikat WHERE mahasiswa_id = ?";
    $stmt_ser = $conn->prepare($sql_ser);
    $stmt_ser->bind_param("i", $mahasiswa_id);
    $stmt_ser->execute();
    $res_ser = $stmt_ser->get_result();

    if ($res_ser->num_rows > 0) {
        header("Location: admin-sertifikat.php?status=already_issued");
        exit();
    }

    // 3. Generate Certificate Data
    $nomor_sertifikat = "MAGIS/" . date('Y') . "/" . str_pad($mahasiswa_id, 4, '0', STR_PAD_LEFT);
    $tanggal_terbit = date('Y-m-d');
    $file_name = "sertifikat_" . $mahasiswa_id . "_" . time() . ".pdf"; // Placeholder for file generation logic

    // 4. Insert to Database
    $sql_insert = "INSERT INTO sertifikat (mahasiswa_id, nomor_sertifikat, tanggal_terbit, file_sertifikat, created_by) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isssi", $mahasiswa_id, $nomor_sertifikat, $tanggal_terbit, $file_name, $user_id);

    if ($stmt_insert->execute()) {
        // Optional: Update student status to 'selesai' if not already
        $conn->query("UPDATE mahasiswa SET status = 'selesai' WHERE id_mahasiswa = $mahasiswa_id");
        
        header("Location: admin-sertifikat.php?status=success");
        exit();
    } else {
        header("Location: admin-sertifikat.php?status=error_save");
        exit();
    }
} else {
    header("Location: admin-sertifikat.php");
    exit();
}
?>
