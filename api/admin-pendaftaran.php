<?php
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-admin.php");
    exit();
}

$nama_admin = $_SESSION['nama'];

// Handle Acceptance/Rejection/Change Mentor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id_mahasiswa = (int)$_POST['id_mahasiswa'];
    $action = $_POST['action']; // 'aktif', 'ditolak', or 'change_mentor'
    $mentor_id = isset($_POST['mentor_id']) ? (int)$_POST['mentor_id'] : 'NULL';
    
    if ($action === 'aktif') {
        $sql_update = "UPDATE Mahasiswa SET status = '$action', mentor_id = $mentor_id WHERE id_mahasiswa = $id_mahasiswa";
    } else if ($action === 'change_mentor') {
        $sql_update = "UPDATE Mahasiswa SET mentor_id = $mentor_id WHERE id_mahasiswa = $id_mahasiswa";
    } else {
        $sql_update = "UPDATE Mahasiswa SET status = '$action', mentor_id = NULL WHERE id_mahasiswa = $id_mahasiswa";
    }
    
    $conn->query($sql_update);
    header("Location: admin-pendaftaran.php?success=1");
    exit();
}

// Fetch Mentors
$mentors = [];
$m_sql = "SELECT m.id_mentor, u.nama FROM Mentor m JOIN Users u ON m.user_id = u.id_user";
$m_result = $conn->query($m_sql);
if ($m_result && $m_result->num_rows > 0) {
    while($m_row = $m_result->fetch_assoc()) {
        $mentors[] = $m_row;
    }
}

// Search, Filter & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where = [];
$search_escaped = $conn->real_escape_string($search);
if ($search !== '') {
    $where[] = "(u.nama LIKE '%$search_escaped%' OR m.nim LIKE '%$search_escaped%')";
}
if ($filter_status !== '') {
    $status_escaped = $conn->real_escape_string($filter_status);
    $where[] = "m.status = '$status_escaped'";
}
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$count_sql = "SELECT COUNT(*) as total FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id $where_sql";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));

// Fetch paginated
$sql = "SELECT m.*, u.nama, u.email, mentor_user.nama as nama_mentor 
        FROM Mahasiswa m 
        JOIN Users u ON u.id_user = m.user_id 
        LEFT JOIN Mentor mentor_tab ON m.mentor_id = mentor_tab.id_mentor
        LEFT JOIN Users mentor_user ON mentor_tab.user_id = mentor_user.id_user
        $where_sql
        ORDER BY FIELD(m.status, 'pending', 'aktif', 'ditolak'), m.id_mahasiswa DESC
        LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);

