<?php
require_once 'config.php';

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor' || !isset($_SESSION['mentor_id'])) {
    header("Location: index.php");
    exit();
}

$nama_mentor = $_SESSION['nama'];
$mentor_id = $_SESSION['mentor_id'];

// Get Mahasiswa ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard-mentor.php");
    exit();
}
$mahasiswa_id = intval($_GET['id']);

// Validate that this Mahasiswa belongs to the current mentor
$sql_mhs = "SELECT m.*, u.nama, u.email 
            FROM Mahasiswa m
            JOIN Users u ON u.id_user = m.user_id
            WHERE m.id_mahasiswa = ? AND m.mentor_id = ?";
$stmt_mhs = $conn->prepare($sql_mhs);
$stmt_mhs->bind_param("ii", $mahasiswa_id, $mentor_id);
$stmt_mhs->execute();
$res_mhs = $stmt_mhs->get_result();

if ($res_mhs->num_rows === 0) {
    header("Location: dashboard-mentor.php");
    exit();
}
$mhs = $res_mhs->fetch_assoc();

// Check if evaluation already exists
$sql_penilaian = "SELECT * FROM Penilaian WHERE mahasiswa_id = ? AND mentor_id = ?";
$stmt_pen = $conn->prepare($sql_penilaian);
$stmt_pen->bind_param("ii", $mahasiswa_id, $mentor_id);
$stmt_pen->execute();
$res_pen = $stmt_pen->get_result();
$penilaian = $res_pen->fetch_assoc();

