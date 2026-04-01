<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mahasiswa_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filter & Search parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;

// Build WHERE clause
$where_clauses = ["mahasiswa_id = $mahasiswa_id"];
if ($search) {
    $where_clauses[] = "(kegiatan LIKE '%$search%' OR tanggal LIKE '%$search%')";
}
if ($start_date) {
    $where_clauses[] = "tanggal >= '$start_date'";
}
if ($end_date) {
    $where_clauses[] = "tanggal <= '$end_date'";
}
if ($month) {
    $where_clauses[] = "MONTH(tanggal) = $month";
}
$where_sql = implode(' AND ', $where_clauses);

// Count total records
$count_sql = "SELECT COUNT(*) as total FROM Logbook WHERE $where_sql";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch Records
$sql = "SELECT id_logbook, tanggal, kegiatan, status_validasi FROM Logbook WHERE $where_sql ORDER BY tanggal DESC, id_logbook DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$logbooks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logbooks[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'data' => $logbooks,
    'total_records' => (int)$total_records,
    'total_pages' => (int)$total_pages,
    'current_page' => $page
]);
?>
