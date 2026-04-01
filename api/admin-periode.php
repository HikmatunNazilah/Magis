<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login-admin.php"); exit(); }
$nama_admin = $_SESSION['nama'];
$active_page = 'periode';

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_period'])) {
    $nama_proc = $conn->real_escape_string($_POST['nama_periode']);
    $tgl_mulai = $conn->real_escape_string($_POST['tgl_mulai']);
    $tgl_selesai = $conn->real_escape_string($_POST['tgl_selesai']);
    $kuota = (int)$_POST['kuota'];
    $admin_id = $_SESSION['user_id'];
    $conn->query("INSERT INTO Periode_Magang (nama_periode, tanggal_mulai, tanggal_selesai, kuota, created_by) VALUES ('$nama_proc', '$tgl_mulai', '$tgl_selesai', $kuota, $admin_id)");
    header("Location: admin-periode.php?success=added");
    exit();
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_period'])) {
    $id = (int)$_POST['id_periode'];
    $nama_proc = $conn->real_escape_string($_POST['nama_periode']);
    $tgl_mulai = $conn->real_escape_string($_POST['tgl_mulai']);
    $tgl_selesai = $conn->real_escape_string($_POST['tgl_selesai']);
    $kuota = (int)$_POST['kuota'];
    $conn->query("UPDATE Periode_Magang SET nama_periode='$nama_proc', tanggal_mulai='$tgl_mulai', tanggal_selesai='$tgl_selesai', kuota=$kuota WHERE id_periode=$id");
    header("Location: admin-periode.php?success=updated");
    exit();
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_period'])) {
    $id = (int)$_POST['id_periode'];
    $conn->query("DELETE FROM Periode_Magang WHERE id_periode=$id");
    header("Location: admin-periode.php?success=deleted");
    exit();
}

// Search, Filter & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$now = date('Y-m-d');

$where = [];
$se = $conn->real_escape_string($search);
if ($search !== '') { $where[] = "nama_periode LIKE '%$se%'"; }
if ($filter_status !== '') {
    if ($filter_status === 'active') { $where[] = "'$now' BETWEEN tanggal_mulai AND tanggal_selesai"; }
    elseif ($filter_status === 'upcoming') { $where[] = "'$now' < tanggal_mulai"; }
    elseif ($filter_status === 'expired') { $where[] = "'$now' > tanggal_selesai"; }
}
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$total_rows = $conn->query("SELECT COUNT(*) as total FROM Periode_Magang $where_sql")->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));
$result = $conn->query("SELECT * FROM Periode_Magang $where_sql ORDER BY tanggal_mulai DESC LIMIT $per_page OFFSET $offset");

