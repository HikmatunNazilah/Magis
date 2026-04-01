<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login-admin.php"); exit(); }
$nama_admin = $_SESSION['nama'];
$active_page = 'rekap-presensi';

// ─── Filters ───
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$filter_periode = isset($_GET['periode']) ? $_GET['periode'] : '';
$filter_universitas = isset($_GET['universitas']) ? $_GET['universitas'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Get all periode for dropdown
$periodes = [];
$res_periode = $conn->query("SELECT * FROM periode_magang ORDER BY tanggal_mulai DESC");
while ($rp = $res_periode->fetch_assoc()) { $periodes[] = $rp; }

// Get all universitas for dropdown
$universitas_list = [];
$res_univ = $conn->query("SELECT DISTINCT universitas FROM Mahasiswa WHERE universitas IS NOT NULL AND universitas != '' ORDER BY universitas ASC");
while ($ru = $res_univ->fetch_assoc()) { $universitas_list[] = $ru['universitas']; }

// ─── Build WHERE clause ───
$where = [];
$se = $conn->real_escape_string($search);
if ($search !== '') { $where[] = "(u.nama LIKE '%$se%' OR m.nim LIKE '%$se%')"; }
if ($filter_universitas !== '') { $where[] = "m.universitas = '".$conn->real_escape_string($filter_universitas)."'"; }
if ($filter_date_from !== '') { $where[] = "p.tanggal >= '".$conn->real_escape_string($filter_date_from)."'"; }
if ($filter_date_to !== '') { $where[] = "p.tanggal <= '".$conn->real_escape_string($filter_date_to)."'"; }
if ($filter_periode !== '') { $where[] = "m.periode_id = ".(int)$filter_periode; }
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// ─── Main Query: Rekapitulasi per mahasiswa ───
// This groups attendance by student and calculates totals
$count_sql = "SELECT COUNT(DISTINCT m.id_mahasiswa) as total 
    FROM Mahasiswa m 
    JOIN Users u ON m.user_id = u.id_user 
    LEFT JOIN Presensi p ON p.mahasiswa_id = m.id_mahasiswa 
    $where_sql";
$total_rows_result = $conn->query($count_sql);
$total_rows = $total_rows_result ? $total_rows_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, ceil($total_rows / $per_page));

// Recap data query
$rekap_sql = "SELECT 
    m.id_mahasiswa,
    m.nim,
    u.nama as nama_mhs,
    m.universitas,
    m.jurusan,
    m.status,
    COUNT(p.id_presensi) as total_hadir,
    COUNT(CASE WHEN p.jam_masuk IS NOT NULL AND p.jam_keluar IS NOT NULL THEN 1 END) as total_lengkap,
    COUNT(CASE WHEN p.jam_masuk IS NOT NULL AND p.jam_keluar IS NULL THEN 1 END) as total_belum_keluar,
    MIN(p.tanggal) as first_attendance,
    MAX(p.tanggal) as last_attendance,
    SEC_TO_TIME(SUM(CASE WHEN p.jam_masuk IS NOT NULL AND p.jam_keluar IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(p.jam_keluar, p.jam_masuk)) ELSE 0 END)) as total_jam_kerja,
    SEC_TO_TIME(AVG(CASE WHEN p.jam_masuk IS NOT NULL AND p.jam_keluar IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(p.jam_keluar, p.jam_masuk)) ELSE NULL END)) as avg_jam_kerja,
    TIME_FORMAT(SEC_TO_TIME(AVG(CASE WHEN p.jam_masuk IS NOT NULL THEN TIME_TO_SEC(p.jam_masuk) ELSE NULL END)), '%H:%i') as avg_jam_masuk,
    TIME_FORMAT(SEC_TO_TIME(AVG(CASE WHEN p.jam_keluar IS NOT NULL THEN TIME_TO_SEC(p.jam_keluar) ELSE NULL END)), '%H:%i') as avg_jam_keluar
FROM Mahasiswa m 
JOIN Users u ON m.user_id = u.id_user 
LEFT JOIN Presensi p ON p.mahasiswa_id = m.id_mahasiswa 
$where_sql
GROUP BY m.id_mahasiswa, m.nim, u.nama, m.universitas, m.jurusan, m.status
ORDER BY u.nama ASC
LIMIT $per_page OFFSET $offset";
$result = $conn->query($rekap_sql);

