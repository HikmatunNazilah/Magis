<?php
require_once 'config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $no_telepon = $conn->real_escape_string($_POST['no_telepon']);
    $nim = $conn->real_escape_string($_POST['nim']);
    $universitas = $conn->real_escape_string($_POST['universitas']);
    $jurusan = $conn->real_escape_string($_POST['jurusan']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $new_password = $_POST['new_password'];

    $conn->begin_transaction();

    try {
        // 1. Update Users table
        $sql_user = "UPDATE Users SET nama = '$nama', email = '$email' WHERE id_user = $user_id";
        if (!$conn->query($sql_user)) {
            throw new Exception("Gagal memperbarui data user: " . $conn->error);
        }

        // 2. Update Mahasiswa table
        $sql_mhs = "UPDATE Mahasiswa SET 
                    nim = '$nim', 
                    universitas = '$universitas', 
                    jurusan = '$jurusan', 
                    no_telepon = '$no_telepon', 
                    alamat = '$alamat' 
                    WHERE user_id = $user_id";
        if (!$conn->query($sql_mhs)) {
            throw new Exception("Gagal memperbarui data mahasiswa: " . $conn->error);
        }

        // 3. Update Password if provided
        if (!empty($new_password)) {
            if (strlen($new_password) < 8) {
                throw new Exception("Password minimal 8 karakter.");
            }
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_pass = "UPDATE Users SET password = '$hashed_password' WHERE id_user = $user_id";
            if (!$conn->query($sql_pass)) {
                throw new Exception("Gagal memperbarui password: " . $conn->error);
            }
        }

        $conn->commit();
        
        // Update session
        $_SESSION['nama'] = $nama;
        
        $success_msg = "Profil Anda berhasil diperbarui!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = $e->getMessage();
    }
}

// Fetch current data
$sql = "SELECT m.*, u.nama, u.email 
        FROM Mahasiswa m 
        JOIN Users u ON m.user_id = u.id_user 
        WHERE u.id_user = $user_id";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: logout.php");
    exit();
}

$status = $data['status'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-blue-50 flex font-sans text-slate-800">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg h-screen fixed left-0 top-0 flex flex-col z-20">
        <div class="h-20 bg-blue-700 flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" />
            </svg>
            <h1 class="font-extrabold text-2xl text-white tracking-wider">MAGIS</h1>
        </div>
        <div class="p-4 text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">Menu Utama</div>
        <nav class="flex-1 px-4 space-y-1">
            <a href="dashboard-mhs.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span class="font-semibold">Beranda</span>
            </a>
            <a href="<?php echo $data['nim'] ? '#' : 'daftar-magang.php'; ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl <?php echo $data['nim'] ? 'opacity-40 cursor-not-allowed text-slate-400' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 <?php echo !$data['nim'] ? 'group-hover:scale-110 transition' : ''; ?>">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9Z" />
                </svg>
                <span class="<?php echo $data['nim'] ? 'font-medium' : 'font-semibold'; ?>">Daftar</span>
            </a>
            <a href="presensi.php" class="flex items-center gap-3 px-3 py-3 rounded-xl <?php echo ($status !== 'aktif' ? 'opacity-40 cursor-not-allowed' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-semibold">Presensi</span>
            </a>
            <a href="logbook.php" class="flex items-center gap-3 px-3 py-3 rounded-xl <?php echo ($status !== 'aktif' ? 'opacity-40 cursor-not-allowed' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <span class="font-semibold">Logbook</span>
            </a>
            <a href="mhs-sertifikat.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <span class="font-semibold">E-Sertifikat</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-100">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 00 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                Keluar
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 p-6 flex items-center justify-between border-b border-slate-200">
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Pengaturan Akun</h2>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($data['nama']); ?></p>
                    <p class="text-xs text-slate-500 font-medium"><?php echo htmlspecialchars($data['email']); ?></p>
                </div>
                <a href="mhs-settings.php" class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-black shadow-lg shadow-blue-200 hover:scale-105 transition active:scale-95">
                    <?php echo strtoupper(substr($data['nama'], 0, 1)); ?>
                </a>
            </div>
        </header>

        <!-- Content -->
        <div class="p-8">
            <div class="max-w-4xl mx-auto">
                <?php if ($success_msg): ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-5 rounded-2xl mb-8 flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <p class="font-bold"><?php echo $success_msg; ?></p>
                </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-5 rounded-2xl mb-8 flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <p class="font-bold"><?php echo $error_msg; ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="mhs-settings.php" class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                        <div class="p-10">
                            <h3 class="text-xl font-black text-slate-800 tracking-tight mb-8 flex items-center gap-3">
                                <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                                Informasi Profil
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nama Lengkap</label>
                                    <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Email</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">NIM</label>
                                    <input type="text" name="nim" value="<?php echo htmlspecialchars($data['nim']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">No. Telepon</label>
                                    <input type="text" name="no_telepon" value="<?php echo htmlspecialchars($data['no_telepon']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" placeholder="Contoh: 08123456789">
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Universitas</label>
                                    <input type="text" name="universitas" value="<?php echo htmlspecialchars($data['universitas']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Jurusan</label>
                                    <input type="text" name="jurusan" value="<?php echo htmlspecialchars($data['jurusan']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Alamat Domisili</label>
                                    <textarea name="alamat" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm"><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                        <div class="p-10">
                            <h3 class="text-xl font-black text-slate-800 tracking-tight mb-8 flex items-center gap-3">
                                <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                                Keamanan
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Password Baru (Kosongkan jika tidak ingin merubah)</label>
                                    <div class="relative">
                                        <input type="password" id="new_password" name="new_password" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" placeholder="••••••••">
                                        <button type="button" onclick="togglePassword('new_password', 'toggle-icon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition duration-200">
                                            <svg id="toggle-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-blue-600 text-white px-10 py-5 rounded-3xl font-black shadow-2xl shadow-blue-100 hover:bg-blue-700 hover:-translate-y-1 transition transform active:scale-95 duration-200">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
            }
        }
    </script>
</body>
</html>
