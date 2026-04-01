<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard-admin.php");
    } else {
        header("Location: dashboard-mhs.php");
    }
    exit();
}

$error = "";
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid') {
        $error = "Username atau password admin salah.";
    } else if ($_GET['error'] == 'required') {
        $error = "Silakan isi semua bidang.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body class="bg-[#0f172a] flex items-center justify-center min-h-screen relative overflow-hidden text-slate-900">
    <!-- Decorative Elements -->
    <div class="absolute top-[-10%] left-[-5%] w-[40%] h-[40%] bg-blue-600/20 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[120px]"></div>

    <div
        class="glass p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md z-10 transition-all duration-500 hover:shadow-blue-500/10 border-white/20">
        <div class="text-center mb-10">
            <div
                class="w-20 h-20 mx-auto bg-gradient-to-tr from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30 mb-6 group transition-transform hover:rotate-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white"
                    class="w-10 h-10">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>
            <h1 class="font-extrabold text-3xl text-slate-900 tracking-tight">Admin MAGIS</h1>
            <p class="text-slate-500 font-medium mt-2">Pusat Kendali Administrasi</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 mb-6 rounded-2xl text-center text-sm font-bold">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form class="space-y-6" action="login-process.php" method="POST">
            <input type="hidden" name="source" value="admin">
            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 ml-1">Username Admin</label>
                <div class="relative group">
                    <div
                        class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none group-focus-within:text-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <input type="text" name="identifier"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-11 pr-4 py-4 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-900 placeholder:text-slate-400"
                        required placeholder="admin_magis">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 ml-1">Password</label>
                <div class="relative group">
                    <div
                        class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none group-focus-within:text-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <input type="password" id="password_admin" name="password"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-11 pr-12 py-4 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-900 placeholder:text-slate-400"
                        required placeholder="••••••••">
                    <button type="button" onclick="togglePassword('password_admin', 'eyeIconAdmin')" class="absolute right-4 top-4 text-slate-400 hover:text-blue-600 transition-colors">
                        <svg id="eyeIconAdmin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all duration-200 active:scale-95">
                    Login Kendali Admin
                </button>
            </div>
        </form>

        <div class="mt-8 text-center flex flex-col gap-4">
            <p class="text-xs text-slate-400 font-medium italic">"Hanya untuk personel yang berwenang"</p>
            <a href="index.php" class="text-sm font-bold text-blue-400 hover:text-blue-300 transition underline underline-offset-4">Kembali ke Portal Mahasiswa</a>
        </div>
    </div>
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
            }
        }
    </script>
</body>

</html>
