<?php
require_once 'config.php';

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor' || !isset($_SESSION['mentor_id'])) {
    header("Location: index.php");
    exit();
}

$mentor_id = $_SESSION['mentor_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_logbook = intval($_POST['id_logbook']);
    $mahasiswa_id = intval($_POST['mahasiswa_id']);
    $action = $_POST['action'];
    $catatan_mentor = isset($_POST['catatan_mentor']) ? trim($_POST['catatan_mentor']) : null;

    // Verify ownership (Mentor has access to this Mahasiswa)
    $sql_cek = "SELECT id_mahasiswa FROM Mahasiswa WHERE id_mahasiswa = ? AND mentor_id = ?";
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("ii", $mahasiswa_id, $mentor_id);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result();
    
    if ($res_cek->num_rows === 0) {
        // Unauthorized
        header("Location: dashboard-mentor.php");
        exit();
    }

    if ($action === 'approve') {
        $status = 'disetujui';
        $sql_update = "UPDATE Logbook SET status_validasi = ?, catatan_mentor = NULL, updated_at = NOW() WHERE id_logbook = ? AND mahasiswa_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sii", $status, $id_logbook, $mahasiswa_id);
    } else if ($action === 'reject') {
        $status = 'ditolak';
        $sql_update = "UPDATE Logbook SET status_validasi = ?, catatan_mentor = ?, updated_at = NOW() WHERE id_logbook = ? AND mahasiswa_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssii", $status, $catatan_mentor, $id_logbook, $mahasiswa_id);
    } else {
        header("Location: mentor-mahasiswa-detail.php?id=$mahasiswa_id&status=error");
        exit();
    }

    if ($stmt_update->execute()) {
        header("Location: mentor-mahasiswa-detail.php?id=$mahasiswa_id&status=success");
        exit();
    } else {
         header("Location: mentor-mahasiswa-detail.php?id=$mahasiswa_id&status=error");
         exit();
    }
} else {
    header("Location: dashboard-mentor.php");
    exit();
}
?>
