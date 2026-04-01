<?php
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-admin.php");
    exit();
}

$nama_admin = $_SESSION['nama'];
$success_msg = "";
$error_msg = "";

// Handle Form Submissions (Create/Update/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create' || $action === 'update') {
            $nama = $conn->real_escape_string($_POST['nama']);
            $username = $conn->real_escape_string($_POST['username']);
            $email = $conn->real_escape_string($_POST['email']);
            $no_telepon = $conn->real_escape_string($_POST['no_telepon']);
            $jabatan = $conn->real_escape_string($_POST['jabatan']);

            if ($action === 'create') {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $conn->begin_transaction();
                try {
                    $sql_user = "INSERT INTO Users (nama, username, email, password, role) VALUES ('$nama', '$username', '$email', '$password', 'mentor')";
                    if (!$conn->query($sql_user)) throw new Exception("Error creating user: " . $conn->error);
                    
                    $user_id = $conn->insert_id;

                    $sql_mentor = "INSERT INTO Mentor (user_id, jabatan, no_telepon) VALUES ($user_id, '$jabatan', '$no_telepon')";
                    if (!$conn->query($sql_mentor)) throw new Exception("Error creating mentor: " . $conn->error);

                    $conn->commit();
                    $success_msg = "Mentor baru berhasil ditambahkan!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_msg = $e->getMessage();
                }
            } else {
                $id_mentor = (int)$_POST['id_mentor'];
                $id_user = (int)$_POST['id_user'];

                $conn->begin_transaction();
                try {
                    $sql_user = "UPDATE Users SET nama = '$nama', username = '$username', email = '$email' WHERE id_user = $id_user";
                    if (!$conn->query($sql_user)) throw new Exception("Error updating user: " . $conn->error);

                    $sql_mentor = "UPDATE Mentor SET jabatan = '$jabatan', no_telepon = '$no_telepon' WHERE id_mentor = $id_mentor";
                    if (!$conn->query($sql_mentor)) throw new Exception("Error updating mentor: " . $conn->error);

                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $sql_pass = "UPDATE Users SET password = '$password' WHERE id_user = $id_user";
                        $conn->query($sql_pass);
                    }

                    $conn->commit();
                    $success_msg = "Data mentor berhasil diperbarui!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_msg = $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id_mentor = (int)$_POST['id_mentor'];
            $id_user = (int)$_POST['id_user'];

            $conn->begin_transaction();
            try {
                $sql_del = "DELETE FROM Users WHERE id_user = $id_user";
                if (!$conn->query($sql_del)) throw new Exception("Error deleting mentor: " . $conn->error);

                $conn->commit();
                $success_msg = "Mentor berhasil dihapus!";
            } catch (Exception $e) {
                $conn->rollback();
                $error_msg = $e->getMessage();
            }
        }
    }
}

// Search & Pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = [];
$search_escaped = $conn->real_escape_string($search);
if ($search !== '') {
    $where[] = "(u.nama LIKE '%$search_escaped%' OR u.email LIKE '%$search_escaped%' OR u.username LIKE '%$search_escaped%')";
}
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$count_sql = "SELECT COUNT(*) as total FROM Mentor m JOIN Users u ON m.user_id = u.id_user $where_sql";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));

// Fetch paginated
$sql = "SELECT m.*, u.nama, u.username, u.email, u.id_user 
        FROM Mentor m 
        JOIN Users u ON m.user_id = u.id_user 
        $where_sql
        ORDER BY u.nama ASC
        LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);

