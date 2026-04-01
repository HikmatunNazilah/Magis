<?php
require_once 'config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$sql = "SELECT m.*, u.nama, u.email FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id WHERE u.id_user = $user_id";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

// If data is null (rare case), redirect
if (!$data) {
    header("Location: logout.php");
    exit();
}

$status = $data['status']; // 'pending', 'aktif', 'ditolak'

// Fetch Mentor name if exists
$mentor_name = "Belum ditentukan";
if ($data['mentor_id']) {
    $m_id = $data['mentor_id'];
    $mentor_sql = "SELECT u.nama FROM Mentor mt JOIN Users u ON u.id_user = mt.user_id WHERE mt.id_mentor = $m_id";
    $m_result = $conn->query($mentor_sql);
    if ($m_result && $m_result->num_rows > 0) {
        $mentor_data = $m_result->fetch_assoc();
        $mentor_name = $mentor_data['nama'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda Mahasiswa - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .status-circle { transition: all 0.5s ease; }
        .step-active { border-color: #2563eb; background-color: #eff6ff; color: #1d4ed8; }
        .step-complete { border-color: #10b981; background-color: #ecfdf5; color: #047857; }
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
            <a href="dashboard-mhs.php" class="flex items-center gap-3 px-3 py-3 rounded-xl bg-blue-50 text-blue-600 border-l-4 border-blue-600 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span class="font-bold">Beranda</span>
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
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Beranda</h2>
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
        <div class="p-8 flex-1 relative">
            
            <!-- Step Progress Indicator -->
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm mb-8">
                <div class="flex items-center justify-between max-w-2xl mx-auto relative px-4">
                    <!-- Progress Line -->
                    <div class="absolute left-12 right-12 top-1/2 h-1 bg-slate-100 -translate-y-1/2 -z-10">
                        <div class="h-full bg-blue-600 transition-all duration-1000" style="width: <?php echo ($status === 'aktif' ? '100' : ($status === 'pending' ? '50' : '50')); ?>%"></div>
                    </div>

                    <!-- Step 1 -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center font-black step-complete shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pendaftaran</span>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center font-black <?php echo ($status === 'pending' || $status === 'aktif' ? 'step-active' : 'border-slate-200 bg-white text-slate-300'); ?> shadow-sm">
                            <?php if($status === 'aktif'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <?php else: ?>
                                2
                            <?php endif; ?>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest <?php echo ($status === 'pending' ? 'text-blue-600' : 'text-slate-400'); ?>">Verifikasi Admin</span>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center gap-2">
                        <?php 
                        $step3_class = "border-slate-200 bg-white text-slate-300";
                        if($status === 'aktif') $step3_class = "step-complete";
                        if($status === 'ditolak') $step3_class = "border-rose-500 bg-rose-50 text-rose-600";
                        ?>
                        <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center font-black <?php echo $step3_class; ?> shadow-sm">
                            <?php if($status === 'aktif'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <?php elseif($status === 'ditolak'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            <?php else: ?>
                                3
                            <?php endif; ?>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest <?php echo ($status === 'aktif' ? 'text-emerald-600' : ($status === 'ditolak' ? 'text-rose-600' : 'text-slate-400')); ?>">Keputusan</span>
                    </div>
                </div>
            </div>

            <!-- Stats & Welcome Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Welcome Card -->
                <div class="md:col-span-2 bg-blue-700 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-blue-100">
                    <div class="relative z-10">
                        <h3 class="text-4xl font-black mb-3">Halo, <?php echo explode(' ', $data['nama'])[0]; ?>! 👋</h3>
                        <p class="text-blue-100 font-medium text-lg max-w-md leading-relaxed opacity-90">
                            <?php 
                            if($status === 'pending') echo "Pendaftaran Anda sedang menunggu proses verifikasi oleh tim Admin MAGIS.";
                            elseif($status === 'aktif') echo "Selamat! Anda resmi menjadi bagian dari program magang MAGIS.";
                            elseif($status === 'ditolak') echo "Maaf, pendaftaran Anda belum dapat kami setujui saat ini.";
                            else echo "Lengkapi pendaftaran Anda di menu 'Daftar' untuk memulai program.";
                            ?>
                        </p>
                        
                        <?php if($status === 'aktif'): ?>
                            <div class="mt-10 flex gap-4">
                                <a href="logbook.php" class="inline-flex items-center gap-3 bg-white text-blue-700 px-8 py-4 rounded-2xl font-black shadow-xl hover:bg-blue-50 transition active:scale-95 group">
                                    Mulai Aktivitas
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 group-hover:translate-x-1 transition">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        <?php elseif($status === 'pending'): ?>
                             <div class="mt-10 p-5 bg-blue-600/30 rounded-2xl border border-blue-400/20 inline-flex items-center gap-3">
                                 <div class="w-3 h-3 bg-blue-300 rounded-full animate-pulse"></div>
                                 <span class="text-sm font-bold text-blue-50">Menunggu Validasi Admin...</span>
                             </div>
                        <?php elseif($status === 'ditolak'): ?>
                             <div class="mt-10 p-5 bg-rose-600/30 rounded-2xl border border-rose-400/20 inline-flex items-center gap-3">
                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                 <span class="text-sm font-bold text-white">Hubungi Admin untuk informasi lebih lanjut.</span>
                             </div>
                        <?php endif; ?>
                    </div>
                    <div class="absolute right-[-40px] top-[-40px] bg-white/10 w-80 h-80 rounded-full blur-[80px]"></div>
                </div>

                <!-- Status Detail Card -->
                <div class="bg-white rounded-[2.5rem] p-10 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Status Saat Ini</p>
                        <?php 
                        $status_class = "bg-slate-100 text-slate-400"; // default
                        $status_label = "BELUM DAFTAR";
                        if($status === 'aktif') { $status_class = "bg-emerald-100 text-emerald-700"; $status_label = "AKTIF"; }
                        elseif($status === 'pending') { $status_class = "bg-blue-100 text-blue-700"; $status_label = "PENDING"; }
                        elseif($status === 'ditolak') { $status_class = "bg-rose-100 text-rose-700"; $status_label = "DITOLAK"; }
                        ?>
                        <span class="inline-block <?php echo $status_class; ?> font-black px-8 py-3 rounded-2xl text-xl shadow-sm tracking-tight text-center w-full">
                            <?php echo $status_label; ?>
                        </span>
                        
                        <div class="mt-8 space-y-4">
                            <?php if($status === 'pending'): ?>
                                <p class="text-xs text-slate-500 font-medium leading-relaxed italic border-l-2 border-blue-200 pl-4">Berkas Anda telah kami terima dan sedang antre untuk divalidasi oleh Verifikator Admin.</p>
                            <?php elseif($status === 'ditolak'): ?>
                                <p class="text-xs text-rose-500 font-medium leading-relaxed italic border-l-2 border-rose-200 pl-4">Pendaftaran tidak dapat dilanjutkan. Silakan ajukan ulang atau hubungi tim support MAGIS.</p>
                            <?php elseif($status === 'aktif'): ?>
                                <p class="text-xs text-emerald-600 font-medium leading-relaxed italic border-l-2 border-emerald-200 pl-4">Semua akses fitur telah dibuka. Anda dapat mulai melakukan Presensi dan mengisi Logbook.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-slate-50">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Mentor Pembimbing</p>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            </div>
                            <p class="text-slate-800 font-black text-sm"><?php echo htmlspecialchars($mentor_name); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Features -->
            <h4 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                Menu Magang
                <span class="h-1 flex-1 bg-slate-100 rounded-full"></span>
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Presensi -->
                <?php if($status === 'aktif'): ?>
                <a href="presensi.php" class="bg-white p-8 rounded-[2rem] border border-slate-100 hover:border-blue-400 hover:shadow-2xl hover:shadow-blue-50/50 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316Z" />
                        </svg>
                    </div>
                    <h5 class="font-black text-slate-800 text-lg mb-2">Presensi Selfie</h5>
                    <p class="text-[13px] text-slate-400 font-medium leading-relaxed italic">Catat kehadiran Anda hari ini melalui kamera.</p>
                </a>
                <?php else: ?>
                <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100 opacity-60 relative overflow-hidden group">
                    <div class="w-14 h-14 bg-slate-200 text-slate-400 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </div>
                    <h5 class="font-black text-slate-400 text-lg mb-2">Presensi Selfie</h5>
                    <p class="text-[13px] text-slate-400 font-medium">Fitur ini dikunci hingga pendaftaran Anda Aktif.</p>
                    <div class="absolute inset-0 bg-white/40 backdrop-blur-[1px] invisible group-hover:visible flex items-center justify-center">
                         <span class="bg-slate-800 text-white text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-widest shadow-xl">Menunggu Validasi</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Logbook -->
                <?php if($status === 'aktif'): ?>
                <a href="logbook.php" class="bg-white p-8 rounded-[2rem] border border-slate-100 hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-50/50 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <h5 class="font-black text-slate-800 text-lg mb-2">Isi Logbook</h5>
                    <p class="text-[13px] text-slate-400 font-medium leading-relaxed italic">Laporkan progres kegiatan harian Anda.</p>
                </a>
                <?php else: ?>
                <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100 opacity-60 relative overflow-hidden group">
                    <div class="w-14 h-14 bg-slate-200 text-slate-400 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </div>
                    <h5 class="font-black text-slate-400 text-lg mb-2">Isi Logbook</h5>
                    <p class="text-[13px] text-slate-400 font-medium leading-relaxed italic">Fitur ini dibuka setelah akun divalidasi.</p>
                    <div class="absolute inset-0 bg-white/40 backdrop-blur-[1px] invisible group-hover:visible flex items-center justify-center">
                         <span class="bg-slate-800 text-white text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-widest shadow-xl">Menunggu Validasi</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sertifikat -->
                <a href="mhs-sertifikat.php" class="bg-white p-8 rounded-[2rem] border border-slate-100 hover:border-emerald-400 hover:shadow-2xl hover:shadow-emerald-50/50 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <h5 class="font-black text-slate-800 text-lg mb-2">E-Sertifikat</h5>
                    <p class="text-[13px] text-slate-400 font-medium leading-relaxed italic">Unduh setelah sesi magang berakhir.</p>
                </a>

                <!-- Profil -->
                <a href="mhs-settings.php" class="bg-white p-8 rounded-[2rem] border border-slate-100 hover:border-slate-400 hover:shadow-2xl hover:shadow-slate-50 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h5 class="font-black text-slate-800 text-lg mb-2">Pengaturan Akun</h5>
                    <p class="text-[13px] text-slate-400 font-medium italic">Kelola profil dan password Anda.</p>
                </a>
            </div>
        </div>
    </main>
</body>
</html>
