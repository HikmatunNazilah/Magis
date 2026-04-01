<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard-admin.php");
    }
    else if ($_SESSION['role'] === 'mentor') {
        header("Location: dashboard-mentor.php");
    }
    else {
        header("Location: dashboard-mhs.php");
    }
    exit();
}

$error = "";
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid') {
        $error = "Username atau password salah.";
    }
    else if ($_GET['error'] == 'required') {
        $error = "Silakan isi semua bidang.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center min-h-screen font-sans">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-white">
        <div class="text-center mb-8">
            <div class="inline-block bg-blue-700 p-3 rounded-2xl shadow-lg mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-10 h-10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" />
                </svg>
            </div>
            <h1 class="font-extrabold text-3xl text-blue-900 tracking-tight">MAGIS</h1>
            <p class="text-gray-500 text-sm mt-2">Sistem Magang Informasi dan Sistem</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <p class="text-sm"><?php echo $error; ?></p>
        </div>
        <?php
endif; ?>

        <form class="space-y-5" action="login-process.php" method="POST">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Username / Email</label>
                <div class="relative">
                    <input type="text" name="identifier" class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm bg-gray-50/50" required placeholder="Masukkan username">
                    <div class="absolute left-3 top-3.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                </div>
            </div>
                <div class="relative group">
                    <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-lg pl-10 pr-12 py-3 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm bg-gray-50/50" required placeholder="••••••••">
                    <div class="absolute left-3 top-3.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z" />
                        </svg>
                    </div>
                    <button type="button" onclick="togglePassword('password', 'eyeIcon')" class="absolute right-3 top-3 text-gray-400 hover:text-blue-600 transition-colors p-0.5">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-gray-600 cursor-pointer">
                    <input type="checkbox" class="rounded text-blue-600 focus:ring-blue-500 border-gray-300 h-4 w-4">
                    <span>Ingat saya</span>
                </label>
                <button type="button" onclick="showForgetModal()" class="text-blue-600 font-semibold hover:text-blue-700 transition">Lupa Password?</button>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transition transform active:scale-95 duration-200">
                Masuk ke Sistem
            </button>
        </form>
        <p class="text-center text-gray-600 text-sm mt-8">
            Belum punya akun? <a href="register.php" class="text-blue-600 font-bold hover:underline">Registrasi Mandiri</a>
        </p>
    </div>
    <!-- Forgot Password Modal -->
    <div id="forgetModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300 p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Lupa Password</h3>
                    <button onclick="closeForgetModal()" class="text-slate-400 hover:text-slate-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Step 1: Input Email -->
                <div id="forgetStep1" class="space-y-6">
                    <p class="text-sm text-slate-500 font-medium leading-relaxed">Masukkan email terdaftar Anda. Kami akan mengirimkan kode verifikasi 6-digit.</p>
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Email Mahasiswa</label>
                        <input type="email" id="forgetEmail" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm" placeholder="email@kampus.ac.id">
                    </div>
                    <button onclick="requestResetCode()" id="btnForgetStep1" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                        <span>Kirim Kode Verifikasi</span>
                    </button>
                </div>

                <!-- Step 2: Input Code -->
                <div id="forgetStep2" class="hidden space-y-6">
                    <p class="text-sm text-slate-500 font-medium leading-relaxed">Masukkan 6-digit kode yang telah kami kirimkan ke email Anda.</p>
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Kode Verifikasi</label>
                        <input type="text" id="forgetCode" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-center font-black text-2xl tracking-[0.5em] text-blue-600 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" maxlength="6" placeholder="000000">
                    </div>
                    <button onclick="verifyResetCode()" id="btnForgetStep2" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all px-6">
                        Verifikasi Kode
                    </button>
                </div>

                <!-- Step 3: New Password -->
                <div id="forgetStep3" class="hidden space-y-6">
                    <p class="text-sm text-slate-500 font-medium leading-relaxed">Buat password baru yang kuat untuk akun Anda.</p>
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Password Baru</label>
                        <div class="relative">
                            <input type="password" id="newPassword" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm" placeholder="••••••••">
                            <button type="button" onclick="togglePassword('newPassword', 'eyeIconNew')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition duration-200">
                                <svg id="eyeIconNew" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button onclick="resetPasswordNow()" id="btnForgetStep3" class="w-full bg-emerald-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">
                        Perbarui Password
                    </button>
                </div>

                <!-- Error/Success Message -->
                <div id="forgetMsg" class="mt-4 text-center text-sm font-bold hidden"></div>
            </div>
        </div>
    </div>

    <script>
        function showForgetModal() {
            const modal = document.getElementById('forgetModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('div').classList.remove('scale-95');
                modal.querySelector('div').classList.add('scale-100');
            }, 10);
        }

        function closeForgetModal() {
            const modal = document.getElementById('forgetModal');
            modal.classList.add('opacity-0');
            modal.querySelector('div').classList.remove('scale-100');
            modal.querySelector('div').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                // Reset steps
                document.getElementById('forgetStep1').classList.remove('hidden');
                document.getElementById('forgetStep2').classList.add('hidden');
                document.getElementById('forgetStep3').classList.add('hidden');
                document.getElementById('forgetMsg').classList.add('hidden');
            }, 300);
        }

        function requestResetCode() {
            const email = document.getElementById('forgetEmail').value;
            const btn = document.getElementById('btnForgetStep1');
            const msg = document.getElementById('forgetMsg');

            if (!email) return alert('Silakan masukkan email Anda');

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"></span> Mengirim...';
            
            fetch('api/forgot-password-request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}`
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Kirim Kode Verifikasi';
                if (data.success) {
                    document.getElementById('forgetStep1').classList.add('hidden');
                    document.getElementById('forgetStep2').classList.remove('hidden');
                    msg.classList.remove('hidden', 'text-rose-500');
                    msg.classList.add('text-emerald-500');
                    msg.textContent = 'Kode terkirim (Demo: ' + data.code + ')';
                } else {
                    msg.classList.remove('hidden', 'text-emerald-500');
                    msg.classList.add('text-rose-500');
                    msg.textContent = data.message;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Kirim Kode Verifikasi';
                msg.classList.remove('hidden', 'text-emerald-500');
                msg.classList.add('text-rose-500');
                msg.textContent = 'Gagal menghubungkan ke server.';
                console.error(err);
            });
        }

        function verifyResetCode() {
            const email = document.getElementById('forgetEmail').value;
            const code = document.getElementById('forgetCode').value;
            const btn = document.getElementById('btnForgetStep2');
            const msg = document.getElementById('forgetMsg');

            if (code.length < 6) return;

            btn.disabled = true;
            fetch('api/verify-reset-code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&code=${encodeURIComponent(code)}`
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    document.getElementById('forgetStep2').classList.add('hidden');
                    document.getElementById('forgetStep3').classList.remove('hidden');
                    msg.classList.add('hidden');
                } else {
                    msg.classList.remove('hidden', 'text-emerald-500');
                    msg.classList.add('text-rose-500');
                    msg.textContent = 'Kode verifikasi salah.';
                }
            });
        }

        function resetPasswordNow() {
            const email = document.getElementById('forgetEmail').value;
            const password = document.getElementById('newPassword').value;
            const btn = document.getElementById('btnForgetStep3');
            const msg = document.getElementById('forgetMsg');

            if (password.length < 8) return alert('Password minimal 8 karakter');

            btn.disabled = true;
            fetch('api/reset-password-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    msg.classList.remove('hidden', 'text-rose-500');
                    msg.classList.add('text-emerald-500');
                    msg.textContent = 'Password berhasil diubah! Silakan login.';
                    setTimeout(closeForgetModal, 2000);
                } else {
                    msg.classList.remove('hidden', 'text-emerald-500');
                    msg.classList.add('text-rose-500');
                    msg.textContent = 'Terjadi kesalahan.';
                }
            });
        }

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