function buildQuery($params) {
    return http_build_query(array_filter($params, function($v) { return $v !== ''; }));
}
$query_params = ['search' => $search];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Mentor - MAGIS Admin</title>
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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41c-.024-2.116-.19-4.232-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75c1.114 0 2.134.304 3.007.832m0 0c.39.238.744.524 1.06.852m0 0C12.446 16.8 13.385 16 14.53 16c1.3 0 2.458.835 2.766 2.022" />
            </svg>
            <h1 class="font-extrabold text-xl text-white tracking-wider leading-none">MAGIS ADMIN</h1>
        </div>
        
        <div class="p-4 text-slate-500 text-xs font-bold uppercase tracking-widest mt-4">Menu Utama</div>
        <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
            <a href="dashboard-admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                <span class="font-semibold">Dashboard</span>
            </a>
            <a href="admin-periode.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                <span class="font-semibold">Kelola Periode</span>
            </a>
            <a href="admin-mentor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                <span class="font-bold">Data Mentor</span>
            </a>
            <a href="admin-pendaftaran.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                <span class="font-semibold">Data Pendaftaran</span>
            </a>
            <a href="admin-sertifikat.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                <span class="font-semibold">Sertifikat</span>
            </a>
            <div class="p-2 text-slate-500 text-[10px] font-black uppercase tracking-widest mt-2 border-t border-slate-800/50 pt-4">Monitoring</div>
            <a href="admin-monitoring-presensi.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /></svg>
                <span class="font-semibold">Presensi</span>
            </a>
            <a href="admin-monitoring-logbook.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition group-hover:scale-110"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                <span class="font-semibold">Logbook</span>
            </a>
            <div class="p-2 text-slate-500 text-[10px] font-black uppercase tracking-widest mt-2 border-t border-slate-800/50 pt-4">Management</div>
            <a href="admin-mahasiswa-manual.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 font-black transition group-hover:scale-110"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                <span class="font-semibold">Input Manual</span>
            </a>
        </nav>
        
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition group font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-400"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                <span>Logout Admin</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200 px-8 h-20 flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Manajemen Data Mentor</h2>
            <button onclick="openModal('create')" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-1 transition active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Mentor Baru
            </button>
        </header>

        <div class="p-8">
            <?php if ($success_msg): ?>
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-5 rounded-2xl mb-8 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <p class="font-bold"><?php echo $success_msg; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-5 rounded-2xl mb-8 flex items-center gap-3">
                <p class="font-bold"><?php echo $error_msg; ?></p>
            </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Cari Mentor</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama, email, atau username..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm">
                    </div>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    Cari
                </button>
                <?php if ($search !== ''): ?>
                <a href="admin-mentor.php" class="bg-slate-100 text-slate-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">Reset</a>
                <?php endif; ?>
            </form>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest">
                        <tr>
                            <th class="py-5 px-8">Mentor</th>
                            <th class="py-5 px-6">Email / Username</th>
                            <th class="py-5 px-6">Kontak & Jabatan</th>
                            <th class="py-5 px-8 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-indigo-50/10 transition group">
                                <td class="py-6 px-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-indigo-100/50 text-indigo-700 rounded-2xl flex items-center justify-center font-black text-lg">
                                            <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($row['nama']); ?></p>
                                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Mentor ID: #<?php echo $row['id_mentor']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-6">
                                    <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($row['email']); ?></p>
                                    <p class="text-xs text-slate-400">@<?php echo htmlspecialchars($row['username']); ?></p>
                                </td>
                                <td class="py-6 px-6">
                                    <p class="text-sm font-black text-slate-700"><?php echo htmlspecialchars($row['no_telepon'] ?? '-'); ?></p>
                                    <p class="text-xs text-slate-500 font-medium italic"><?php echo htmlspecialchars($row['jabatan'] ?? 'Staff'); ?></p>
                                </td>
                                <td class="py-6 px-8 flex justify-center gap-2 mt-2">
                                    <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="p-2.5 text-slate-400 hover:text-indigo-600 hover:bg-white border border-transparent hover:border-slate-100 rounded-xl transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $row['id_mentor']; ?>, <?php echo $row['id_user']; ?>, '<?php echo addslashes($row['nama']); ?>')" class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-white border border-transparent hover:border-slate-100 rounded-xl transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.682-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-400 font-bold italic">
                                    <?php echo $search !== '' ? 'Tidak ada mentor yang cocok dengan pencarian.' : 'Belum ada data mentor.'; ?>
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

    <!-- Modal Form Mentor -->
    <div id="mentorModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-2xl shadow-2xl animate-in fade-in zoom-in duration-300">
            <div class="flex items-center justify-between mb-8">
                <h3 id="modalTitle" class="text-2xl font-black text-slate-800 tracking-tight">Tambah Mentor Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="mentorForm" action="admin-mentor.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id_mentor" id="form_id_mentor">
                <input type="hidden" name="id_user" id="form_id_user">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest px-1">Informasi Personal</h4>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Nama Lengkap</label>
                            <input type="text" name="nama" id="form_nama" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3.5 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Email</label>
                            <input type="email" name="email" id="form_email" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3.5 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">No. Telepon</label>
                            <input type="text" name="no_telepon" id="form_no_telepon" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3.5 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" placeholder="08xxx">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest px-1">Akses Sistem</h4>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Username</label>
                            <input type="text" name="username" id="form_username" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3.5 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="form_password" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3.5 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" placeholder="Kosongkan jika tidak ubah">
                                <button type="button" onclick="togglePassword('form_password', 'toggle-icon-pass')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition duration-200">
                                    <svg id="toggle-icon-pass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1">Jabatan</label>
                            <input type="text" name="jabatan" id="form_jabatan" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3.5 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm" placeholder="Contoh: Senior Developer">
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex gap-4">
                    <button type="button" onclick="closeModal()" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 font-black rounded-2xl hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 w-full max-w-md shadow-2xl">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.682-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Hapus Mentor?</h3>
                <p class="text-slate-500 font-medium mt-2">Anda akan menghapus data <span id="delete_nama" class="font-black text-slate-800"></span>. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form action="admin-mentor.php" method="POST" class="flex gap-4">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id_mentor" id="delete_id_mentor">
                <input type="hidden" name="id_user" id="delete_id_user">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 font-black rounded-2xl hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="flex-1 px-6 py-4 bg-rose-600 text-white font-black rounded-2xl shadow-xl shadow-rose-100 hover:bg-rose-700 transition active:scale-95">Hapus Permanen</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(mode) {
            const modal = document.getElementById('mentorModal');
            const title = document.getElementById('modalTitle');
            const action = document.getElementById('formAction');
            const form = document.getElementById('mentorForm');
            const passInput = document.getElementById('form_password');

            form.reset();
            modal.classList.remove('hidden');

            if (mode === 'create') {
                title.innerText = "Tambah Mentor Baru";
                action.value = "create";
                passInput.required = true;
                passInput.placeholder = "Min. 8 karakter";
            }
        }

        function openEditModal(mentor) {
            const modal = document.getElementById('mentorModal');
            const title = document.getElementById('modalTitle');
            const action = document.getElementById('formAction');
            const passInput = document.getElementById('form_password');

            title.innerText = "Edit Data Mentor";
            action.value = "update";
            passInput.required = false;
            passInput.placeholder = "Kosongkan jika tidak ganti";

            document.getElementById('form_id_mentor').value = mentor.id_mentor;
            document.getElementById('form_id_user').value = mentor.id_user;
            document.getElementById('form_nama').value = mentor.nama;
            document.getElementById('form_username').value = mentor.username;
            document.getElementById('form_email').value = mentor.email;
            document.getElementById('form_no_telepon').value = mentor.no_telepon;
            document.getElementById('form_jabatan').value = mentor.jabatan;

            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('mentorModal').classList.add('hidden');
        }

        function confirmDelete(id_mentor, id_user, nama) {
            document.getElementById('delete_id_mentor').value = id_mentor;
            document.getElementById('delete_id_user').value = id_user;
            document.getElementById('delete_nama').innerText = nama;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />';
            }
        }
    </script>
</body>
</html>
