<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login-admin.php"); exit(); }
$nama_admin = $_SESSION['nama'];
$active_page = 'dashboard';

// ─── Summary Queries ───
// Total semua Mahasiswa
$total_all = $conn->query("SELECT COUNT(*) as t FROM Mahasiswa")->fetch_assoc()['t'];

// By status
$total_pending  = $conn->query("SELECT COUNT(*) as t FROM Mahasiswa WHERE status = 'pending'")->fetch_assoc()['t'];
$total_aktif    = $conn->query("SELECT COUNT(*) as t FROM Mahasiswa WHERE status = 'aktif'")->fetch_assoc()['t'];
$total_ditolak  = $conn->query("SELECT COUNT(*) as t FROM Mahasiswa WHERE status = 'ditolak'")->fetch_assoc()['t'];
$total_selesai  = $conn->query("SELECT COUNT(*) as t FROM Mahasiswa WHERE status = 'selesai'")->fetch_assoc()['t'];

// Total mentor
$total_mentor = $conn->query("SELECT COUNT(*) as t FROM Mentor")->fetch_assoc()['t'];

// Total sertifikat terbit
$total_sertifikat = $conn->query("SELECT COUNT(*) as t FROM Sertifikat")->fetch_assoc()['t'];

// Total logbook entries
$total_logbook = $conn->query("SELECT COUNT(*) as t FROM Logbook")->fetch_assoc()['t'];

// Total presensi entries
$total_presensi = $conn->query("SELECT COUNT(*) as t FROM Presensi")->fetch_assoc()['t'];

// Universitas terbanyak
$res_univ = $conn->query("SELECT universitas, COUNT(*) as jumlah FROM Mahasiswa GROUP BY universitas ORDER BY jumlah DESC LIMIT 3");
$top_universities = [];
while ($r = $res_univ->fetch_assoc()) { $top_universities[] = $r; }

// Active Period
$periode = $conn->query("SELECT * FROM Periode_Magang ORDER BY id_periode DESC LIMIT 1")->fetch_assoc();
$nama_periode = $periode ? $periode['nama_periode'] : "Tidak ada periode aktif";

// Recent Registrations
$res_recent = $conn->query("SELECT u.nama, m.universitas, m.status, m.nim FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id ORDER BY m.id_Mahasiswa DESC LIMIT 5");