// ─── Summary Statistics ───
$summary_sql = "SELECT 
    COUNT(DISTINCT m.id_mahasiswa) as total_mhs,
    COUNT(p.id_presensi) as total_presensi,
    SEC_TO_TIME(SUM(CASE WHEN p.jam_masuk IS NOT NULL AND p.jam_keluar IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(p.jam_keluar, p.jam_masuk)) ELSE 0 END)) as grand_total_jam,
    SEC_TO_TIME(AVG(CASE WHEN p.jam_masuk IS NOT NULL AND p.jam_keluar IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(p.jam_keluar, p.jam_masuk)) ELSE NULL END)) as grand_avg_jam
FROM Mahasiswa m 
JOIN Users u ON m.user_id = u.id_user 
LEFT JOIN Presensi p ON p.mahasiswa_id = m.id_mahasiswa 
$where_sql";
$summary = $conn->query($summary_sql)->fetch_assoc();

// ─── Detail Harian Query (untuk view per mahasiswa) ───
$detail_mhs_id = isset($_GET['detail']) ? (int)$_GET['detail'] : 0;
$detail_data = [];
$detail_mhs_info = null;
if ($detail_mhs_id > 0) {
    // Get student info
    $info_sql = "SELECT m.*, u.nama FROM Mahasiswa m JOIN Users u ON m.user_id = u.id_user WHERE m.id_mahasiswa = $detail_mhs_id";
    $detail_mhs_info = $conn->query($info_sql)->fetch_assoc();
    
    // Get daily attendance
    $detail_where = [];
    if ($filter_date_from !== '') { $detail_where[] = "p.tanggal >= '".$conn->real_escape_string($filter_date_from)."'"; }
    if ($filter_date_to !== '') { $detail_where[] = "p.tanggal <= '".$conn->real_escape_string($filter_date_to)."'"; }
    $detail_where[] = "p.mahasiswa_id = $detail_mhs_id";
    $detail_where_sql = 'WHERE ' . implode(' AND ', $detail_where);
    
    $detail_sql = "SELECT p.* FROM Presensi p $detail_where_sql ORDER BY p.tanggal ASC";
    $detail_result = $conn->query($detail_sql);
    while ($dr = $detail_result->fetch_assoc()) { $detail_data[] = $dr; }
}

function buildQuery($params) { return http_build_query(array_filter($params, function($v) { return $v !== ''; })); }
$query_params = ['search' => $search, 'date_from' => $filter_date_from, 'date_to' => $filter_date_to, 'periode' => $filter_periode, 'universitas' => $filter_universitas];

