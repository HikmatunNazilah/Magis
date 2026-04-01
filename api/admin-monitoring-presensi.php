<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login-admin.php"); exit(); }
$nama_admin = $_SESSION['nama'];
$active_page = 'presensi';

// Search, Filter & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = [];
$se = $conn->real_escape_string($search);
if ($search !== '') { $where[] = "(u.nama LIKE '%$se%' OR m.nim LIKE '%$se%')"; }
if ($filter_date !== '') { $where[] = "p.tanggal = '".$conn->real_escape_string($filter_date)."'"; }
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$total_rows = $conn->query("SELECT COUNT(*) as total FROM Presensi p JOIN Mahasiswa m ON p.mahasiswa_id = m.id_mahasiswa JOIN Users u ON m.user_id = u.id_user $where_sql")->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));

$sql = "SELECT p.*, u.nama as nama_mhs, m.nim FROM Presensi p JOIN Mahasiswa m ON p.mahasiswa_id = m.id_mahasiswa JOIN Users u ON m.user_id = u.id_user $where_sql ORDER BY p.tanggal DESC, p.jam_masuk DESC LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);

function buildQuery($params) { return http_build_query(array_filter($params, function($v) { return $v !== ''; })); }
$query_params = ['search' => $search, 'tanggal' => $filter_date];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Presensi - MAGIS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <?php include '_sidebar_admin.php'; ?>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Monitoring Presensi Mahasiswa</h2>
            <div class="px-4 py-2 bg-indigo-50 rounded-xl">
                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest leading-none mb-1">Total Data</p>
                <p class="text-lg font-black text-indigo-700 leading-none"><?php echo $total_rows; ?></p>
            </div>
        </header>

        <div class="p-8">
            <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Cari Mahasiswa</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama atau NIM..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                    </div>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Tanggal</label>
                    <input type="date" name="tanggal" value="<?php echo htmlspecialchars($filter_date); ?>" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    Cari
                </button>
                <?php if ($search !== '' || $filter_date !== ''): ?>
                <a href="admin-monitoring-presensi.php" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                <?php endif; ?>
            </form>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest">
                        <tr>
                            <th class="py-5 px-8">Mahasiswa</th>
                            <th class="py-5 px-6">Tanggal</th>
                            <th class="py-5 px-6">Masuk</th>
                            <th class="py-5 px-6">Keluar</th>
                            <th class="py-5 px-8 text-center">Foto Selfie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-indigo-50/10 transition group">
                            <td class="py-6 px-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-blue-100/50 text-blue-700 rounded-xl flex items-center justify-center font-black text-sm"><?php echo strtoupper(substr($row['nama_mhs'], 0, 1)); ?></div>
                                    <div>
                                        <p class="font-black text-slate-800 leading-tight text-sm"><?php echo htmlspecialchars($row['nama_mhs']); ?></p>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">NIM: <?php echo htmlspecialchars($row['nim']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-6 px-6 font-bold text-slate-700 text-sm"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                            <td class="py-6 px-6 font-bold text-emerald-600 text-sm"><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                            <td class="py-6 px-6 font-bold text-rose-600 text-sm"><?php echo $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-'; ?></td>
                            <td class="py-6 px-8 flex justify-center">
                                <?php if($row['foto_selfie']): ?>
                                <div class="relative group/photo">
                                    <img src="uploads/<?php echo $row['foto_selfie']; ?>" alt="Selfie" class="w-10 h-10 object-cover rounded-xl shadow-md border-2 border-white group-hover/photo:scale-[2.5] transition duration-300 z-10 relative cursor-pointer" onclick="window.open('uploads/<?php echo $row['foto_selfie']; ?>')">
                                </div>
                                <?php else: ?><span class="text-[10px] text-slate-300 italic font-medium">No photo</span><?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" class="py-12 text-center text-slate-400 font-bold italic text-sm"><?php echo ($search !== '' || $filter_date !== '') ? 'Tidak ada data yang cocok.' : 'Belum ada data presensi.'; ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-between mt-6">
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
    </main>
</body>
</html>
