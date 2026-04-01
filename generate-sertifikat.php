<?php
require_once 'config.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    die("ID Sertifikat tidak ditemukan.");
}

$id_sertifikat = intval($_GET['id']);

// Fetch Certificate & Student Data
$sql = "SELECT s.*, m.nim, u.nama, p.nilai_akhir, m.universitas, m.jurusan
        FROM sertifikat s
        JOIN mahasiswa m ON m.id_mahasiswa = s.mahasiswa_id
        JOIN users u ON u.id_user = m.user_id
        LEFT JOIN penilaian p ON p.mahasiswa_id = m.id_mahasiswa
        WHERE s.id_sertifikat = $id_sertifikat";
$res = $conn->query($sql);

if ($res->num_rows === 0) {
    die("Data sertifikat tidak valid.");
}

$data = $res->fetch_assoc();

// Check access (Admin or the student themselves)
if ($_SESSION['role'] === 'mahasiswa' && $_SESSION['mahasiswa_id'] != $data['mahasiswa_id']) {
    die("Anda tidak memiliki akses ke sertifikat ini.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat - <?php echo htmlspecialchars($data['nama']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .cert-container {
            width: 297mm;
            height: 210mm;
            padding: 15mm;
            background: white;
            position: relative;
            box-sizing: border-box;
            background-color: #fff;
        }
        .cert-border-outer {
            border: 15px solid #1e3a8a;
            height: 100%;
            padding: 5px;
            box-sizing: border-box;
            position: relative;
        }
        .cert-border-inner {
            border: 2px solid #d4af37;
            height: 100%;
            padding: 40px;
            box-sizing: border-box;
            position: relative;
        }
        .title-font { font-family: 'Cinzel', serif; }
        .cursive-font { font-family: 'Great Vibes', cursive; font-size: 3.5rem; }
        .gold-acc { color: #d4af37; }
        .navy-acc { color: #1e3a8a; }
        
        /* Corner ornaments */
        .corner {
            position: absolute;
            width: 80px;
            height: 80px;
            border: 4px solid #d4af37;
            z-index: 10;
        }
        .top-left { top: -10px; left: -10px; border-right: 0; border-bottom: 0; }
        .top-right { top: -10px; right: -10px; border-left: 0; border-bottom: 0; }
        .bottom-left { bottom: -10px; left: -10px; border-right: 0; border-top: 0; }
        .bottom-right { bottom: -10px; right: -10px; border-left: 0; border-top: 0; }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center py-10">

    <div class="no-print mb-10 flex gap-4">
        <button onclick="window.print()" class="px-8 py-3 bg-blue-700 text-white font-black rounded-2xl shadow-xl hover:bg-blue-800 transition transform active:scale-95">
            Cetak Sertifikat
        </button>
        <button onclick="window.close()" class="px-8 py-3 bg-white text-slate-600 font-black rounded-2xl shadow-md border border-slate-200 hover:bg-gray-50 transition active:scale-95">
            Tutup
        </button>
    </div>

    <!-- Certificate Layout -->
    <div class="cert-container shadow-2xl">
        <div class="cert-border-outer">
            <div class="cert-border-inner flex flex-col items-center">
                <!-- Ornaments -->
                <div class="corner top-left"></div>
                <div class="corner top-right"></div>
                <div class="corner bottom-left"></div>
                <div class="corner bottom-right"></div>

                <!-- Content -->
                <div class="mt-4 flex flex-col items-center">
                    <div class="w-20 h-20 bg-blue-700 rounded-full flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" />
                        </svg>
                    </div>
                    <h1 class="title-font text-5xl font-black navy-acc tracking-widest uppercase">Sertifikat Kelulusan</h1>
                    <p class="title-font text-lg font-bold gold-acc tracking-[0.3em] uppercase mt-2">Program Magang MAGIS</p>
                </div>

                <div class="mt-12 text-center">
                    <p class="text-xl italic text-slate-500 font-medium">Dengan ini diberikan kepada:</p>
                    <h2 class="cursive-font navy-acc mt-4"><?php echo htmlspecialchars($data['nama']); ?></h2>
                    <div class="w-64 h-[2px] bg-slate-200 mx-auto mt-2"></div>
                    <p class="text-lg font-bold text-slate-700 mt-2"><?php echo htmlspecialchars($data['universitas']); ?></p>
                    <p class="text-sm font-medium text-slate-400">NIM: <?php echo htmlspecialchars($data['nim']); ?></p>
                </div>

                <div class="mt-10 px-20 text-center text-slate-600 leading-relaxed font-medium">
                    <p>Telah berhasil menyelesaikan program magang intensif di MAGIS dengan pencapaian yang sangat baik.</p>
                    <p>Sertifikat ini diberikan sebagai bentuk apresiasi atas dedikasi, keterampilan, dan kinerja yang luar biasa selama masa program berlangsung.</p>
                </div>

                <div class="mt-8 flex items-center justify-center gap-4">
                    <div class="px-6 py-2 border-2 border-gold-acc rounded-xl">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest block text-center">Nilai Akhir</span>
                        <span class="text-2xl font-black navy-acc block text-center"><?php echo floatval($data['nilai_akhir']); ?></span>
                    </div>
                </div>

                <div class="mt-auto flex items-end justify-between w-full px-10 mb-6">
                    <div class="flex flex-col items-center">
                        <div class="w-32 h-32 border-2 border-slate-100 p-2 rounded-xl mb-4 bg-white shadow-sm flex items-center justify-center overflow-hidden">
                             <!-- QR Placeholder using qrserver API for actual nomor_sertifikat -->
                             <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($data['nomor_sertifikat']); ?>" alt="QR Verification" class="w-full">
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Verifikasi Digital</p>
                        <p class="text-[9px] text-slate-300 font-bold"><?php echo htmlspecialchars($data['nomor_sertifikat']); ?></p>
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <p class="text-sm font-medium text-slate-500 mb-12"><?php echo date('d F Y', strtotime($data['tanggal_terbit'])); ?></p>
                        <div class="w-48 h-[1px] bg-slate-400 mb-2"></div>
                        <p class="font-black navy-acc uppercase tracking-widest">Admin MAGIS System</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Direktur Program</p>
                    </div>
                </div>

                <!-- Footer text -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-[8px] text-slate-300 font-bold uppercase tracking-[0.5em]">
                    MAGIS Assessment & Certification System
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print window if requested via query param
        if (new URLSearchParams(window.location.search).has('autoprint')) {
            window.onload = () => window.print();
        }
    </script>
</body>
</html>