function buildQuery($params) { return http_build_query(array_filter($params, function($v) { return $v !== ''; })); }
$query_params = ['search' => $search, 'status' => $filter_status];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Periode - MAGIS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <?php include '_sidebar_admin.php'; ?>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Kelola Periode Magang</h2>
            <button onclick="document.getElementById('addPeriodModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-1 transition active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Periode Baru
            </button>
        </header>

        <div class="p-8">
            <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 px-5 py-4 rounded-2xl font-bold text-sm flex items-center gap-3
                <?php echo $_GET['success'] === 'deleted' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <?php if ($_GET['success'] === 'deleted'): ?>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.682-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    <?php else: ?>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    <?php endif; ?>
                </svg>
                <?php
                $msgs = ['added' => 'Periode baru berhasil ditambahkan!', 'updated' => 'Periode berhasil diperbarui!', 'deleted' => 'Periode berhasil dihapus.'];
                echo $msgs[$_GET['success']] ?? 'Operasi berhasil.';
                ?>
            </div>
            <?php endif; ?>

            <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Cari Periode</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama periode..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                    </div>
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Status</label>
                    <select name="status" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                        <option value="">Semua</option>
                        <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="upcoming" <?php echo $filter_status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="expired" <?php echo $filter_status === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Cari</button>
                <?php if ($search !== '' || $filter_status !== ''): ?>
                <a href="admin-periode.php" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                <?php endif; ?>
            </form>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest">
                        <tr>
                            <th class="py-5 px-8">Nama Periode</th>
                            <th class="py-5 px-6">Mulai</th>
                            <th class="py-5 px-6">Selesai</th>
                            <th class="py-5 px-6">Kuota</th>
                            <th class="py-5 px-6 text-center">Status</th>
                            <th class="py-5 px-8 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if ($result && $result->num_rows > 0): while($row = $result->fetch_assoc()):
                            $status = "EXPIRED"; $badge = "bg-slate-100 text-slate-500";
                            if ($now >= $row['tanggal_mulai'] && $now <= $row['tanggal_selesai']) { $status = "ACTIVE"; $badge = "bg-emerald-100 text-emerald-700"; }
                            elseif ($now < $row['tanggal_mulai']) { $status = "UPCOMING"; $badge = "bg-blue-100 text-blue-700"; }
                        ?>
                        <tr class="hover:bg-indigo-50/20 transition group">
                            <td class="py-6 px-8 font-black text-slate-800"><?php echo htmlspecialchars($row['nama_periode']); ?></td>
                            <td class="py-6 px-6 text-slate-600 font-bold"><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                            <td class="py-6 px-6 text-slate-600 font-bold"><?php echo date('d M Y', strtotime($row['tanggal_selesai'])); ?></td>
                            <td class="py-6 px-6 font-black text-indigo-600"><?php echo $row['kuota']; ?></td>
                            <td class="py-6 px-6 text-center"><span class="px-3 py-1.5 <?php echo $badge; ?> text-[10px] font-black rounded-xl uppercase tracking-widest"><?php echo $status; ?></span></td>
                            <td class="py-6 px-8">
                                <div class="flex justify-center gap-2">
                                    <!-- Edit Button -->
                                    <button onclick="openEditModal(<?php echo $row['id_periode']; ?>, '<?php echo addslashes($row['nama_periode']); ?>', '<?php echo $row['tanggal_mulai']; ?>', '<?php echo $row['tanggal_selesai']; ?>', <?php echo $row['kuota']; ?>)"
                                        class="p-2.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 rounded-xl transition" title="Edit Periode">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    </button>
                                    <!-- Delete Button -->
                                    <button onclick="confirmDelete(<?php echo $row['id_periode']; ?>, '<?php echo addslashes($row['nama_periode']); ?>')"
                                        class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 rounded-xl transition" title="Hapus Periode">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.682-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" class="py-12 text-center text-slate-400 font-bold italic"><?php echo ($search !== '' || $filter_status !== '') ? 'Tidak ada data yang cocok.' : 'Belum ada periode magang.'; ?></td></tr>
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

    <!-- ══════ Modal Tambah Periode ══════ -->
    <div id="addPeriodModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-lg shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Buat Periode Baru</h3>
                <button onclick="document.getElementById('addPeriodModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="admin-periode.php" method="POST" class="space-y-5">
                <input type="hidden" name="add_period" value="1">
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">Nama Program / Batch</label>
                    <input type="text" name="nama_periode" placeholder="Magang Batch IX - 2026" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold placeholder:text-slate-300 transition" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold transition uppercase text-xs" required>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold transition uppercase text-xs" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">Kuota Mahasiswa</label>
                    <input type="number" name="kuota" placeholder="50" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold placeholder:text-slate-300 transition" required>
                </div>
                <div class="flex gap-4 pt-6 border-t border-slate-50 mt-4">
                    <button type="button" onclick="document.getElementById('addPeriodModal').classList.add('hidden')" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 font-black rounded-2xl hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Simpan Periode</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════ Modal Edit Periode ══════ -->
    <div id="editPeriodModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-lg shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Edit Periode</h3>
                <button onclick="document.getElementById('editPeriodModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="admin-periode.php" method="POST" class="space-y-5">
                <input type="hidden" name="edit_period" value="1">
                <input type="hidden" name="id_periode" id="edit_id_periode">
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">Nama Program / Batch</label>
                    <input type="text" name="nama_periode" id="edit_nama_periode" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold placeholder:text-slate-300 transition" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" id="edit_tgl_mulai" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold transition uppercase text-xs" required>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" id="edit_tgl_selesai" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold transition uppercase text-xs" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">Kuota Mahasiswa</label>
                    <input type="number" name="kuota" id="edit_kuota" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold placeholder:text-slate-300 transition" required>
                </div>
                <div class="flex gap-4 pt-6 border-t border-slate-50 mt-4">
                    <button type="button" onclick="document.getElementById('editPeriodModal').classList.add('hidden')" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 font-black rounded-2xl hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" action="admin-periode.php" method="POST" class="hidden">
        <input type="hidden" name="delete_period" value="1">
        <input type="hidden" name="id_periode" id="delete_id_periode">
    </form>

    <script>
        // Edit Modal
        function openEditModal(id, nama, mulai, selesai, kuota) {
            document.getElementById('edit_id_periode').value = id;
            document.getElementById('edit_nama_periode').value = nama;
            document.getElementById('edit_tgl_mulai').value = mulai;
            document.getElementById('edit_tgl_selesai').value = selesai;
            document.getElementById('edit_kuota').value = kuota;
            document.getElementById('editPeriodModal').classList.remove('hidden');
        }

        // Delete Confirmation
        function confirmDelete(id, nama) {
            Swal.fire({
                title: 'Hapus Periode?',
                html: 'Anda yakin ingin menghapus periode <strong>"' + nama + '"</strong>? Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id_periode').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html>
