<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $conn->real_escape_string($_POST['identifier']);
    $password = $_POST['password'];
    $source = $_POST['source'] ?? 'index'; // Default ke index jika tidak di-set

    // Tentukan URL redirect jika gagal berdasarkan sumber
    $failure_url = ($source === 'admin') ? "login-admin.php?error=invalid" : "index.php?error=invalid";
    $required_url = ($source === 'admin') ? "login-admin.php?error=required" : "index.php?error=required";

    if (empty($identifier) || empty($password)) {
        header("Location: " . $required_url);
        exit();
    }

    // Cek pengguna di database dengan cara aman (Prepared Statement)
    $stmt = $conn->prepare("SELECT id_user, nama, username, email, password, role FROM Users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: dashboard-admin.php");
            } else if ($user['role'] === 'mentor') {
                $user_id = $user['id_user'];
                $m_stmt = $conn->prepare("SELECT id_mentor FROM Mentor WHERE user_id = ?");
                $m_stmt->bind_param("i", $user_id);
                $m_stmt->execute();
                $m_result = $m_stmt->get_result();
                if ($m_result->num_rows === 1) {
                    $m_data = $m_result->fetch_assoc();
                    $_SESSION['mentor_id'] = $m_data['id_mentor'];
                }
                header("Location: dashboard-mentor.php");
            } else if ($user['role'] === 'mahasiswa') {
                // Get mahasiswa_id
                $user_id = $user['id_user'];
                $m_stmt = $conn->prepare("SELECT id_mahasiswa FROM Mahasiswa WHERE user_id = ?");
                $m_stmt->bind_param("i", $user_id);
                $m_stmt->execute();
                $m_result = $m_stmt->get_result();
                if ($m_result->num_rows === 1) {
                    $m_data = $m_result->fetch_assoc();
                    $_SESSION['mahasiswa_id'] = $m_data['id_mahasiswa'];
                }
                header("Location: dashboard-mhs.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
    }

    // If login fails
    header("Location: " . $failure_url);
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
