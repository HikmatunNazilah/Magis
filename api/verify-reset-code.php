<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = $conn->real_escape_string($_POST['email']);
$code = $conn->real_escape_string(trim($_POST['code']));

$sql = "SELECT id FROM Password_Resets 
        WHERE email = '$email' AND code = '$code' AND expires_at > NOW() 
        ORDER BY id DESC LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Kode salah atau sudah kedaluwarsa.']);
}
?>
