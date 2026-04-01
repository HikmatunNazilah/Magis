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
$mahasiswa_id = $_SESSION['mahasiswa_id'];

$m_sql = "SELECT m.*, u.nama, u.email FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id WHERE m.id_mahasiswa = $mahasiswa_id";
$m_result = $conn->query($m_sql);
$data = $m_result->fetch_assoc();
$email = $data['email'];
$nama = $data['nama'];

// Fetch Assessment
$sql_pen = "SELECT * FROM penilaian WHERE mahasiswa_id = $mahasiswa_id";
$res_pen = $conn->query($sql_pen);
$penilaian = $res_pen->fetch_assoc();

// Fetch Certificate
$sql_ser = "SELECT * FROM sertifikat WHERE mahasiswa_id = $mahasiswa_id";
$res_ser = $conn->query($sql_ser);
$sertifikat = $res_ser->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Sertifikat - MAGIS</title>
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
            <a href="presensi.php" class="flex items-center gap-3 px-3 py-3 rounded-xl <?php echo ($data['status'] !== 'aktif' ? 'opacity-40 cursor-not-allowed' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-semibold">Presensi</span>
            </a>
            <a href="logbook.php" class="flex items-center gap-3 px-3 py-3 rounded-xl <?php echo ($data['status'] !== 'aktif' ? 'opacity-40 cursor-not-allowed' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <span class="font-semibold">Logbook</span>
            </a>
            <a href="mhs-sertifikat.php" class="flex items-center gap-3 px-3 py-3 rounded-xl bg-blue-50 text-blue-600 border-l-4 border-blue-600 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shadow-inner">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <span class="font-bold">E-Sertifikat</span>
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
            <h2 class="text-xl font-bold text-white tracking-tight leading-none">E-Sertifikat Magang</h2>
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

        <div class="p-8 flex-1 overflow-y-auto max-w-5xl mx-auto w-full">
            <?php if ($penilaian): ?>
                <!-- Assessment Score Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-12">
                     <div class="md:col-span-3 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm flex flex-col justify-center">
                        <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                             <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                             Detail Penilaian Kinerja
                        </h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Disiplin</p>
                                <p class="text-2xl font-black text-slate-800"><?php echo $penilaian['nilai_disiplin']; ?></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Analisis</p>
                                <p class="text-2xl font-black text-slate-800"><?php echo $penilaian['nilai_analisis']; ?></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Komunikasi</p>
                                <p class="text-2xl font-black text-slate-800"><?php echo $penilaian['nilai_komunikasi']; ?></p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kerjasama</p>
                                <p class="text-2xl font-black text-slate-800"><?php echo $penilaian['nilai_kerjasama']; ?></p>
                            </div>
                        </div>
                     </div>
                     <div class="md:col-span-2 bg-indigo-600 p-8 rounded-[2.5rem] shadow-xl shadow-indigo-100 text-white flex flex-col items-center justify-center text-center">
                        <p class="text-xs font-black text-indigo-200 uppercase tracking-widest mb-2">Nilai Akhir Rata-rata</p>
                        <h4 class="text-7xl font-black mb-2"><?php echo floatval($penilaian['nilai_akhir']); ?></h4>
                        <?php 
                        $predikat = "Cukup";
                        if($penilaian['nilai_akhir'] >= 85) $predikat = "Istimewa";
                        else if($penilaian['nilai_akhir'] >= 75) $predikat = "Sangat Baik";
                        else if($penilaian['nilai_akhir'] >= 65) $predikat = "Baik";
                        ?>
                        <p class="text-lg font-bold text-indigo-100 italic">"<?php echo $predikat; ?>"</p>
                     </div>
                </div>

                <!-- Certificate Section -->
                <?php if ($sertifikat): ?>
                    <div class="bg-white p-12 rounded-[3.5rem] border border-slate-200 shadow-2xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-transparent"></div>
                        <div class="relative z-10 flex flex-col items-center text-center">
                            <div class="w-24 h-24 bg-blue-100 text-blue-600 rounded-3xl flex items-center justify-center mb-6 shadow-inner group-hover:scale-110 transition duration-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                </svg>
                            </div>
                            <h3 class="text-3xl font-black text-slate-900 mb-2">Sertifikat Anda Telah Terbit!</h3>
                            <p class="text-slate-500 font-bold mb-8">Selamat! Anda telah berhasil menyelesaikan program magang di MAGIS. Silakan simpan sertifikat digital Anda.</p>
                            
                            <div class="grid grid-cols-2 gap-8 w-full max-w-lg mb-12">
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 text-left">
                                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nomor Sertifikat</p>
                                     <p class="font-black text-slate-800"><?php echo htmlspecialchars($sertifikat['nomor_sertifikat']); ?></p>
                                </div>
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 text-left">
                                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal Terbit</p>
                                     <p class="font-black text-slate-800"><?php echo date('d F Y', strtotime($sertifikat['tanggal_terbit'])); ?></p>
                                </div>
                            </div>

                            <a href="generate-sertifikat.php?id=<?php echo $sertifikat['id_sertifikat']; ?>" target="_blank" class="inline-flex items-center gap-3 px-10 py-5 bg-blue-600 text-white font-black rounded-3xl shadow-2xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition transform active:scale-95 duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Unduh Sertifikat (PDF)
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-amber-50 border-2 border-amber-200 p-10 rounded-[3rem] text-center flex flex-col items-center">
                        <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-3xl flex items-center justify-center mb-6 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-amber-900 mb-2">Proses Penerbitan Sertifikat</h3>
                        <p class="text-amber-700 font-bold max-w-md mx-auto">Nilai Anda sudah keluar, namun sertifikat sedang dalam proses validasi akhir oleh Admin. Silakan cek kembali dalam beberapa waktu.</p>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- No data yet -->
                <div class="bg-white p-20 rounded-[3rem] border border-slate-200 shadow-sm text-center flex flex-col items-center">
                    <div class="w-24 h-24 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mb-8 border-4 border-slate-100 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800 mb-4 tracking-tight">Belum Ada Penilaian</h3>
                    <p class="text-slate-400 font-medium max-w-sm mx-auto leading-relaxed">Mentor Anda belum memberikan penilaian akhir kinerja. Sertifikat akan tersedia setelah seluruh proses evaluasi selesai dilakukan oleh Mentor dan divalidasi oleh Admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