function formatDuration($time_str) {
    if (!$time_str) return '0j 0m';
    $parts = explode(':', $time_str);
    $hours = (int)$parts[0];
    $minutes = (int)($parts[1] ?? 0);
    return $hours . 'j ' . $minutes . 'm';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kehadiran - MAGIS Admin</title>
    <meta name="description" content="Laporan rekapitulasi kehadiran mahasiswa magang MAGIS">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out forwards; }
        .animate-fade-delay-1 { animation-delay: 0.05s; opacity: 0; }
        .animate-fade-delay-2 { animation-delay: 0.1s; opacity: 0; }
        .animate-fade-delay-3 { animation-delay: 0.15s; opacity: 0; }
        .animate-fade-delay-4 { animation-delay: 0.2s; opacity: 0; }
        
        /* Print Styles */
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            aside { display: none !important; }
            main { margin-left: 0 !important; }
            .print-table { border-collapse: collapse !important; }
            .print-table th, .print-table td { border: 1px solid #333 !important; padding: 8px !important; }
            .print-table th { background: #f0f0f0 !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            .rounded-\[2\.5rem\], .rounded-\[2rem\] { border-radius: 0 !important; }
            .shadow-sm, .shadow-lg, .shadow-xl { box-shadow: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        
        /* Modal Background */
        .modal-overlay {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <?php include '_sidebar_admin.php'; ?>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between no-print">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Rekapitulasi Kehadiran</h2>
                <p class="text-xs text-slate-500 font-medium tracking-wide mt-1">Laporan presensi harian mahasiswa magang</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="flex items-center gap-2 bg-slate-800 text-white px-5 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-900 transition active:scale-95 shadow-lg shadow-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12h.008v.008h-.008V12Zm-1.5 0h.008v.008H17.25V12Z" /></svg>
                    Cetak Laporan
                </button>
                <button onclick="exportCSV()" class="flex items-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-2xl font-bold text-sm hover:bg-emerald-700 transition active:scale-95 shadow-lg shadow-emerald-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Export CSV
                </button>
            </div>
        </header>

        <!-- Print Header (only visible when printing) -->
        <div class="print-only hidden p-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-black text-slate-900">LAPORAN REKAPITULASI KEHADIRAN</h1>
                <h2 class="text-lg font-bold text-slate-700 mt-1">Sistem Informasi Magang - MAGIS</h2>
                <div class="mt-3 text-sm text-slate-600">
                    <?php if ($filter_date_from || $filter_date_to): ?>
                        <p>Periode: <?php echo $filter_date_from ? date('d M Y', strtotime($filter_date_from)) : 'Awal'; ?> — <?php echo $filter_date_to ? date('d M Y', strtotime($filter_date_to)) : 'Sekarang'; ?></p>
                    <?php endif; ?>
                    <p>Dicetak pada: <?php echo date('d M Y, H:i'); ?> WIB</p>
                </div>
            </div>
            <hr class="border-t-2 border-slate-800 mb-4">
        </div>

        <div class="p-8 space-y-6">
            <!-- ═══ Summary Cards ═══ -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 no-print">
                <div class="bg-white p-5 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-blue-50/50 transition duration-300 group animate-fade animate-fade-delay-1">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl group-hover:scale-110 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.97 5.97 0 0 0-.942 3.197M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z" /></svg>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-slate-900 leading-none"><?php echo $summary['total_mhs']; ?></p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Mahasiswa</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-emerald-50/50 transition duration-300 group animate-fade animate-fade-delay-2">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl group-hover:scale-110 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-slate-900 leading-none"><?php echo $summary['total_presensi']; ?></p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Total Presensi</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-violet-50/50 transition duration-300 group animate-fade animate-fade-delay-3">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-violet-50 text-violet-600 rounded-2xl group-hover:scale-110 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-slate-900 leading-none"><?php echo formatDuration($summary['grand_total_jam']); ?></p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Total Jam Kerja</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 p-5 rounded-[2rem] shadow-xl shadow-indigo-100 text-white group hover:from-indigo-700 hover:to-indigo-800 transition duration-300 animate-fade animate-fade-delay-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/20 rounded-2xl group-hover:scale-110 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                        </div>
                        <div>
                            <p class="text-3xl font-black leading-none"><?php echo formatDuration($summary['grand_avg_jam']); ?></p>
                            <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mt-1">Rata-rata/Hari</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Filter Bar ═══ -->
            <form method="GET" class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm no-print" id="filterForm">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Cari Nama / NIM</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ketik nama atau NIM..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Universitas</label>
                        <select name="universitas" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="">Semua Universitas</option>
                            <?php foreach ($universitas_list as $univ): ?>
                            <option value="<?php echo htmlspecialchars($univ); ?>" <?php echo $filter_universitas === $univ ? 'selected' : ''; ?>><?php echo htmlspecialchars($univ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="min-w-[140px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>
                    <div class="min-w-[140px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>
                    <div class="min-w-[160px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Periode</label>
                        <select name="periode" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            <option value="">Semua Periode</option>
                            <?php foreach ($periodes as $pr): ?>
                            <option value="<?php echo $pr['id_periode']; ?>" <?php echo $filter_periode == $pr['id_periode'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pr['nama_periode']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                        Filter
                    </button>
                    <?php if ($search !== '' || $filter_date_from !== '' || $filter_date_to !== '' || $filter_periode !== '' || $filter_universitas !== ''): ?>
                    <a href="admin-rekap-presensi.php" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- ═══ Detail Modal (if viewing a student) ═══ -->
            <?php if ($detail_mhs_id > 0 && $detail_mhs_info): ?>
            <div class="fixed inset-0 z-50 flex items-center justify-center modal-overlay no-print" id="detailModal">
                <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col animate-fade">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-8 py-6 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white font-black text-xl"><?php echo strtoupper(substr($detail_mhs_info['nama'], 0, 1)); ?></div>
                            <div class="text-white">
                                <h3 class="text-xl font-black leading-tight"><?php echo htmlspecialchars($detail_mhs_info['nama']); ?></h3>
                                <p class="text-indigo-200 text-sm font-bold">NIM: <?php echo htmlspecialchars($detail_mhs_info['nim']); ?> · <?php echo htmlspecialchars($detail_mhs_info['universitas']); ?></p>
                            </div>
                        </div>
                        <a href="admin-rekap-presensi.php?<?php echo buildQuery($query_params); ?>" class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-white hover:bg-white/30 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </a>
                    </div>
                    <!-- Modal Body -->
                    <div class="flex-1 overflow-y-auto p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="font-black text-slate-800 text-lg flex items-center gap-2">
                                <span class="w-1.5 h-5 bg-indigo-600 rounded-full"></span>
                                Detail Presensi Harian
                            </h4>
                            <span class="text-sm font-bold text-slate-400"><?php echo count($detail_data); ?> data</span>
                        </div>
                        <?php if (count($detail_data) > 0): ?>
                        <div class="rounded-2xl border border-slate-200 overflow-hidden">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest">
                                    <tr>
                                        <th class="py-4 px-5">No</th>
                                        <th class="py-4 px-5">Tanggal</th>
                                        <th class="py-4 px-5">Hari</th>
                                        <th class="py-4 px-5">Jam Masuk</th>
                                        <th class="py-4 px-5">Jam Keluar</th>
                                        <th class="py-4 px-5">Durasi</th>
                                        <th class="py-4 px-5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php 
                                    $days_id = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                    foreach ($detail_data as $idx => $dd): 
                                        $day_num = date('w', strtotime($dd['tanggal']));
                                        $duration = '';
                                        $status_class = '';
                                        $status_text = '';
                                        if ($dd['jam_masuk'] && $dd['jam_keluar']) {
                                            $diff = strtotime($dd['jam_keluar']) - strtotime($dd['jam_masuk']);
                                            $h = floor($diff / 3600);
                                            $m = floor(($diff % 3600) / 60);
                                            $duration = $h . 'j ' . $m . 'm';
                                            $status_class = 'bg-emerald-100 text-emerald-700';
                                            $status_text = 'Lengkap';
                                        } elseif ($dd['jam_masuk']) {
                                            $duration = '-';
                                            $status_class = 'bg-yellow-100 text-yellow-700';
                                            $status_text = 'Belum Keluar';
                                        } else {
                                            $duration = '-';
                                            $status_class = 'bg-slate-100 text-slate-500';
                                            $status_text = 'Tidak Ada';
                                        }
                                    ?>
                                    <tr class="hover:bg-indigo-50/30 transition">
                                        <td class="py-4 px-5 text-sm font-bold text-slate-400"><?php echo $idx + 1; ?></td>
                                        <td class="py-4 px-5 text-sm font-bold text-slate-800"><?php echo date('d M Y', strtotime($dd['tanggal'])); ?></td>
                                        <td class="py-4 px-5 text-sm font-bold text-slate-600"><?php echo $days_id[$day_num]; ?></td>
                                        <td class="py-4 px-5 text-sm font-bold text-emerald-600"><?php echo $dd['jam_masuk'] ? date('H:i', strtotime($dd['jam_masuk'])) : '-'; ?></td>
                                        <td class="py-4 px-5 text-sm font-bold text-rose-600"><?php echo $dd['jam_keluar'] ? date('H:i', strtotime($dd['jam_keluar'])) : '-'; ?></td>
                                        <td class="py-4 px-5 text-sm font-black text-indigo-700"><?php echo $duration; ?></td>
                                        <td class="py-4 px-5 text-center">
                                            <span class="text-[9px] font-black px-3 py-1.5 <?php echo $status_class; ?> rounded-lg uppercase tracking-widest"><?php echo $status_text; ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-12 text-slate-400 italic font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <p>Belum ada data presensi untuk mahasiswa ini.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ═══ Main Recap Table ═══ -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 flex items-center gap-2">
                        <span class="w-1.5 h-5 bg-indigo-600 rounded-full"></span>
                        Tabel Rekapitulasi Kehadiran
                    </h3>
                    <span class="text-xs font-bold text-slate-400"><?php echo $total_rows; ?> mahasiswa</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left print-table" id="rekapTable">
                        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest">
                            <tr>
                                <th class="py-5 px-6">No</th>
                                <th class="py-5 px-6">NIM</th>
                                <th class="py-5 px-6">Nama Mahasiswa</th>
                                <th class="py-5 px-6">Universitas</th>
                                <th class="py-5 px-6 text-center">Total Hadir</th>
                                <th class="py-5 px-6 text-center">Lengkap</th>
                                <th class="py-5 px-6 text-center">Belum Keluar</th>
                                <th class="py-5 px-6">Total Jam Kerja</th>
                                <th class="py-5 px-6">Rata-rata/Hari</th>
                                <th class="py-5 px-6">Avg Masuk</th>
                                <th class="py-5 px-6">Avg Keluar</th>
                                <th class="py-5 px-6 text-center no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if ($result && $result->num_rows > 0): $no = $offset + 1; while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-indigo-50/20 transition group">
                                <td class="py-5 px-6 text-sm font-bold text-slate-400"><?php echo $no++; ?></td>
                                <td class="py-5 px-6">
                                    <span class="text-sm font-black text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg"><?php echo htmlspecialchars($row['nim']); ?></span>
                                </td>
                                <td class="py-5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-100 to-blue-100 text-indigo-700 rounded-xl flex items-center justify-center font-black text-sm"><?php echo strtoupper(substr($row['nama_mhs'], 0, 1)); ?></div>
                                        <div>
                                            <p class="font-black text-slate-800 text-sm leading-tight"><?php echo htmlspecialchars($row['nama_mhs']); ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold"><?php echo htmlspecialchars($row['jurusan']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-6 text-sm font-bold text-slate-600 max-w-[150px] truncate"><?php echo htmlspecialchars($row['universitas']); ?></td>
                                <td class="py-5 px-6 text-center">
                                    <span class="text-lg font-black text-slate-800"><?php echo $row['total_hadir']; ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold block">hari</span>
                                </td>
                                <td class="py-5 px-6 text-center">
                                    <span class="text-sm font-black text-emerald-600"><?php echo $row['total_lengkap']; ?></span>
                                </td>
                                <td class="py-5 px-6 text-center">
                                    <?php if ($row['total_belum_keluar'] > 0): ?>
                                    <span class="text-sm font-black text-amber-600"><?php echo $row['total_belum_keluar']; ?></span>
                                    <?php else: ?>
                                    <span class="text-sm font-bold text-slate-300">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="font-black text-indigo-700 text-sm"><?php echo formatDuration($row['total_jam_kerja']); ?></span>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="font-bold text-slate-700 text-sm"><?php echo formatDuration($row['avg_jam_kerja']); ?></span>
                                </td>
                                <td class="py-5 px-6 font-bold text-emerald-600 text-sm"><?php echo $row['avg_jam_masuk'] ?? '-'; ?></td>
                                <td class="py-5 px-6 font-bold text-rose-600 text-sm"><?php echo $row['avg_jam_keluar'] ?? '-'; ?></td>
                                <td class="py-5 px-6 text-center no-print">
                                    <a href="admin-rekap-presensi.php?<?php echo buildQuery(array_merge($query_params, ['detail' => $row['id_mahasiswa']])); ?>" class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-xs font-black hover:bg-indigo-100 transition active:scale-95" title="Lihat detail harian">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="12" class="py-16 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-14 h-14 mx-auto mb-4 text-slate-200"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h3.75m-3.75 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    <p class="text-slate-400 font-bold italic text-sm"><?php echo ($search !== '' || $filter_date_from !== '' || $filter_date_to !== '' || $filter_periode !== '' || $filter_universitas !== '') ? 'Tidak ada data yang cocok dengan filter.' : 'Belum ada data presensi.'; ?></p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ═══ Pagination ═══ -->
            <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-between no-print">
                <p class="text-sm text-slate-500 font-bold">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> &middot; <?php echo $total_rows; ?> mahasiswa</p>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?><a href="?<?php echo buildQuery(array_merge($query_params, ['page' => $page - 1])); ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">&laquo; Prev</a><?php endif; ?>
                    <?php $sp = max(1, $page - 2); $ep = min($total_pages, $page + 2); for ($i = $sp; $i <= $ep; $i++): ?>
                    <a href="?<?php echo buildQuery(array_merge($query_params, ['page' => $i])); ?>" class="px-4 py-2 <?php echo $i === $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?> rounded-xl text-sm font-bold transition shadow-sm"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?><a href="?<?php echo buildQuery(array_merge($query_params, ['page' => $page + 1])); ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">Next &raquo;</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    function exportCSV() {
        const table = document.getElementById('rekapTable');
        const rows = table.querySelectorAll('tr');
        let csv = '\uFEFF'; // BOM for Excel UTF-8
        
        rows.forEach((row, rowIndex) => {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            cols.forEach((col, colIndex) => {
                // Skip the last column (Aksi)
                if (colIndex === cols.length - 1 && col.closest('thead, tbody')) {
                    if (col.classList.contains('no-print')) return;
                }
                let text = col.textContent.trim().replace(/\s+/g, ' ');
                // Escape quotes
                text = text.replace(/"/g, '""');
                rowData.push('"' + text + '"');
            });
            csv += rowData.join(';') + '\n';
        });
        
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'rekap_kehadiran_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Close modal on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('detailModal');
            if (modal) {
                const closeLink = modal.querySelector('a[href*="admin-rekap-presensi"]');
                if (closeLink) closeLink.click();
            }
        }
    });
    
    // Close modal on overlay click
    const overlay = document.getElementById('detailModal');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                const closeLink = this.querySelector('a[href*="admin-rekap-presensi"]');
                if (closeLink) closeLink.click();
            }
        });
    }
    </script>
</body>
</html>
