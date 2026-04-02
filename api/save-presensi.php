<?php
require_once 'config.php';

// Matikan error reporting agar warning tidak merusak format JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mahasiswa_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit();
    }

    $type = $data['type']; // 'masuk' or 'keluar'
    $photoData = $data['photo']; // Base64
    $mahasiswa_id = $_SESSION['mahasiswa_id'];
    $tanggal = date('Y-m-d');
    $jam = date('H:i:s');

    // Persiapan simpan foto (Remote Upload to cPanel)
    $db_photo_path = null;
    if ($photoData) {
        $photo_name = "_" . $mahasiswa_id . "_" . time() . ".jpg";
        
        // Kirim foto ke bridge script di cPanel via cURL
        $ch = curl_init($remote_upload_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'photo' => $photoData,
            'filename' => $photo_name,
            'folder' => 'presensi'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $remote_res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Simpan path relatif ke database
        $db_photo_path = "presensi/" . $photo_name;
    }

    // Database Logic
    // Gunakan table name 'Presensi' (kapital) agar konsisten dengan file lain
    $check_sql = "SELECT id_presensi FROM Presensi WHERE mahasiswa_id = $mahasiswa_id AND tanggal = '$tanggal'";
    $check_res = $conn->query($check_sql);

    if ($type === 'masuk') {
        if ($check_res && $check_res->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Anda sudah absen MASUK hari ini.']);
            exit();
        }
        $sql = "INSERT INTO Presensi (mahasiswa_id, tanggal, jam_masuk, foto_selfie) VALUES ($mahasiswa_id, '$tanggal', '$jam', '$db_photo_path')";
    } else {
        if (!$check_res || $check_res->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Anda harus absen MASUK terlebih dahulu.']);
            exit();
        }
        $sql = "UPDATE Presensi SET jam_keluar = '$jam' WHERE mahasiswa_id = $mahasiswa_id AND tanggal = '$tanggal'";
    }

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'time' => $jam, 'date' => date('l, d F Y')]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
