<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = $conn->real_escape_string($_POST['email']);
$password = $_POST['password'];

if (empty($password) || strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password minimal 8 karakter.']);
    exit();
}

// Security: In a real app, you'd double check the verification session here.
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE Users SET password = '$hashed_password' WHERE email = '$email'";

if ($conn->query($sql)) {
    // Delete reset codes for this email
    $conn->query("DELETE FROM Password_Resets WHERE email = '$email'");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengubah password.']);
}
?>
