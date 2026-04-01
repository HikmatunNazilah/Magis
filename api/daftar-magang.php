<?php
require_once 'config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mahasiswa_id = $_SESSION['mahasiswa_id'];

$m_sql = "SELECT m.*, u.nama, u.email FROM Mahasiswa m JOIN Users u ON u.id_user = m.user_id WHERE m.id_mahasiswa = $mahasiswa_id";
$m_result = $conn->query($m_sql);
$data = $m_result->fetch_assoc();
$email = $data['email'];
$nama = $data['nama'];

$success = false;
$error = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = $conn->real_escape_string($_POST['nim']);
    $universitas = $conn->real_escape_string($_POST['universitas']);
    $jurusan = $conn->real_escape_string($_POST['jurusan']);
    
    // File Uploads
    $upload_dir = 'uploads/';
    $files = ['cv', 'transkrip', 'surat', 'proposal'];
    $file_paths = [];

    foreach ($files as $f) {
        if (isset($_FILES[$f]) && $_FILES[$f]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$f]['name'], PATHINFO_EXTENSION);
            $new_name = $f . "_" . $mahasiswa_id . "_" . time() . "." . $ext;
            $dest = $upload_dir . $new_name;
            if (move_uploaded_file($_FILES[$f]['tmp_name'], $dest)) {
                $file_paths[$f] = $new_name;
            }
        }
    }

    $cv = isset($file_paths['cv']) ? "'".$file_paths['cv']."'" : "NULL";
    $transkrip = isset($file_paths['transkrip']) ? "'".$file_paths['transkrip']."'" : "NULL";
    $surat = isset($file_paths['surat']) ? "'".$file_paths['surat']."'" : "NULL";
    $proposal = isset($file_paths['proposal']) ? "'".$file_paths['proposal']."'" : "NULL";

    $sql_update = "UPDATE Mahasiswa SET 
                   nim = '$nim', 
                   universitas = '$universitas', 
                   jurusan = '$jurusan', 
                   cv_path = $cv, 
                   transkrip_path = $transkrip, 
                   surat_path = $surat, 
                   proposal_path = $proposal,
                   status = 'pending' 
                   WHERE id_mahasiswa = $mahasiswa_id";

    if ($conn->query($sql_update)) {
        $success = true;
    } else {
        $error = "Terjadi kesalahan: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Magang - MAGIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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
            <a href="<?php echo $data['nim'] ? '#' : 'daftar-magang.php'; ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl <?php echo $data['nim'] ? 'opacity-40 cursor-not-allowed text-slate-400' : 'bg-blue-50 text-blue-600 border-l-4 border-blue-600 transition shadow-sm'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 <?php echo !$data['nim'] ? 'shadow-inner' : ''; ?>">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9Z" />
                </svg>
                <span class="<?php echo $data['nim'] ? 'font-medium' : 'font-bold'; ?>">Daftar</span>
            </a>
            <a href="presensi.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316Z" />
                </svg>
                <span class="font-semibold">Presensi</span>
            </a>
            <a href="logbook.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <span class="font-semibold">Logbook</span>
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

    <main class="ml-64 flex-1 flex flex-col min-h-screen relative pb-20">
        <header class="bg-blue-600 shadow-md h-20 flex items-center justify-between px-8 z-10 sticky top-0">
            <h2 class="text-xl font-bold text-white tracking-tight leading-none">Pendaftaran Magang</h2>
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

        <div class="p-8 flex-1 overflow-y-auto flex flex-col items-center">
            
            <?php if ($data['nim']): ?>
                <!-- Already Registered Message -->
                <div class="bg-white p-12 rounded-[3rem] border border-slate-200 shadow-xl shadow-blue-50 max-w-2xl w-full text-center flex flex-col items-center animate-in fade-in zoom-in duration-500">
                    <div class="w-24 h-24 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-8 border-4 border-white shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800 mb-4 tracking-tight">Pendaftaran Sudah Diterima</h3>
                    <p class="text-slate-500 font-bold mb-10 leading-relaxed">Anda telah berhasil melakukan pendaftaran magang. Data Anda saat ini sedang dalam proses verifikasi oleh tim Admin MAGIS.</p>
                    
                    <div class="grid grid-cols-2 gap-4 w-full mb-10">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-left">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Anda</p>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                <?php echo ($data['status'] === 'aktif' ? 'bg-emerald-100 text-emerald-700' : ($data['status'] === 'ditolak' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700')); ?>">
                                <?php echo $data['status']; ?>
                            </span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-left">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">NIM Terdaftar</p>
                            <p class="font-bold text-slate-800"><?php echo htmlspecialchars($data['nim']); ?></p>
                        </div>
                    </div>

                    <a href="dashboard-mhs.php" class="bg-blue-600 text-white px-10 py-5 rounded-3xl font-black shadow-2xl shadow-blue-100 hover:bg-blue-700 hover:-translate-y-1 transition transform active:scale-95 duration-200">
                        Kembali ke Beranda
                    </a>
                </div>
            <?php else: ?>
                <!-- Registration Form -->
                <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-blue-50 border border-slate-200 max-w-2xl w-full">
                <div class="flex items-center gap-3 mb-8 border-b border-slate-50 pb-6">
                     <div class="w-1.5 h-8 bg-blue-600 rounded-full"></div>
                     <h3 class="text-2xl font-black text-slate-800 tracking-tight">Lengkapi Data Anda</h3>
                </div>

                <?php if ($success): ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-5 rounded-2xl mb-8 flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <p class="font-bold">Berhasil mengajukan pendaftaran. Mohon tunggu proses validasi admin.</p>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-5 rounded-2xl mb-8 flex items-center gap-3">
                    <p class="font-bold"><?php echo $error; ?></p>
                </div>
                <?php endif; ?>

                <form class="space-y-6" action="daftar-magang.php" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nama Lengkap</label>
                            <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" readonly class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-500 outline-none cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">NIM / ID Mahasiswa</label>
                            <input type="text" name="nim" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" required placeholder="Contoh: 415210100...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Universitas / Instansi</label>
                        <input type="text" name="universitas" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" required placeholder="Nama Kampus Anda">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Program Studi / Jurusan</label>
                        <input type="text" name="jurusan" class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm" required placeholder="Contoh: Teknik Informatika">
                    </div>

                    <div class="pt-6 mt-6 border-t border-slate-50 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Curriculum Vitae (PDF)</label>
                            <input type="file" name="cv" accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer" required>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Transkrip Nilai (PDF)</label>
                            <input type="file" name="transkrip" accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer" required>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Surat Pengantar (PDF)</label>
                            <input type="file" name="surat" accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer" required>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Proposal Magang (PDF)</label>
                            <input type="file" name="proposal" accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer" required>
                        </div>
                    </div>

                    <div class="pt-10 mt-6 border-t border-slate-50">
                        <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-3xl shadow-2xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 transition transform active:scale-95 duration-200">
                            Ajukan Pendaftaran Sekarang
                        </button>
                        <p class="text-center text-[10px] font-black text-slate-300 uppercase tracking-widest mt-4">Pastikan semua data diisi dengan benar</p>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
