<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $nama = $first_name . ' ' . $last_name;
    $username = explode('@', $email)[0]; // Simple username from email

    // Check if email already exists
    $check_sql = "SELECT id_user FROM Users WHERE email = '$email'";
    $check_res = $conn->query($check_sql);
    if ($check_res->num_rows > 0) {
        header("Location: register.php?error=exists");
        exit();
    }

    // Insert into Users
    $sql_user = "INSERT INTO Users (nama, username, email, password, role) VALUES ('$nama', '$username', '$email', '$password', 'mahasiswa')";
    
    if ($conn->query($sql_user)) {
        $user_id = $conn->insert_id;
        
        // Insert into Mahasiswa (default pending)
        $sql_mhs = "INSERT INTO Mahasiswa (user_id, status) VALUES ($user_id, 'pending')";
        $conn->query($sql_mhs);

        header("Location: index.php?success=registered");
    } else {
        header("Location: register.php?error=failed");
    }
    exit();
} else {
    header("Location: register.php");
    exit();
}
?>
