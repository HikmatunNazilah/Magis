<?php
require_once 'config.php';

// Delete existing test users if they exist
$conn->query("DELETE FROM Users WHERE username IN ('admin', 'mentor', 'budi')");

// Hash Password for test users
$password_admin = password_hash('admin123', PASSWORD_DEFAULT);
$password_mhs = password_hash('mhs123', PASSWORD_DEFAULT);

// 1. Create Admin
$sql1 = "INSERT INTO Users (nama, username, email, password, role) VALUES ('Admin Utama', 'admin', 'admin@magis.com', '$password_admin', 'admin')";
$conn->query($sql1);
$admin_id = $conn->insert_id;

// 2. Create Mahasiswa User
$sql2 = "INSERT INTO Users (nama, username, email, password, role) VALUES ('Budi Mahasiswa', 'budi', 'budi@student.com', '$password_mhs', 'mahasiswa')";
$conn->query($sql2);
$user_id = $conn->insert_id;

// 3. Create Mentor
$password_mentor = password_hash('mentor123', PASSWORD_DEFAULT);
$sql3 = "INSERT INTO Users (nama, username, email, password, role) VALUES ('Mentor Pembimbing', 'mentor', 'mentor@magis.com', '$password_mentor', 'mentor')";
$conn->query($sql3);
$mentor_user_id = $conn->insert_id;

// Insert into Mentor table
$sql4 = "INSERT INTO Mentor (user_id, jabatan) VALUES ($mentor_user_id, 'Senior Software Engineer')";
$conn->query($sql4);
$mentor_id = $conn->insert_id;

// 4. Create Mahasiswa Detail linked to Mentor
$sql5 = "INSERT INTO Mahasiswa (user_id, nim, universitas, jurusan, status, mentor_id) VALUES ($user_id, '12345678', 'Universitas Gadjah Mada', 'Teknik Informatika', 'aktif', $mentor_id)";
$conn->query($sql5);

echo "Test users created successfully!\n";
echo "Admin: admin / admin123\n";
echo "Mentor: mentor / mentor123\n";
echo "Mahasiswa: budi / mhs123\n";
?>
