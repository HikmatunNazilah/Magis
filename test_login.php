<?php
require_once 'config.php';

$stmt = $conn->query("SELECT * FROM Users WHERE username='mentor'");
$user = $stmt->fetch_assoc();

if ($user) {
    echo "Found mentor\n";
    echo "Password hash: " . $user['password'] . "\n";
    echo "Verify 'mentor123': " . (password_verify('mentor123', $user['password']) ? 'YES' : 'NO') . "\n";
} else {
    echo "Not found";
}
?>
