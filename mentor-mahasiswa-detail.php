<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor' || !isset($_SESSION['mentor_id'])) {
    header("Location: index.php"); exit();
}

$nama_mentor = $_SESSION['nama'];
$mentor_id = $_SESSION['mentor_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) { header("Location: dashboard-mentor.php"); exit(); }
$mahasiswa_id = intval($_GET['id']);

// Validate ownership
$stmt_mhs = $conn->prepare("SELECT m.*, u.nama, u.email FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id WHERE m.id_mahasiswa = ? AND m.mentor_id = ?");
$stmt_mhs->bind_param("ii", $mahasiswa_id, $mentor_id);
$stmt_mhs->execute();
$res_mhs = $stmt_mhs->get_result();
if ($res_mhs->num_rows === 0) { header("Location: dashboard-mentor.php"); exit(); }
$mhs = $res_mhs->fetch_assoc();

// Logbook Filter & Pagination
$filter_status = isset($_GET['validasi']) ? $_GET['validasi'] : '';
$filter_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = ["mahasiswa_id = ?"];
$params = [$mahasiswa_id];
$types = "i";

if ($filter_status !== '') {
    $where[] = "status_validasi = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if ($filter_date !== '') {
    $where[] = "tanggal = ?";
    $params[] = $filter_date;
    $types .= "s";
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

// Count
$stmt_c = $conn->prepare("SELECT COUNT(*) as total FROM Logbook $where_sql");
$stmt_c->bind_param($types, ...$params);
$stmt_c->execute();
$total_logs = $stmt_c->get_result()->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_logs / $per_page));

// Fetch logbooks
$sql_log = "SELECT * FROM Logbook $where_sql ORDER BY tanggal DESC LIMIT ? OFFSET ?";
$params_p = array_merge($params, [$per_page, $offset]);
$types_p = $types . "ii";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->bind_param($types_p, ...$params_p);
$stmt_log->execute();
$res_logbook = $stmt_log->get_result();