// Logbook belum divalidasi
$logbook_pending = $conn->query("SELECT COUNT(*) as t FROM Logbook WHERE status_validasi = 'pending'")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }
    @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-count { animation: countUp 0.5s ease-out forwards; }
    </style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <?php include '_sidebar_admin.php'; ?>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Ringkasan Sistem</h2>
                <p class="text-xs text-slate-500 font-medium tracking-wide">Selamat datang, <?php echo htmlspecialchars($nama_admin); ?></p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-400"><?php echo date('d M Y, H:i'); ?></span>
                <div class="w-10 h-10 bg-indigo-600 shadow-lg shadow-indigo-100 text-white rounded-xl flex items-center justify-center font-black"><?php echo strtoupper(substr($nama_admin, 0, 1)); ?></div>
            </div>
        </header>

        <div class="p-8 space-y-8">
            <!-- ═══ Row 1: Main Stats ═══ -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <!-- Total Pendaftar -->
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-blue-50/50 transition duration-300 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl group-hover:scale-110 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.97 5.97 0 0 0-.942 3.197M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z" /></svg></div>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Total</span>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tight leading-none animate-count"><?php echo $total_all; ?></h3>
                    <p class="text-sm font-bold text-slate-500 mt-2">Total Pendaftar</p>
                </div>

                <!-- Mahasiswa Aktif -->
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-emerald-50/50 transition duration-300 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl group-hover:scale-110 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Aktif</span>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tight leading-none animate-count"><?php echo $total_aktif; ?></h3>
                    <p class="text-sm font-bold text-slate-500 mt-2">Mahasiswa Aktif</p>
                </div>

                <!-- Magang Selesai -->
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-sky-50/50 transition duration-300 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-sky-50 text-sky-600 rounded-2xl group-hover:scale-110 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" /></svg></div>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Selesai</span>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tight leading-none animate-count"><?php echo $total_selesai; ?></h3>
                    <p class="text-sm font-bold text-slate-500 mt-2">Magang Selesai</p>
                </div>

                <!-- Periode -->
                <div class="bg-indigo-600 p-6 rounded-[2rem] shadow-xl shadow-indigo-100 text-white group hover:bg-indigo-700 transition duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 text-white rounded-2xl group-hover:scale-110 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg></div>
                        <span class="text-[10px] font-black text-white/50 uppercase tracking-widest">Waktu</span>
                    </div>
                    <?php $range = $periode ? date('d M', strtotime($periode['tanggal_mulai'])).' - '.date('d M Y', strtotime($periode['tanggal_selesai'])) : 'N/A'; ?>
                    <h3 class="text-xl font-black tracking-tight leading-tight text-white animate-count"><?php echo $range; ?></h3>
                    <p class="text-sm font-bold text-indigo-100 mt-2">Periode Berjalan</p>
                </div>
            </div>

            <!-- ═══ Row 2: Secondary Stats (smaller) ═══ -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white px-5 py-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-2.5 bg-yellow-50 text-yellow-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
                    <div>
                        <p class="text-2xl font-black text-slate-900 leading-none"><?php echo $total_pending; ?></p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Pending</p>
                    </div>
                </div>
                <div class="bg-white px-5 py-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-2.5 bg-rose-50 text-rose-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
                    <div>
                        <p class="text-2xl font-black text-slate-900 leading-none"><?php echo $total_ditolak; ?></p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Ditolak</p>
                    </div>
                </div>
                <div class="bg-white px-5 py-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg></div>
                    <div>
                        <p class="text-2xl font-black text-slate-900 leading-none"><?php echo $total_mentor; ?></p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Mentor</p>
                    </div>
                </div>
                <div class="bg-white px-5 py-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-2.5 bg-teal-50 text-teal-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg></div>
                    <div>
                        <p class="text-2xl font-black text-slate-900 leading-none"><?php echo $total_sertifikat; ?></p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Sertifikat</p>
                    </div>
                </div>
                <div class="bg-white px-5 py-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg></div>
                    <div>
                        <p class="text-2xl font-black text-slate-900 leading-none"><?php echo $logbook_pending; ?></p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Logbook Pending</p>
                    </div>
                </div>
            </div>

            <!-- ═══ Row 3: Detailed Cards ═══ -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Col 1: Active Period -->
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                        Detail Periode Aktif
                    </h3>
                    <div class="space-y-5">
                        <div class="p-5 bg-slate-50 rounded-3xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Nama Program</p>
                            <p class="font-black text-slate-800 text-lg leading-tight"><?php echo htmlspecialchars($nama_periode); ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-wider mb-1">Mulai</p>
                                <p class="font-black text-slate-800 text-sm"><?php echo $periode ? date('d M Y', strtotime($periode['tanggal_mulai'])) : '-'; ?></p>
                            </div>
                            <div class="p-4 bg-rose-50/50 rounded-2xl border border-rose-100">
                                <p class="text-[10px] font-black text-rose-500 uppercase tracking-wider mb-1">Selesai</p>
                                <p class="font-black text-slate-800 text-sm"><?php echo $periode ? date('d M Y', strtotime($periode['tanggal_selesai'])) : '-'; ?></p>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-black text-slate-600">Kuota Terpakai</span>
                                <span class="font-black text-indigo-600"><?php echo $total_aktif; ?> / <?php echo $periode ? $periode['kuota'] : '0'; ?></span>
                            </div>
                            <?php $pct = ($periode && $periode['kuota'] > 0) ? ($total_aktif / $periode['kuota']) * 100 : 0; ?>
                            <div class="w-full bg-slate-100 h-4 rounded-full overflow-hidden p-1 shadow-inner">
                                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-full rounded-full transition-all duration-1000" style="width: <?php echo min($pct, 100); ?>%"></div>
                            </div>
                            <p class="text-[10px] text-slate-400 font-bold mt-1.5 text-right"><?php echo round($pct); ?>% terisi</p>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Top Universities -->
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                    <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-amber-500 rounded-full"></span>
                        Universitas Terbanyak
                    </h3>
                    <div class="space-y-4">
                        <?php if (count($top_universities) > 0): ?>
                            <?php foreach ($top_universities as $idx => $univ):
                                $colors = [
                                    ['bg-indigo-100', 'text-indigo-700', 'bg-indigo-600'],
                                    ['bg-emerald-100', 'text-emerald-700', 'bg-emerald-500'],
                                    ['bg-amber-100', 'text-amber-700', 'bg-amber-500']
                                ];
                                $c = $colors[$idx] ?? $colors[2];
                                $bar_pct = $total_all > 0 ? ($univ['jumlah'] / $total_all) * 100 : 0;
                            ?>
                            <div class="p-4 rounded-2xl border border-slate-100 hover:shadow-md transition">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 <?php echo $c[0]; ?> <?php echo $c[1]; ?> rounded-xl flex items-center justify-center font-black text-sm"><?php echo $idx + 1; ?></div>
                                        <p class="font-black text-slate-800 text-sm"><?php echo htmlspecialchars($univ['universitas'] ?? 'Tidak diketahui'); ?></p>
                                    </div>
                                    <span class="text-lg font-black text-slate-700"><?php echo $univ['jumlah']; ?></span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="<?php echo $c[2]; ?> h-full rounded-full" style="width: <?php echo $bar_pct; ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-slate-400 italic font-medium py-8">Belum ada data universitas.</p>
                        <?php endif; ?>

                        <!-- Activity Summary -->
                        <div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Ringkasan Aktivitas</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <span class="text-xs font-bold text-slate-600"><?php echo $total_presensi; ?> Presensi</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                    <span class="text-xs font-bold text-slate-600"><?php echo $total_logbook; ?> Logbook</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Col 3: Recent Registrations -->
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                            Pendaftar Terbaru
                        </h3>
                        <a href="admin-pendaftaran.php" class="text-sm font-black text-indigo-600 hover:text-indigo-800 transition group flex items-center gap-1">
                            Semua
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 group-hover:translate-x-1 transition"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </a>
                    </div>
                    <div class="flex-1 space-y-2">
                        <?php if ($res_recent && $res_recent->num_rows > 0): ?>
                            <?php while($row = $res_recent->fetch_assoc()): ?>
                            <div class="flex items-center gap-4 p-3 hover:bg-slate-50 transition rounded-2xl border border-transparent hover:border-slate-100">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-black text-slate-400 text-sm"><?php echo strtoupper(substr($row['nama'], 0, 1)); ?></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-slate-800 leading-tight truncate"><?php echo htmlspecialchars($row['nama']); ?></p>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight truncate"><?php echo htmlspecialchars($row['universitas']); ?></p>
                                </div>
                                <?php $badge = "bg-yellow-100 text-yellow-700"; if($row['status'] === 'aktif') $badge = "bg-emerald-100 text-emerald-700"; if($row['status'] === 'ditolak') $badge = "bg-red-100 text-red-700"; if($row['status'] === 'selesai') $badge = "bg-blue-100 text-blue-700"; ?>
                                <span class="text-[9px] font-black px-2.5 py-1 <?php echo $badge; ?> rounded-lg uppercase tracking-widest whitespace-nowrap"><?php echo $row['status']; ?></span>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="h-full flex flex-col items-center justify-center text-slate-400 italic font-medium py-10">
                                <p>Belum ada pendaftar baru.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
