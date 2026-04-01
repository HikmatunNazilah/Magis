<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login-admin.php"); exit(); }
$nama_admin = $_SESSION['nama'];
$active_page = 'sertifikat';

// Search, Filter & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = [];
$se = $conn->real_escape_string($search);
if ($search !== '') { $where[] = "(u.nama LIKE '%$se%')"; }
if ($filter_status !== '') { $where[] = "m.status = '".$conn->real_escape_string($filter_status)."'"; }
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$total_rows = $conn->query("SELECT COUNT(*) as total FROM mahasiswa m JOIN users u ON u.id_user = m.user_id LEFT JOIN penilaian p ON p.mahasiswa_id = m.id_mahasiswa LEFT JOIN sertifikat s ON s.mahasiswa_id = m.id_mahasiswa $where_sql")->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));

$sql = "SELECT m.id_mahasiswa, u.nama, m.universitas, m.status, p.nilai_akhir, s.nomor_sertifikat, s.id_sertifikat
        FROM mahasiswa m JOIN users u ON u.id_user = m.user_id LEFT JOIN penilaian p ON p.mahasiswa_id = m.id_mahasiswa LEFT JOIN sertifikat s ON s.mahasiswa_id = m.id_mahasiswa
        $where_sql ORDER BY m.status ASC, u.nama ASC LIMIT $per_page OFFSET $offset";
$res = $conn->query($sql);

function buildQuery($params) { return http_build_query(array_filter($params, function($v) { return $v !== ''; })); }
$query_params = ['search' => $search, 'status' => $filter_status];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat & Penilaian - Admin MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <?php include '_sidebar_admin.php'; ?>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Manajemen Sertifikat</h2>
                <p class="text-xs text-slate-500 font-medium tracking-wide">Kelola penilaian dan penerbitan sertifikat mahasiswa.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 shadow-lg shadow-indigo-100 text-white rounded-xl flex items-center justify-center font-black"><?php echo strtoupper(substr($nama_admin, 0, 1)); ?></div>
            </div>
        </header>

        <div class="p-8">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                        Data Penilaian & Sertifikat
                    </h3>
                    <p class="text-sm text-slate-500 font-bold"><?php echo $total_rows; ?> data</p>
                </div>

                <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Cari Mahasiswa</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama mahasiswa..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                        </div>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Status Magang</label>
                        <select name="status" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                            <option value="">Semua</option>
                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="aktif" <?php echo $filter_status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Cari</button>
                    <?php if ($search !== '' || $filter_status !== ''): ?>
                    <a href="admin-sertifikat.php" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b-2 border-slate-100 text-slate-400 text-xs font-black uppercase tracking-wider">
                                <th class="pb-4 pl-4">Mahasiswa</th>
                                <th class="pb-4">Instansi</th>
                                <th class="pb-4">Status Magang</th>
                                <th class="pb-4 text-center">Nilai Akhir</th>
                                <th class="pb-4">Sertifikat</th>
                                <th class="pb-4 text-right pr-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php if ($res && $res->num_rows > 0): while($row = $res->fetch_assoc()): ?>
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                <td class="py-4 pl-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center font-black"><?php echo strtoupper(substr($row['nama'], 0, 1)); ?></div>
                                        <div>
                                            <p class="font-black text-slate-800"><?php echo htmlspecialchars($row['nama']); ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">ID: #<?php echo $row['id_mahasiswa']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 font-bold text-slate-600"><?php echo htmlspecialchars($row['universitas']); ?></td>
                                <td class="py-4">
                                    <?php $badge = "bg-yellow-100 text-yellow-700"; if($row['status'] === 'aktif') $badge = "bg-emerald-100 text-emerald-700"; if($row['status'] === 'selesai') $badge = "bg-blue-100 text-blue-700"; ?>
                                    <span class="text-[10px] font-black px-3 py-1.5 <?php echo $badge; ?> rounded-xl uppercase tracking-widest"><?php echo $row['status']; ?></span>
                                </td>
                                <td class="py-4 text-center">
                                    <?php if($row['nilai_akhir'] !== null): ?>
                                    <span class="text-lg font-black text-indigo-600"><?php echo floatval($row['nilai_akhir']); ?></span>
                                    <?php else: ?><span class="text-xs text-slate-300 italic font-medium">Belum Dinilai</span><?php endif; ?>
                                </td>
                                <td class="py-4">
                                    <?php if($row['nomor_sertifikat']): ?>
                                    <div class="flex flex-col"><span class="text-xs font-black text-emerald-600">Terbit</span><span class="text-[10px] text-slate-400 font-bold"><?php echo htmlspecialchars($row['nomor_sertifikat']); ?></span></div>
                                    <?php else: ?><span class="text-xs text-slate-300 italic font-medium">Belum Terbit</span><?php endif; ?>
                                </td>
                                <td class="py-4 pr-4 text-right">
                                    <?php if($row['nilai_akhir'] !== null && !$row['nomor_sertifikat']): ?>
                                    <button onclick="issueCertificate(<?php echo $row['id_mahasiswa']; ?>, '<?php echo addslashes($row['nama']); ?>')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl font-bold transition duration-300 shadow-lg shadow-indigo-100">Terbitkan</button>
                                    <?php elseif($row['nomor_sertifikat']): ?>
                                    <a href="generate-sertifikat.php?id=<?php echo $row['id_sertifikat']; ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl font-bold transition duration-300">Lihat</a>
                                    <?php else: ?>
                                    <button disabled class="inline-flex items-center gap-2 px-4 py-2 bg-slate-50 text-slate-300 rounded-xl font-bold cursor-not-allowed">Terbitkan</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="6" class="py-12 text-center text-slate-400 italic font-medium"><?php echo ($search !== '' || $filter_status !== '') ? 'Tidak ada data yang cocok.' : 'Belum ada data mahasiswa.'; ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="flex items-center justify-between mt-6 pt-6 border-t border-slate-100">
                    <p class="text-sm text-slate-500 font-bold">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></p>
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

    <script>
        function issueCertificate(id, name) {
            Swal.fire({
                title: 'Terbitkan Sertifikat?',
                text: "Anda akan menerbitkan sertifikat untuk " + name + ". Nomor sertifikat akan dibuat otomatis.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Terbitkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'admin-sertifikat-proses.php?id=' + id;
                }
            })
        }
    </script>
</body>
</html>
