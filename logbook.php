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
$m_sql = "SELECT m.*, u.nama, u.email FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id WHERE u.id_user = $user_id";
$m_result = $conn->query($m_sql);
$data = $m_result->fetch_assoc();
$email = $data['email'];
$nama = $data['nama'];
$status = $data['status'];
$mahasiswa_id = $data['id_mahasiswa'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
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
            <a href="presensi.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:scale-110 transition">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-semibold">Presensi</span>
            </a>
            <a href="logbook.php" class="flex items-center gap-3 px-3 py-3 rounded-xl bg-blue-50 text-blue-600 border-l-4 border-blue-600 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shadow-inner">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <span class="font-bold">Logbook</span>
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

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-blue-600 shadow-md h-20 flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-xl font-bold text-white tracking-tight leading-none">Logbook Magang</h2>
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
        
        <div class="p-8 flex-1 overflow-y-auto">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-none mb-1">Catatan Harian Magang</h3>
                    <p class="text-sm text-slate-500 font-medium italic">Simpan progress magang Anda di sini dan tidak akan hilang.</p>
                </div>
                <button onclick="document.getElementById('addLogModal').classList.remove('hidden')" class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 hover:-translate-y-1 transition active:scale-95">
                    + Tambah Logbook
                </button>
            </div>

            <!-- Filter Form -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 mb-8">
                <form id="filterForm" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Cari Kegiatan</label>
                        <div class="relative">
                            <input type="text" id="filterSearch" placeholder="Cari kegiatan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>
                    <div class="w-48">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Bulan</label>
                        <select id="filterMonth" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition appearance-none cursor-pointer">
                            <option value="">Semua Bulan</option>
                            <?php
                            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            foreach($months as $index => $m_name):
                                $m_val = $index + 1;
                                echo "<option value='$m_val'>$m_name</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Mulai</label>
                        <input type="date" id="filterStartDate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    </div>
                    <div class="w-40">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Sampai</label>
                        <input type="date" id="filterEndDate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition shadow-lg shadow-blue-200 flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        Filter
                    </button>
                    <button type="button" id="resetFilterBtn" class="bg-slate-200 hover:bg-slate-300 text-slate-600 font-bold py-3 px-6 rounded-xl transition text-sm hidden">Reset</button>
                </form>
            </div>
            
            <!-- Table List -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-black tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="py-4 px-6">Tanggal</th>
                                <th class="py-4 px-6">Kegiatan</th>
                                <th class="py-4 px-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="logbookTableBody" class="divide-y divide-slate-100">
                            <!-- Data populated by JS -->
                            <tr>
                                <td colspan="3" class="py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                        <p class="text-sm font-bold text-slate-500">Memuat logbook...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginationContainer" class="hidden p-6 border-t border-slate-100 flex items-center justify-between"></div>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div id="addLogModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-3xl w-full max-w-lg shadow-2xl animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-black text-2xl text-slate-800 tracking-tight">Tambah Logbook Baru</h3>
                <button onclick="document.getElementById('addLogModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="logbookForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">Tanggal Kegiatan</label>
                    <input type="date" id="logDate" required value="<?php echo date('Y-m-d'); ?>" class="w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">Kategori/Topik Kegiatan</label>
                    <input type="text" id="logTitle" placeholder="Cth: Pengembangan Frontend MAGIS" required class="w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">Deskripsi & Hasil</label>
                    <textarea id="logDesc" rows="4" placeholder="Jelaskan detail apa yang Anda kerjakan harini ini..." required class="w-full border border-slate-200 bg-slate-50 rounded-2xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"></textarea>
                </div>
                <div class="flex gap-3 mt-8 pt-6 border-t border-slate-50">
                    <button type="button" onclick="document.getElementById('addLogModal').classList.add('hidden')" class="flex-1 px-6 py-3 text-slate-600 bg-slate-100 font-bold rounded-2xl hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" id="submitBtn" class="flex-[2] px-6 py-3 bg-blue-600 text-white font-bold rounded-2xl shadow-lg shadow-blue-100 hover:bg-blue-700 transition active:scale-95">Simpan Logbook</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPage = 1;

        function getFilterParams() {
            const search = document.getElementById('filterSearch').value;
            const month = document.getElementById('filterMonth').value;
            const startDate = document.getElementById('filterStartDate').value;
            const endDate = document.getElementById('filterEndDate').value;
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (month) params.set('month', month);
            if (startDate) params.set('start_date', startDate);
            if (endDate) params.set('end_date', endDate);
            params.set('page', currentPage);

            // Show/hide reset button
            const resetBtn = document.getElementById('resetFilterBtn');
            if (search || month || startDate || endDate) {
                resetBtn.classList.remove('hidden');
            } else {
                resetBtn.classList.add('hidden');
            }
            return params.toString();
        }

        async function fetchLogbooks() {
            try {
                const params = getFilterParams();
                const response = await fetch('api/get-logbooks.php?' + params);
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('logbookTableBody');
                    if (result.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="3" class="py-12 text-center text-slate-400 font-medium italic">Belum ada catatan logbook.</td>
                            </tr>
                        `;
                        document.getElementById('paginationContainer').classList.add('hidden');
                        return;
                    }
                    
                    tbody.innerHTML = result.data.map(log => `
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="py-5 px-6 font-bold text-slate-600 text-sm whitespace-nowrap">${log.tanggal}</td>
                            <td class="py-5 px-6">
                                <div class="whitespace-pre-wrap text-sm text-slate-700 leading-relaxed">${log.kegiatan}</div>
                            </td>
                            <td class="py-5 px-6 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${log.status_validasi === 'disetujui' ? 'bg-emerald-100 text-emerald-700' : (log.status_validasi === 'ditolak' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')}">
                                    ${log.status_validasi}
                                </span>
                            </td>
                        </tr>
                    `).join('');

                    // Render pagination
                    renderPagination(result.current_page, result.total_pages, result.total_records);
                }
            } catch (error) {
                console.error('Error fetching logbooks:', error);
            }
        }

        function renderPagination(page, totalPages, totalRecords) {
            const container = document.getElementById('paginationContainer');
            if (totalPages <= 1) {
                container.classList.add('hidden');
                return;
            }
            container.classList.remove('hidden');
            const limit = 10;
            const offset = (page - 1) * limit;
            const startLoop = Math.max(1, page - 2);
            const endLoop = Math.min(totalPages, page + 2);

            let pagesHtml = '';
            if (page > 1) {
                pagesHtml += `<button onclick="goToPage(${page - 1})" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg></button>`;
            }
            for (let i = startLoop; i <= endLoop; i++) {
                const activeClass = i === page ? 'bg-blue-600 border-blue-600 text-white font-bold shadow-lg shadow-blue-200' : 'border-slate-200 text-slate-600 hover:bg-blue-50 hover:text-blue-600';
                pagesHtml += `<button onclick="goToPage(${i})" class="w-10 h-10 flex items-center justify-center rounded-xl border ${activeClass} transition">${i}</button>`;
            }
            if (page < totalPages) {
                pagesHtml += `<button onclick="goToPage(${page + 1})" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg></button>`;
            }

            container.innerHTML = `
                <p class="text-xs font-bold text-slate-400">
                    Menampilkan <span class="text-slate-800">${offset + 1}</span> - <span class="text-slate-800">${Math.min(offset + limit, totalRecords)}</span> dari <span class="text-slate-800">${totalRecords}</span> records
                </p>
                <div class="flex items-center gap-2">${pagesHtml}</div>
            `;
        }

        function goToPage(page) {
            currentPage = page;
            fetchLogbooks();
        }

        // Filter form handler
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            currentPage = 1;
            fetchLogbooks();
        });

        // Reset filter handler
        document.getElementById('resetFilterBtn').addEventListener('click', function() {
            document.getElementById('filterSearch').value = '';
            document.getElementById('filterMonth').value = '';
            document.getElementById('filterStartDate').value = '';
            document.getElementById('filterEndDate').value = '';
            currentPage = 1;
            fetchLogbooks();
        });

        document.getElementById('logbookForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerText;
            
            submitBtn.innerText = 'Menyimpan...';
            submitBtn.disabled = true;

            const date = document.getElementById('logDate').value;
            const title = document.getElementById('logTitle').value;
            const desc = document.getElementById('logDesc').value;
            
            try {
                const response = await fetch('api/save-logbook.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tanggal: date, title: title, desc: desc })
                });
                
                const result = await response.json();
                if (result.success) {
                    document.getElementById('addLogModal').classList.add('hidden');
                    e.target.reset();
                    currentPage = 1;
                    await fetchLogbooks();
                } else {
                    alert('Gagal menyimpan: ' + result.message);
                }
            } catch (error) {
                console.error('Error saving logbook:', error);
                alert('Terjadi kesalahan jaringan.');
            } finally {
                submitBtn.innerText = originalText;
                submitBtn.disabled = false;
            }
        });

        // Initialize
        fetchLogbooks();
    </script>
</body>
</html>
