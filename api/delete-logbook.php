<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mahasiswa_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $mahasiswa_id = $_SESSION['mahasiswa_id'];
    $id_logbook = (int)$data['id_logbook'];

    // Only allow deleting own logbooks that are pending or ditolak
    $check = $conn->query("SELECT status_validasi FROM Logbook WHERE id_logbook = $id_logbook AND mahasiswa_id = $mahasiswa_id");
    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Logbook tidak ditemukan.']);
        exit();
    }
    $row = $check->fetch_assoc();
    if ($row['status_validasi'] === 'disetujui') {
        echo json_encode(['success' => false, 'message' => 'Logbook yang sudah disetujui tidak dapat dihapus.']);
        exit();
    }

    $sql = "DELETE FROM Logbook WHERE id_logbook=$id_logbook AND mahasiswa_id=$mahasiswa_id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
