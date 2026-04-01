<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor' || !isset($_SESSION['mentor_id'])) {
    header("Location: index.php");
    exit();
}

$nama_mentor = $_SESSION['nama'];
$mentor_id = $_SESSION['mentor_id'];

// Search, Filter & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where = ["m.mentor_id = ?"];
$params = [$mentor_id];
$types = "i";

if ($search !== '') {
    $where[] = "(u.nama LIKE ? OR m.nim LIKE ?)";
    $search_like = "%$search%";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss";
}
if ($filter_status !== '') {
    $where[] = "m.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

// Count total
$count_sql = "SELECT COUNT(*) as total FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id $where_sql";
$stmt_count = $conn->prepare($count_sql);
$stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));

// Fetch paginated
$sql_mhs = "SELECT m.id_mahasiswa, u.nama, m.universitas, m.jurusan, m.status, m.nim
            FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id
            $where_sql ORDER BY m.status ASC, u.nama ASC LIMIT ? OFFSET ?";
$params_paged = array_merge($params, [$per_page, $offset]);
$types_paged = $types . "ii";
$stmt = $conn->prepare($sql_mhs);
$stmt->bind_param($types_paged, ...$params_paged);
$stmt->execute();
$res_mhs = $stmt->get_result();

// Stats (unfiltered)
$stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM Mahasiswa WHERE mentor_id = ?");
$stmt_total->bind_param("i", $mentor_id);
$stmt_total->execute();
$total_mhs_all = $stmt_total->get_result()->fetch_assoc()['total'];

// Find Active Period
$res_periode = $conn->query("SELECT * FROM Periode_Magang ORDER BY id_periode DESC LIMIT 1");
$periode = $res_periode->fetch_assoc();
$nama_periode = $periode ? $periode['nama_periode'] : "Tidak ada periode aktif";

function buildQuery($params) { return http_build_query(array_filter($params, function($v) { return $v !== ''; })); }
$query_params = ['search' => $search, 'status' => $filter_status];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mentor - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <!-- Sidebar Mentor -->
    <aside class="w-64 bg-slate-900 shadow-xl h-screen fixed left-0 top-0 flex flex-col z-20">
        <div class="h-20 bg-indigo-700 flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" /></svg>
            <h1 class="font-extrabold text-xl text-white tracking-wider leading-none">MAGIS MENTOR</h1>
        </div>
        <div class="p-4 text-slate-500 text-xs font-bold uppercase tracking-widest mt-4">Menu Utama</div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard-mentor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                <span class="font-bold">Dashboard Papan</span>
            </a>
            <a href="mentor-monitoring-presensi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /></svg>
                <span class="font-semibold">Monitoring Presensi</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition group font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                <span>Logout Mentor</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Dashboard Mentor</h2>
                <p class="text-xs text-slate-500 font-medium tracking-wide">Selamat datang, <?php echo htmlspecialchars($nama_mentor); ?></p>
            </div>
            <div class="flex items-center gap-4">
                <a href="mentor-settings.php" title="Pengaturan Profil" class="w-10 h-10 bg-indigo-600 shadow-lg shadow-indigo-100 text-white rounded-xl flex items-center justify-center font-black hover:scale-110 hover:bg-indigo-700 transition transform border-2 border-white">
                    <?php echo strtoupper(substr($nama_mentor, 0, 1)); ?>
                </a>
            </div>
        </header>

        <div class="p-8 space-y-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        </div>
                        <span class="text-xs font-black text-slate-300 uppercase tracking-widest">Team</span>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tight leading-none"><?php echo $total_mhs_all; ?></h3>
                    <p class="text-sm font-bold text-slate-500 mt-2">Mahasiswa Bimbingan</p>
                </div>
                
                <div class="bg-indigo-600 p-6 rounded-[2rem] shadow-xl shadow-indigo-100 text-white md:col-span-2 relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-64 h-64 -translate-y-1/4 translate-x-1/4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118.75 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" /></svg>
                    </div>
                    <div class="relative z-10 flex flex-col justify-center h-full">
                        <h3 class="text-3xl font-black tracking-tight leading-none text-white mb-2">Tinjau Aktivitas</h3>
                        <p class="text-sm font-bold text-indigo-100 mb-6 max-w-md">Pantau logbook harian, berikan evaluasi, dan pastikan mahasiswa mendapatkan pengalaman magang terbaik.</p>
                        <div class="inline-block px-4 py-2 bg-white/20 rounded-xl text-white font-bold text-sm w-max backdrop-blur-sm border border-white/10">
                            Periode Aktif: <?php echo htmlspecialchars($nama_periode); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mahasiswa List -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                    Daftar Mahasiswa Bimbingan
                </h3>

                <!-- Search & Filter -->
                <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Cari Mahasiswa</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama atau NIM..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                        </div>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Status</label>
                        <select name="status" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                            <option value="">Semua</option>
                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="aktif" <?php echo $filter_status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        Cari
                    </button>
                    <?php if ($search !== '' || $filter_status !== ''): ?>
                    <a href="dashboard-mentor.php" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-100 text-slate-400 text-xs font-black uppercase tracking-wider">
                                <th class="pb-4 pl-4">Mahasiswa</th>
                                <th class="pb-4">Instansi/Universitas</th>
                                <th class="pb-4">Status</th>
                                <th class="pb-4 text-right pr-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php if ($res_mhs && $res_mhs->num_rows > 0): ?>
                                <?php while($row = $res_mhs->fetch_assoc()): ?>
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                    <td class="py-4 pl-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center font-black">
                                                <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-800"><?php echo htmlspecialchars($row['nama']); ?></p>
                                                <p class="text-xs text-slate-500 font-semibold"><?php echo htmlspecialchars($row['jurusan']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4"><span class="font-bold text-slate-600"><?php echo htmlspecialchars($row['universitas']); ?></span></td>
                                    <td class="py-4">
                                        <?php $badge = "bg-yellow-100 text-yellow-700"; if($row['status'] === 'aktif') $badge = "bg-emerald-100 text-emerald-700"; if($row['status'] === 'selesai') $badge = "bg-blue-100 text-blue-700"; ?>
                                        <span class="text-[10px] font-black px-3 py-1.5 <?php echo $badge; ?> rounded-xl uppercase tracking-widest"><?php echo $row['status']; ?></span>
                                    </td>
                                    <td class="py-4 pr-4 text-right flex justify-end gap-2">
                                        <a href="mentor-mahasiswa-detail.php?id=<?php echo $row['id_mahasiswa']; ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl font-bold transition duration-300">Detail & Logbook</a>
                                        <a href="mentor-penilaian.php?id=<?php echo $row['id_mahasiswa']; ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl font-bold transition duration-300">Penilaian</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="py-12 text-center text-slate-400 italic font-medium"><?php echo ($search !== '' || $filter_status !== '') ? 'Tidak ada mahasiswa yang cocok dengan pencarian.' : 'Belum ada mahasiswa bimbingan.'; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="flex items-center justify-between mt-6 pt-6 border-t border-slate-100">
                    <p class="text-sm text-slate-500 font-bold">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> &middot; <?php echo $total_rows; ?> data</p>
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
        </div>
    </main>
</body>
</html>
