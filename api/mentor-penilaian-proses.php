<?php
require_once 'config.php';

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor' || !isset($_SESSION['mentor_id'])) {
    header("Location: index.php");
    exit();
}

$mentor_id = $_SESSION['mentor_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mahasiswa_id = intval($_POST['mahasiswa_id']);
    $nilai_disiplin = intval($_POST['nilai_disiplin']);
    $nilai_analisis = intval($_POST['nilai_analisis']);
    $nilai_komunikasi = intval($_POST['nilai_komunikasi']);
    $nilai_kerjasama = intval($_POST['nilai_kerjasama']);

    // Ensure values are within 0 - 100
    $v_disiplin = max(0, min(100, $nilai_disiplin));
    $v_analisis = max(0, min(100, $nilai_analisis));
    $v_komunikasi = max(0, min(100, $nilai_komunikasi));
    $v_kerjasama = max(0, min(100, $nilai_kerjasama));

    // Calculate Final Score
    $nilai_akhir = ($v_disiplin + $v_analisis + $v_komunikasi + $v_kerjasama) / 4;

    // Verify ownership
    $sql_cek = "SELECT id_mahasiswa FROM Mahasiswa WHERE id_mahasiswa = ? AND mentor_id = ?";
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("ii", $mahasiswa_id, $mentor_id);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result();

    if ($res_cek->num_rows === 0) {
        header("Location: dashboard-mentor.php");
        exit();
    }

    // Check if already evaluated, to prevent duplicates
    $sql_check_pen = "SELECT id_penilaian FROM Penilaian WHERE mahasiswa_id = ? AND mentor_id = ?";
    $stmt_check_pen = $conn->prepare($sql_check_pen);
    $stmt_check_pen->bind_param("ii", $mahasiswa_id, $mentor_id);
    $stmt_check_pen->execute();
    $res_check_pen = $stmt_check_pen->get_result();

    if ($res_check_pen->num_rows > 0) {
        // Validation exists
        header("Location: mentor-penilaian.php?id=$mahasiswa_id&status=error");
        exit();
    }

    // Insert to database
    $sql_insert = "INSERT INTO Penilaian (mahasiswa_id, mentor_id, nilai_analisis, nilai_komunikasi, nilai_kerjasama, nilai_disiplin, nilai_akhir) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiiiidd", $mahasiswa_id, $mentor_id, $v_analisis, $v_komunikasi, $v_kerjasama, $v_disiplin, $nilai_akhir);

    if ($stmt_insert->execute()) {
        header("Location: mentor-penilaian.php?id=$mahasiswa_id&status=success");
        exit();
    } else {
        header("Location: mentor-penilaian.php?id=$mahasiswa_id&status=error");
        exit();
    }
} else {
    header("Location: dashboard-mentor.php");
    exit();
}
?>