// Build query string for pagination links
function buildQuery($params) {
    return http_build_query(array_filter($params, function($v) { return $v !== ''; }));
}
$query_params = ['search' => $search, 'status' => $filter_status];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftaran - MAGIS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <!-- Sidebar Admin -->
    <aside class="w-64 bg-slate-900 shadow-xl h-screen fixed left-0 top-0 flex flex-col z-20">
        <div class="h-20 bg-indigo-700 flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" />
            </svg>
            <h1 class="font-extrabold text-xl text-white tracking-wider leading-none">MAGIS ADMIN</h1>
        </div>
        <div class="p-4 text-slate-500 text-xs font-bold uppercase tracking-widest mt-4">Menu Utama</div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard-admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span class="font-semibold">Dashboard</span>
            </a>
            <a href="admin-periode.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <span class="font-semibold">Kelola Periode</span>
            </a>
            <a href="admin-mentor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="font-semibold">Data Mentor</span>
            </a>
            <a href="admin-pendaftaran.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="font-bold">Data Pendaftaran</span>
            </a>
            <a href="admin-sertifikat.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <span class="font-semibold">Sertifikat</span>
            </a>
            <div class="p-2 text-slate-500 text-[10px] font-black uppercase tracking-widest mt-2 border-t border-slate-800/50 pt-4">Monitoring</div>
            <a href="admin-monitoring-presensi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-semibold">Presensi</span>
            </a>
            <a href="admin-monitoring-logbook.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <span class="font-semibold">Logbook</span>
            </a>
            <div class="p-2 text-slate-500 text-[10px] font-black uppercase tracking-widest mt-2 border-t border-slate-800/50 pt-4">Management</div>
            <a href="admin-mahasiswa-manual.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 font-black">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
                <span class="font-semibold">Input Manual</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition group font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span>Logout Admin</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Validasi Pendaftaran Mahasiswa</h2>
            <div class="px-4 py-2 bg-indigo-50 rounded-xl">
                 <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest leading-none mb-1">Total Data</p>
                 <p class="text-lg font-black text-indigo-700 leading-none"><?php echo $total_rows; ?></p>
            </div>
        </header>

        <div class="p-8">
            <!-- Search & Filter Bar -->
            <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Cari Mahasiswa</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama atau NIM..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                    </div>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Status</label>
                    <select name="status" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="aktif" <?php echo $filter_status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    Cari
                </button>
                <?php if ($search !== '' || $filter_status !== ''): ?>
                <a href="admin-pendaftaran.php" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                <?php endif; ?>
            </form>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest">
                        <tr>
                            <th class="py-5 px-6">Mahasiswa</th>
                            <th class="py-5 px-6">Instansi & Prodi</th>
                            <th class="py-5 px-6">Status</th>
                            <th class="py-5 px-6">Mentor</th>
                            <th class="py-5 px-6 text-center">Aksi / Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-indigo-50/10 transition group">
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-blue-100/50 text-blue-700 rounded-2xl flex items-center justify-center font-black text-lg">
                                            <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($row['nama']); ?></p>
                                            <p class="text-xs text-slate-500 font-bold uppercase tracking-tight">NIM: <?php echo htmlspecialchars($row['nim'] ?? 'N/A'); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-6">
                                    <p class="font-black text-slate-700 text-sm"><?php echo htmlspecialchars($row['universitas'] ?? '-'); ?></p>
                                    <p class="text-xs text-slate-500 font-medium"><?php echo htmlspecialchars($row['jurusan'] ?? '-'); ?></p>
                                </td>
                                <td class="py-6 px-6">
                                    <?php 
                                    $badge = "bg-yellow-100 text-yellow-700";
                                    if($row['status'] === 'aktif') $badge = "bg-emerald-100 text-emerald-700";
                                    if($row['status'] === 'ditolak') $badge = "bg-red-100 text-red-700";
                                    ?>
                                    <span class="px-4 py-1.5 <?php echo $badge; ?> text-[10px] font-black rounded-xl uppercase tracking-widest">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td class="py-6 px-6">
                                    <?php if($row['status'] === 'aktif'): ?>
                                        <div class="flex flex-col gap-1">
                                            <p class="text-sm font-black text-slate-700"><?php echo htmlspecialchars($row['nama_mentor'] ?? 'Belum ada'); ?></p>
                                            <button onclick="openChangeMentorModal(<?php echo $row['id_mahasiswa']; ?>, '<?php echo addslashes($row['nama']); ?>', <?php echo $row['mentor_id'] ?? 'null'; ?>)" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-tighter text-left w-fit">🔄 Ganti Mentor</button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-400 font-bold italic">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex justify-center gap-2">
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form action="admin-pendaftaran.php" method="POST" class="flex flex-col gap-2 w-full max-w-xs">
                                                <input type="hidden" name="id_mahasiswa" value="<?php echo $row['id_mahasiswa']; ?>">
                                                <select name="mentor_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 transition" required>
                                                    <option value="" disabled selected>Pilih Mentor...</option>
                                                    <?php foreach($mentors as $m): ?>
                                                        <option value="<?php echo $m['id_mentor']; ?>"><?php echo htmlspecialchars($m['nama']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="flex gap-2">
                                                    <button type="submit" name="action" value="aktif" class="flex-1 bg-emerald-500 text-white px-4 py-2 rounded-xl font-black text-[10px] hover:bg-emerald-600 shadow-lg shadow-emerald-100 transition active:scale-95 uppercase tracking-wider">Terima</button>
                                                    <button type="submit" name="action" value="ditolak" class="flex-1 bg-rose-500 text-white px-4 py-2 rounded-xl font-black text-[10px] hover:bg-rose-600 shadow-lg shadow-rose-100 transition active:scale-95 uppercase tracking-wider">Tolak</button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 font-bold italic">Sudah divalidasi</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 font-bold italic">
                                    <?php echo ($search !== '' || $filter_status !== '') ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada pendaftaran mahasiswa.'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-between mt-6">
                <p class="text-sm text-slate-500 font-bold">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> &middot; <?php echo $total_rows; ?> data</p>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo buildQuery(array_merge($query_params, ['page' => $page - 1])); ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">&laquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                    <a href="?<?php echo buildQuery(array_merge($query_params, ['page' => $i])); ?>" class="px-4 py-2 <?php echo $i === $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?> rounded-xl text-sm font-bold transition shadow-sm"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo buildQuery(array_merge($query_params, ['page' => $page + 1])); ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Ganti Mentor -->
    <div id="changeMentorModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-md shadow-2xl animate-in fade-in zoom-in duration-300">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Ganti Mentor</h3>
                <button onclick="closeChangeMentorModal()" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="admin-pendaftaran.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="change_mentor">
                <input type="hidden" name="id_mahasiswa" id="modal_mhs_id">
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Mahasiswa</label>
                    <p id="modal_mhs_nama" class="font-black text-slate-800 text-lg px-1"></p>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Pilih Mentor Baru</label>
                    <select name="mentor_id" id="modal_mentor_id" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                        <?php foreach($mentors as $m): ?>
                            <option value="<?php echo $m['id_mentor']; ?>"><?php echo htmlspecialchars($m['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex gap-4 pt-6 border-t border-slate-50">
                    <button type="button" onclick="closeChangeMentorModal()" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 font-black rounded-2xl hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openChangeMentorModal(id, nama, mentorId) {
            document.getElementById('modal_mhs_id').value = id;
            document.getElementById('modal_mhs_nama').innerText = nama;
            document.getElementById('modal_mentor_id').value = mentorId;
            document.getElementById('changeMentorModal').classList.remove('hidden');
        }

        function closeChangeMentorModal() {
            document.getElementById('changeMentorModal').classList.add('hidden');
        }
    </script>
</body>
</html>
