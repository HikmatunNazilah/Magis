<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mahasiswa_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $type = $data['type']; // 'masuk' or 'keluar'
    $photoData = $data['photo']; // Base64
    $mahasiswa_id = $_SESSION['mahasiswa_id'];
    $tanggal = date('Y-m-d');
    $jam = date('H:i:s');

    // Save Photo
    $photo_name = "presensi_" . $mahasiswa_id . "_" . time() . ".jpg";
    $photo_path = "../uploads/" . $photo_name;
    
    $photo_parts = explode(";base64,", $photoData);
    $photo_base64 = base64_decode($photo_parts[1]);
    file_put_contents($photo_path, $photo_base64);

    // Database Logic
    // Check if row exists for today
    $check_sql = "SELECT id_presensi FROM Presensi WHERE mahasiswa_id = $mahasiswa_id AND tanggal = '$tanggal'";
    $check_res = $conn->query($check_sql);

    if ($type === 'masuk') {
        if ($check_res->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Anda sudah absen MASUK hari ini.']);
            exit();
        }
        $sql = "INSERT INTO Presensi (mahasiswa_id, tanggal, jam_masuk, foto_selfie) VALUES ($mahasiswa_id, '$tanggal', '$jam', '$photo_name')";
    } else {
        if ($check_res->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Anda harus absen MASUK terlebih dahulu.']);
            exit();
        }
        $sql = "UPDATE Presensi SET jam_keluar = '$jam' WHERE mahasiswa_id = $mahasiswa_id AND tanggal = '$tanggal'";
    }

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'time' => $jam, 'date' => date('l, d F Y')]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
