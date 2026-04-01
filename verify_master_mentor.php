<?php
require 'config.php';

$content = file_get_contents('admin-mentor.php');

// Check CRUD logic
if (strpos($content, "action === 'create'") !== false && 
    strpos($content, "action === 'update'") !== false && 
    strpos($content, "action === 'delete'") !== false) {
    echo "✅ admin-mentor.php CRUD logic present\n";
} else {
    echo "❌ admin-mentor.php CRUD logic missing\n";
}

// Check sidebar in other files
$files = [
    'dashboard-admin.php',
    'admin-periode.php',
    'admin-pendaftaran.php',
    'admin-monitoring-presensi.php',
    'admin-monitoring-logbook.php',
    'admin-mahasiswa-manual.php'
];

foreach ($files as $file) {
    $c = file_get_contents($file);
    if (strpos($c, 'admin-mentor.php') !== false) {
        echo "✅ Sidebar updated in $file\n";
    } else {
        echo "❌ Sidebar missing in $file\n";
    }
}
?>
