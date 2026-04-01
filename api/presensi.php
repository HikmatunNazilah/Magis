<?php
require_once 'config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit();
}

$nama = $_SESSION['nama'];
$email = "";
$user_id = $_SESSION['user_id'];
$m_sql = "SELECT m.*, u.nama, u.email FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id WHERE u.id_user = $user_id";
$m_result = $conn->query($m_sql);
$data = $m_result->fetch_assoc();
$email = $data['email'];
$nama = $data['nama'];
$status = $data['status'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Selfie - MAGIS</title>
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
            <a href="presensi.php" class="flex items-center gap-3 px-3 py-3 rounded-xl bg-blue-50 text-blue-600 border-l-4 border-blue-600 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shadow-inner">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-bold">Presensi</span>
            </a>
            <a href="logbook.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
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

    <main class="ml-64 flex-1 flex flex-col min-h-screen relative pb-20">
        <header class="bg-blue-600 shadow-md h-20 flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-xl font-bold text-white tracking-tight">Presensi Magang</h2>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-white"><?php echo htmlspecialchars($nama); ?></p>
                    <p class="text-xs text-blue-100"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <a href="mhs-settings.php" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-700 font-bold shadow-sm hover:scale-105 transition active:scale-95">
                    <?php echo strtoupper(substr($nama, 0, 1)); ?>
                </a>
            </div>
        </header>

        <div class="p-8 flex-1 flex flex-col items-center">
            
            <div class="bg-white p-8 rounded-3xl shadow-xl shadow-blue-50 border border-slate-200 w-full max-w-xl h-fit mb-24">
                <!-- Clock -->
                <div class="text-center mb-8 bg-blue-50 py-6 rounded-2xl border border-blue-100/50">
                    <p id="currentDate" class="text-blue-400 font-bold uppercase tracking-widest text-xs mb-1"></p>
                    <p id="currentTime" class="text-4xl font-black text-blue-800 tracking-tight leading-none"></p>
                </div>

                <div class="mb-8 group">
                    <div class="relative w-full aspect-square md:aspect-video bg-slate-900 rounded-3xl overflow-hidden flex items-center justify-center border-8 border-slate-100 shadow-2xl transition duration-500 group-hover:border-blue-100">
                        <video id="camera" autoplay playsinline class="w-full h-full object-cover"></video>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" id="cameraPlaceholder">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-8 h-8 border-4 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <p class="text-white text-xs font-bold uppercase tracking-widest">Memulai Kamera...</p>
                            </div>
                        </div>
                        <canvas id="canvas" class="hidden absolute top-0 left-0 w-full h-full object-cover"></canvas>
                        
                        <!-- Overlay Guide -->
                        <div class="absolute inset-0 border-[40px] border-slate-900/40 pointer-events-none rounded-3xl"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button id="btnBerangkat" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-6 rounded-2xl shadow-lg shadow-emerald-100 transition flex items-center justify-center gap-3 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        MASUK
                    </button>
                    <button id="btnPulang" class="bg-rose-500 hover:bg-rose-600 text-white font-black py-4 px-6 rounded-2xl shadow-lg shadow-rose-100 transition flex items-center justify-center gap-3 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        KELUAR
                    </button>
                </div>

                <!-- Status Feedback -->
                <div id="statusMessage" class="hidden mt-8 p-5 rounded-3xl text-center font-black animate-in fade-in slide-in-from-top-4 duration-300">
                </div>
            </div>

        </div>

        <!-- Footer Nav -->
        <div class="fixed bottom-6 left-64 right-0 flex justify-center z-20 pointer-events-none">
            <div class="bg-white/80 backdrop-blur-xl p-2 rounded-3xl border border-slate-200 shadow-2xl flex gap-1 pointer-events-auto">
                <a href="presensi.php" class="bg-blue-600 text-white px-6 py-3 rounded-2xl flex items-center gap-2 font-black shadow-lg shadow-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316Z" />
                    </svg>
                    Presensi
                </a>
                <a href="presensi-history.php" class="text-slate-500 px-6 py-3 rounded-2xl flex items-center gap-2 font-bold hover:bg-slate-50 hover:text-blue-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat
                </a>
            </div>
        </div>
    </main>

    <script>
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const placeholder = document.getElementById('cameraPlaceholder');
        const statusBox = document.getElementById('statusMessage');
        const btnMasuk = document.getElementById('btnBerangkat');
        const btnKeluar = document.getElementById('btnPulang');
        let stream = null;

        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            document.getElementById('currentDate').innerText = now.toLocaleDateString('id-ID', options);
            document.getElementById('currentTime').innerText = now.getHours().toString().padStart(2, '0') + ':' + 
                                                               now.getMinutes().toString().padStart(2, '0') + ':' + 
                                                               now.getSeconds().toString().padStart(2, '0');
        }
        setInterval(updateClock, 1000);
        updateClock();

        async function initCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = stream;
                video.onloadedmetadata = () => placeholder.style.display = 'none';
            } catch (err) {
                placeholder.innerHTML = '<p class="text-rose-500 text-xs font-bold bg-white/90 p-3 rounded-xl shadow-lg">AKSES KAMERA DITOLAK</p>';
            }
        }
        initCamera();

        async function checkStatus() {
            try {
                const response = await fetch('api/get-presensi-status.php');
                const result = await response.json();
                if (result.success) {
                    const data = result.data;
                    if (data.has_masuk) {
                        btnMasuk.disabled = true;
                        btnMasuk.title = "Anda sudah masuk jam " + data.jam_masuk;
                    }
                    if (data.has_keluar) {
                        btnKeluar.disabled = true;
                        btnKeluar.title = "Anda sudah keluar jam " + data.jam_keluar;
                    }
                    
                    if (data.has_masuk && !data.has_keluar) {
                        statusBox.innerHTML = `Anda sudah absen MASUK pukul ${data.jam_masuk}. Jangan lupa absen KELUAR saat pulang.`;
                        statusBox.className = "mt-8 p-5 rounded-3xl text-center font-black bg-blue-50 text-blue-600 block";
                    } else if (data.has_keluar) {
                        statusBox.innerHTML = `Presensi hari ini selesai. (Masuk: ${data.jam_masuk}, Keluar: ${data.jam_keluar})`;
                        statusBox.className = "mt-8 p-5 rounded-3xl text-center font-black bg-emerald-50 text-emerald-600 block";
                    }
                }
            } catch (err) { console.error(err); }
        }

        async function doPresensi(type) {
            if (!stream) { alert("Kamera tidak aktif!"); return; }
            
            // Capture image
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const photo = canvas.toDataURL('image/jpeg');
            
            const btn = type === 'masuk' ? btnMasuk : btnKeluar;
            const originalText = btn.innerHTML;
            btn.innerHTML = "Submitting...";
            btn.disabled = true;

            try {
                const response = await fetch('api/save-presensi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type, photo })
                });
                const result = await response.json();
                
                if (result.success) {
                    statusBox.innerHTML = `✅ BERHASIL ABSEN ${type.toUpperCase()}<br><span class="text-sm font-medium">Pukul ${result.time}</span>`;
                    statusBox.className = `mt-8 p-5 rounded-3xl text-center font-black ${type === 'masuk' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'} block`;
                    checkStatus();
                } else {
                    alert(result.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                alert("Kesalahan jaringan.");
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        btnMasuk.onclick = () => doPresensi('masuk');
        btnKeluar.onclick = () => doPresensi('keluar');
        
        checkStatus();
    </script>
</body>
</html>
