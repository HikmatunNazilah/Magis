<?php
require_once 'config.php';

$error = "";
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'exists') {
        $error = "Email sudah terdaftar. Silakan gunakan email lain.";
    } else if ($_GET['error'] == 'failed') {
        $error = "Pendaftaran gagal. Silakan coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center min-h-screen font-sans p-4">
    <div class="bg-white p-10 rounded-3xl shadow-xl w-full max-w-lg border border-white my-8">
        <div class="text-center mb-10">
            <div class="inline-block bg-blue-100 p-3 rounded-2xl mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0zM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
            </div>
            <h1 class="font-extrabold text-3xl text-blue-900 tracking-tight">Daftar Akun Mahasiswa</h1>
            <p class="text-gray-500 text-sm mt-2">Lengkapi data diri Anda untuk memulai magang</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form class="space-y-5" action="register-process.php" method="POST">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Depan</label>
                    <input type="text" name="first_name" class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition shadow-sm bg-gray-50/50" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Belakang</label>
                    <input type="text" name="last_name" class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition shadow-sm bg-gray-50/50" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email Mahasiswa</label>
                <input type="email" name="email" class="w-full border border-gray-300 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition shadow-sm bg-gray-50/50" required placeholder="email@kampus.ac.id">
            </div>
            <div class="space-y-1">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                <div class="relative">
                    <input type="password" id="password_reg" name="password" class="w-full border border-gray-300 rounded-xl px-4 pr-12 py-3 outline-none focus:ring-2 focus:ring-blue-500 transition shadow-sm bg-gray-50/50" required minlength="8" placeholder="Minimal 8 karakter">
                    <button type="button" onclick="togglePassword('password_reg', 'eyeIconReg')" class="absolute right-4 top-3 text-gray-400 hover:text-blue-600 transition-colors">
                        <svg id="eyeIconReg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-xl shadow-lg shadow-blue-100 hover:bg-blue-700 hover:shadow-xl transition transform active:scale-95 duration-200 mt-2">
                Daftar & Masuk Sistem
            </button>
        </form>
        <p class="text-center text-gray-600 text-sm mt-8 font-medium">
            Sudah punya akun? <a href="index.php" class="text-blue-600 font-bold hover:underline">Masuk di sini</a>
        </p>
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