$is_evaluated = ($res_pen->num_rows > 0);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Akhir Magang - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .slider-thumb::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%; 
            background: #4f46e5;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-indigo-50/50 flex font-sans text-slate-800">

    <!-- Sidebar Mentor -->
    <aside class="w-64 bg-slate-900 shadow-xl h-screen fixed left-0 top-0 flex flex-col z-20">
        <div class="h-20 bg-indigo-700 flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" />
            </svg>
            <h1 class="font-extrabold text-xl text-white tracking-wider leading-none">MAGIS</h1>
        </div>
        
        <div class="p-4 text-slate-500 text-xs font-bold uppercase tracking-widest mt-4">Menu Utama</div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard-mentor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span class="font-semibold">Dashboard Papan</span>
            </a>
            <a href="mentor-monitoring-presensi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-semibold">Monitoring Presensi</span>
            </a>
        </nav>
        
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition group font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span>Logout Mentor</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Evaluasi Kinerja Mahasiswa</h2>
            </div>
            <div class="flex items-center gap-4">
                <a href="mentor-settings.php" title="Pengaturan Profil" class="w-10 h-10 bg-indigo-600 shadow-lg shadow-indigo-100 text-white rounded-xl flex items-center justify-center font-black hover:scale-110 hover:bg-indigo-700 transition transform border-2 border-white">
                    <?php echo strtoupper(substr($nama_mentor, 0, 1)); ?>
                </a>
            </div>
        </header>

        <!-- Form Content -->
        <div class="p-8 max-w-4xl mx-auto w-full space-y-8">
            
            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-bold">Penilaian kinerja berhasil disimpan.</span>
                </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-bold">Terjadi kesalahan pada server saat memproses data.</span>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Profile Overview -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-2xl font-black shadow-inner">
                        <?php echo strtoupper(substr($mhs['nama'], 0, 1)); ?>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 leading-tight mb-1"><?php echo htmlspecialchars($mhs['nama']); ?></h3>
                        <p class="text-sm font-bold text-slate-500"><?php echo htmlspecialchars($mhs['universitas']) . " - " . htmlspecialchars($mhs['jurusan']); ?></p>
                    </div>
                </div>
                <?php if($is_evaluated): ?>
                <span class="px-4 py-2 bg-emerald-100 text-emerald-700 font-black rounded-xl border border-emerald-300">Gradiasi Lengkap (<?php echo floatval($penilaian['nilai_akhir']); ?>)</span>
                <?php else: ?>
                <span class="px-4 py-2 bg-yellow-100 text-yellow-700 font-black rounded-xl border border-yellow-300">Belum Dinilai</span>
                <?php endif; ?>
            </div>

            <!-- Evaluation Form -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                    Form Nilai Akhir
                </h3>
                <p class="text-sm font-medium text-slate-500 mb-8 max-w-xl">
                    Berikan penilaian pada skala 0 hingga 100 untuk setiap aspek kompetensi mahasiswa selama menjalani magang.
                </p>

                <form action="mentor-penilaian-proses.php" method="POST" class="space-y-8" <?php echo $is_evaluated ? 'onsubmit="return false;"' : ''; ?>>
                    <input type="hidden" name="mahasiswa_id" value="<?php echo $mahasiswa_id; ?>">
                    <?php if($is_evaluated) { echo '<fieldset disabled="disabled" class="space-y-8">'; } ?>

                    <!-- Disiplin -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="font-bold text-slate-700">Kedisiplinan & Waktu</label>
                            <span id="label-disiplin" class="text-lg font-black text-indigo-600"><?php echo $is_evaluated ? $penilaian['nilai_disiplin'] : '0'; ?></span>
                        </div>
                        <input type="range" name="nilai_disiplin" min="0" max="100" value="<?php echo $is_evaluated ? $penilaian['nilai_disiplin'] : '0'; ?>"
                               oninput="document.getElementById('label-disiplin').innerText = this.value; calculateAvg();" 
                               class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer slider-thumb shadow-inner">
                        <p class="text-xs text-slate-400 mt-2">Ketepatan waktu kehadiran, penyelesaian logbook tepat waktu, kepatuhan jadwal.</p>
                    </div>

                    <!-- Analisis -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="font-bold text-slate-700">Kualitas & Analisis Pekerjaan</label>
                            <span id="label-analisis" class="text-lg font-black text-indigo-600"><?php echo $is_evaluated ? $penilaian['nilai_analisis'] : '0'; ?></span>
                        </div>
                        <input type="range" name="nilai_analisis" id="nilai_analisis" min="0" max="100" value="<?php echo $is_evaluated ? $penilaian['nilai_analisis'] : '0'; ?>"
                               oninput="document.getElementById('label-analisis').innerText = this.value; calculateAvg();" 
                               class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer slider-thumb shadow-inner">
                        <p class="text-xs text-slate-400 mt-2">Kualitas hasil pekerjaan, pemahaman tugas, penyelesaian masalah harian.</p>
                    </div>

                    <!-- Komunikasi -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="font-bold text-slate-700">Komunikasi</label>
                            <span id="label-komunikasi" class="text-lg font-black text-indigo-600"><?php echo $is_evaluated ? $penilaian['nilai_komunikasi'] : '0'; ?></span>
                        </div>
                        <input type="range" name="nilai_komunikasi" min="0" max="100" value="<?php echo $is_evaluated ? $penilaian['nilai_komunikasi'] : '0'; ?>"
                               oninput="document.getElementById('label-komunikasi').innerText = this.value; calculateAvg();" 
                               class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer slider-thumb shadow-inner">
                        <p class="text-xs text-slate-400 mt-2">Kelancaran artikulasi, kemampuan bertanya logis, adaptasi bahasa profesional.</p>
                    </div>

                    <!-- Kerjasama -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="font-bold text-slate-700">Kerjasama Tim / Etika</label>
                            <span id="label-kerjasama" class="text-lg font-black text-indigo-600"><?php echo $is_evaluated ? $penilaian['nilai_kerjasama'] : '0'; ?></span>
                        </div>
                        <input type="range" name="nilai_kerjasama" min="0" max="100" value="<?php echo $is_evaluated ? $penilaian['nilai_kerjasama'] : '0'; ?>"
                               oninput="document.getElementById('label-kerjasama').innerText = this.value; calculateAvg();" 
                               class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer slider-thumb shadow-inner">
                        <p class="text-xs text-slate-400 mt-2">Sikap proaktif dalam tim, respect kepada member, keterlibatan kultur.</p>
                    </div>

                    <div class="p-6 bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] flex items-center justify-between relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/50 to-transparent"></div>
                        <div class="relative z-10 text-slate-600 font-bold uppercase tracking-widest text-sm">
                            Estimasi Nilai Akhir
                        </div>
                        <div class="relative z-10 text-4xl font-black text-indigo-700" id="totalAvg">
                            <?php echo $is_evaluated ? floatval($penilaian['nilai_akhir']) : '0'; ?>
                        </div>
                    </div>

                    <?php if($is_evaluated) { echo '</fieldset>'; } ?>

                    <?php if(!$is_evaluated): ?>
                    <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-600/20 transition duration-300">
                        Simpan Penilaian Akhir
                    </button>
                    <?php else: ?>
                    <div class="text-center bg-slate-100 text-slate-500 font-bold py-4 rounded-2xl">
                        Penilaian telah terkunci dan tidak dapat diubah lagi.
                    </div>
                    <?php endif; ?>
                </form>

            </div>

        </div>
    </main>

    <script>
        function calculateAvg() {
            const disiplin = parseInt(document.querySelector('input[name="nilai_disiplin"]').value);
            const analisis = parseInt(document.querySelector('input[name="nilai_analisis"]').value);
            const komunikasi = parseInt(document.querySelector('input[name="nilai_komunikasi"]').value);
            const kerjasama = parseInt(document.querySelector('input[name="nilai_kerjasama"]').value);

            const total = (disiplin + analisis + komunikasi + kerjasama) / 4;
            document.getElementById('totalAvg').innerText = total.toFixed(1);
        }
    </script>
</body>
</html>
