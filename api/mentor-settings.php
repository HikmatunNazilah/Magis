<?php
require_once 'config.php';

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor' || !isset($_SESSION['mentor_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mentor_id = $_SESSION['mentor_id'];
$success = "";
$error = "";

// Fetch Current Data
$sql = "SELECT u.nama, u.email, u.username, m.no_telepon, m.jabatan 
        FROM Users u 
        JOIN Mentor m ON u.id_user = m.user_id 
        WHERE u.id_user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $no_telepon = $conn->real_escape_string($_POST['no_telepon']);
    $jabatan = $conn->real_escape_string($_POST['jabatan']);
    $new_password = $_POST['new_password'];

    $conn->begin_transaction();

    try {
        // Update Users table
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_u = "UPDATE Users SET nama = ?, email = ?, password = ? WHERE id_user = ?";
            $stmt_u = $conn->prepare($sql_u);
            $stmt_u->bind_param("sssi", $nama, $email, $hashed_password, $user_id);
        } else {
            $sql_u = "UPDATE Users SET nama = ?, email = ? WHERE id_user = ?";
            $stmt_u = $conn->prepare($sql_u);
            $stmt_u->bind_param("ssi", $nama, $email, $user_id);
        }

        if (!$stmt_u->execute()) throw new Exception("Gagal memperbarui data user");

        // Update Mentor table
        $sql_m = "UPDATE Mentor SET no_telepon = ?, jabatan = ? WHERE id_mentor = ?";
        $stmt_m = $conn->prepare($sql_m);
        $stmt_m->bind_param("ssi", $no_telepon, $jabatan, $mentor_id);
        
        if (!$stmt_m->execute()) throw new Exception("Gagal memperbarui data mentor");

        $conn->commit();
        $_SESSION['nama'] = $nama; // Update session name
        $success = "Profil berhasil diperbarui!";
        
        // Refresh data
        $user_data['nama'] = $nama;
        $user_data['email'] = $email;
        $user_data['no_telepon'] = $no_telepon;
        $user_data['jabatan'] = $jabatan;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Profil - MAGIS Mentor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <!-- Sidebar Mentor -->
    <aside class="w-64 bg-slate-900 shadow-xl h-screen fixed left-0 top-0 flex flex-col z-20">
        <div class="h-20 bg-indigo-700 flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" />
            </svg>
            <h1 class="font-extrabold text-xl text-white tracking-wider leading-none">MAGIS MENTOR</h1>
        </div>
        
        <div class="p-4 text-slate-500 text-xs font-bold uppercase tracking-widest mt-4">Menu Utama</div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard-mentor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span class="font-semibold">Dashboard Papan</span>
            </a>
            <a href="mentor-monitoring-presensi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-semibold">Monitoring Presensi</span>
            </a>
        </nav>
        
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition group font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span>Logout Mentor</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Pengaturan Profil</h2>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 shadow-lg shadow-indigo-100 text-white rounded-xl flex items-center justify-center font-black">
                    <?php echo strtoupper(substr($user_data['nama'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="p-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-blue-50 border border-slate-200">
                    <div class="flex items-center gap-3 mb-8 border-b border-slate-50 pb-6">
                         <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                         <h3 class="text-2xl font-black text-slate-800 tracking-tight">Perbarui Informasi Anda</h3>
                    </div>

                    <?php if ($success): ?>
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-5 rounded-2xl mb-8 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <p class="font-bold"><?php echo $success; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-5 rounded-2xl mb-8 flex items-center gap-3">
                        <p class="font-bold"><?php echo $error; ?></p>
                    </div>
                    <?php endif; ?>

                    <form class="space-y-6" method="POST">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <h4 class="text-xs font-black text-indigo-400 uppercase tracking-widest px-1">Informasi Dasar</h4>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nama Lengkap</label>
                                    <input type="text" name="nama" value="<?php echo htmlspecialchars($user_data['nama']); ?>" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Email</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Username (Tidak dapat dirubah)</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 font-bold text-slate-400 outline-none cursor-not-allowed" readonly>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <h4 class="text-xs font-black text-indigo-400 uppercase tracking-widest px-1">Informasi Jabatan & Kontak</h4>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nomor Telepon</label>
                                    <input type="text" name="no_telepon" value="<?php echo htmlspecialchars($user_data['no_telepon']); ?>" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Jabatan / Posisi</label>
                                    <input type="text" name="jabatan" value="<?php echo htmlspecialchars($user_data['jabatan']); ?>" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Password Baru (Kosongkan jika tidak ingin merubah)</label>
                                    <div class="relative">
                                        <input type="password" id="new_password" name="new_password" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" placeholder="••••••••">
                                        <button type="button" onclick="togglePassword('new_password', 'toggle-icon')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition duration-200">
                                            <svg id="toggle-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-10 mt-6 border-t border-slate-50 flex gap-4">
                            <a href="dashboard-mentor.php" class="px-8 py-5 bg-slate-100 text-slate-600 font-black rounded-3xl hover:bg-slate-200 transition duration-200">
                                Kembali
                            </a>
                            <button type="submit" class="flex-1 bg-indigo-600 text-white font-black py-5 rounded-3xl shadow-2xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition transform active:scale-95 duration-200">
                                Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>
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
