<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mahasiswa_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$mahasiswa_id = $_SESSION['mahasiswa_id'];
$tanggal = date('Y-m-d');

$sql = "SELECT jam_masuk, jam_keluar FROM Presensi WHERE mahasiswa_id = $mahasiswa_id AND tanggal = '$tanggal'";
$result = $conn->query($sql);

$status = [
    'has_masuk' => false,
    'has_keluar' => false,
    'jam_masuk' => null,
    'jam_keluar' => null
];

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $status['has_masuk'] = !empty($row['jam_masuk']);
    $status['has_keluar'] = !empty($row['jam_keluar']);
    $status['jam_masuk'] = $row['jam_masuk'];
    $status['jam_keluar'] = $row['jam_keluar'];
}

echo json_encode(['success' => true, 'data' => $status]);
?>
