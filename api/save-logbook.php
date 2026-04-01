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
    $tanggal = $conn->real_escape_string($data['tanggal']);
    $title = $conn->real_escape_string($data['title']);
    $desc = $conn->real_escape_string($data['desc']);
    
    // Combine title and desc into kegiatan for the database
    $kegiatan = "**$title**\n$desc";

    $sql = "INSERT INTO Logbook (mahasiswa_id, tanggal, kegiatan, status_validasi) VALUES ($mahasiswa_id, '$tanggal', '$kegiatan', 'pending')";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
