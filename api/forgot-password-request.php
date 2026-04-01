<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = $conn->real_escape_string($_POST['email']);

// 1. Check if user exists
$user_check = $conn->query("SELECT id_user FROM Users WHERE email = '$email'");
if ($user_check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar.']);
    exit();
}

// 2. Generate 6-digit code
$code = sprintf("%06d", mt_rand(0, 999999));
$expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// 3. Save to Password_Resets
$sql = "INSERT INTO Password_Resets (email, code, expires_at) VALUES ('$email', '$code', '$expires_at')";

// 4. Send Email via Real SMTP
require_once 'config-mail.php';
require_once '../libs/SmtpMailer.php';

$subject = "Kode Verifikasi Reset Password - MAGIS";
$message = "Halo,\n\nKode verifikasi untuk mereset password Anda adalah: $code\n\nKode ini berlaku selama 15 menit.";

$mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS);
$result = $mailer->send($email, $subject, $message, SMTP_FROM);

// Log to file for local testing as backup
$log_entry = "[" . date('Y-m-d H:i:s') . "] TO: $email | SMTP_STATUS: " . ($result['success'] ? 'OK' : 'FAIL: ' . $result['msg']) . " | CODE: $code\n";
file_put_contents('../logs_email.txt', $log_entry, FILE_APPEND);

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true, 
        'code' => $code,
        'mail_status' => $result['success'] ? 'sent_via_smtp' : 'error',
        'mail_error' => $result['msg'] ?? '',
        'message' => $result['success'] ? 'Kode berhasil dikirim ke email.' : 'Gagal mengirim email, silakan cek konfigurasi.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal membuat kode.']);
}
?>