function buildQuery($p) { return http_build_query(array_filter($p, function($v) { return $v !== ''; })); }
$query_params = ['id' => $mahasiswa_id, 'validasi' => $filter_status, 'tanggal' => $filter_date];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mahasiswa - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">
    <!-- Sidebar Mentor -->
    <aside class="w-64 bg-slate-900 shadow-xl h-screen fixed left-0 top-0 flex flex-col z-20">
        <div class="h-20 bg-indigo-700 flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" /></svg>
            <h1 class="font-extrabold text-xl text-white tracking-wider leading-none">MAGIS</h1>
        </div>
        <div class="p-4 text-slate-500 text-xs font-bold uppercase tracking-widest mt-4">Menu Utama</div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard-mentor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                <span class="font-semibold">Dashboard Papan</span>
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
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Detail Aktivitas: <?php echo htmlspecialchars($mhs['nama']); ?></h2>
            </div>
            <div class="flex items-center gap-4">
                <a href="mentor-penilaian.php?id=<?php echo $mahasiswa_id; ?>" class="px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-xl font-bold transition duration-300">Beri Penilaian Akhir</a>
                <a href="mentor-settings.php" title="Pengaturan Profil" class="w-10 h-10 bg-indigo-600 shadow-lg shadow-indigo-100 text-white rounded-xl flex items-center justify-center font-black hover:scale-110 hover:bg-indigo-700 transition transform border-2 border-white"><?php echo strtoupper(substr($nama_mentor, 0, 1)); ?></a>
            </div>
        </header>

        <div class="p-8 space-y-8">
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl font-bold">Validasi logbook berhasil disimpan.</div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl font-bold">Terjadi kesalahan saat memproses data.</div>
            <?php endif; ?>

            <!-- Profile Info Card -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm flex items-center gap-6">
                <div class="w-24 h-24 bg-indigo-100 rounded-3xl flex items-center justify-center text-4xl font-black text-indigo-600 shadow-inner"><?php echo strtoupper(substr($mhs['nama'], 0, 1)); ?></div>
                <div>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-none mb-2"><?php echo htmlspecialchars($mhs['nama']); ?></h3>
                    <div class="flex flex-wrap gap-4 text-sm font-bold text-slate-500">
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" /></svg>
                            <?php echo htmlspecialchars($mhs['universitas']); ?>
                        </span>
                        <span class="flex items-center gap-1 border-l-2 border-slate-200 pl-4"><?php echo htmlspecialchars($mhs['jurusan']); ?></span>
                        <span class="flex items-center gap-1 border-l-2 border-slate-200 pl-4">NIM: <?php echo htmlspecialchars($mhs['nim']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Logbook List with Filter -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                    Logbook & Kegiatan Harian
                    <span class="ml-auto text-sm font-bold text-slate-400"><?php echo $total_logs; ?> entri</span>
                </h3>

                <!-- Filter -->
                <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                    <input type="hidden" name="id" value="<?php echo $mahasiswa_id; ?>">
                    <div class="min-w-[150px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Status Validasi</label>
                        <select name="validasi" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                            <option value="">Semua</option>
                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="disetujui" <?php echo $filter_status === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="min-w-[160px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo htmlspecialchars($filter_date); ?>" class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Filter</button>
                    <?php if ($filter_status !== '' || $filter_date !== ''): ?>
                    <a href="mentor-mahasiswa-detail.php?id=<?php echo $mahasiswa_id; ?>" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="space-y-4">
                    <?php if ($res_logbook && $res_logbook->num_rows > 0): ?>
                        <?php while($log = $res_logbook->fetch_assoc()): ?>
                        <div class="p-6 rounded-3xl border <?php echo $log['status_validasi'] === 'pending' ? 'border-amber-200 bg-amber-50/30' : 'border-slate-100 bg-slate-50/50'; ?> hover:shadow-md transition duration-300">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm font-black text-slate-400 flex items-center gap-2 mb-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                        <?php echo date('d M Y', strtotime($log['tanggal'])); ?>
                                    </p>
                                    <h4 class="text-lg font-black text-slate-800"><?php echo nl2br(htmlspecialchars($log['kegiatan'])); ?></h4>
                                </div>
                                <?php
                                $status_badge = "bg-yellow-100 text-yellow-700 border-yellow-200"; $status_text = "Menunggu Validasi";
                                if($log['status_validasi'] === 'disetujui') { $status_badge = "bg-emerald-100 text-emerald-700 border-emerald-200"; $status_text = "Disetujui"; }
                                elseif($log['status_validasi'] === 'ditolak') { $status_badge = "bg-red-100 text-red-700 border-red-200"; $status_text = "Ditolak / Revisi"; }
                                ?>
                                <span class="px-3 py-1 text-xs font-black uppercase tracking-widest rounded-xl border <?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
                            </div>

                            <?php if ($log['bukti_file']): ?>
                            <div class="mb-4">
                                <a href="uploads/<?php echo htmlspecialchars($log['bukti_file']); ?>" target="_blank" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    Lihat Bukti Foto/File
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if ($log['catatan_mentor']): ?>
                            <div class="mb-4 bg-orange-50 border border-orange-200 p-4 rounded-2xl">
                                <p class="text-xs font-black text-orange-800 uppercase tracking-widest mb-1">Catatan Evaluasi Mentor:</p>
                                <p class="text-sm text-orange-900 font-medium"><?php echo nl2br(htmlspecialchars($log['catatan_mentor'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if ($log['status_validasi'] === 'pending' || $log['status_validasi'] === 'ditolak'): ?>
                            <div class="mt-4 pt-4 border-t border-slate-200/60 flex justify-end gap-3">
                                <button onclick="openRejectModal(<?php echo $log['id_logbook']; ?>)" class="px-4 py-2 bg-white border-2 border-slate-200 hover:border-red-500 text-slate-600 hover:text-red-600 rounded-xl font-bold transition text-sm flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    Tolak / Beri Catatan
                                </button>
                                <form action="mentor-validasi-proses.php" method="POST" class="inline">
                                    <input type="hidden" name="id_logbook" value="<?php echo $log['id_logbook']; ?>">
                                    <input type="hidden" name="mahasiswa_id" value="<?php echo $mahasiswa_id; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition text-sm shadow-md shadow-indigo-600/20 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        Setujui Kegiatan
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-12 text-slate-400 italic font-medium"><?php echo ($filter_status !== '' || $filter_date !== '') ? 'Tidak ada logbook yang cocok dengan filter.' : 'Mahasiswa belum mengisi logbook / aktivitas harian.'; ?></div>
                    <?php endif; ?>
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

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl overflow-hidden flex flex-col scale-95 opacity-0 transition-all duration-300" id="rejectModalContent">
            <div class="bg-red-50 p-6 border-b border-red-100 flex justify-between items-center">
                <h3 class="text-xl font-black text-red-800">Tolak & Berikan Evaluasi</h3>
                <button onclick="closeRejectModal()" class="text-red-400 hover:text-red-600 transition bg-red-100/50 p-2 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="mentor-validasi-proses.php" method="POST" class="p-6 space-y-6">
                <input type="hidden" name="id_logbook" id="modal_id_logbook" value="">
                <input type="hidden" name="mahasiswa_id" value="<?php echo $mahasiswa_id; ?>">
                <input type="hidden" name="action" value="reject">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Evaluasi / Masukan</label>
                    <textarea name="catatan_mentor" rows="4" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:border-red-400 focus:ring focus:ring-red-100 transition resize-none outline-none text-slate-700 font-medium" placeholder="Jelaskan alasan penolakan atau perbaikan yang dibutuhkan..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeRejectModal()" class="px-5 py-2.5 text-slate-600 font-bold hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-600/30 transition">Tolak Kegiatan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(idLogbook) {
            document.getElementById('modal_id_logbook').value = idLogbook;
            const modal = document.getElementById('rejectModal');
            const content = document.getElementById('rejectModalContent');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); content.classList.add('scale-100', 'opacity-100'); }, 10);
        }
        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            const content = document.getElementById('rejectModalContent');
            content.classList.remove('scale-100', 'opacity-100'); content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
        }
    </script>
</body>
</html>
