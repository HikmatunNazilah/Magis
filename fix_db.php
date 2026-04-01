<?php
require_once 'config.php';

$cols = [
    'cv_path VARCHAR(255)',
    'transkrip_path VARCHAR(255)',
    'surat_path VARCHAR(255)',
    'proposal_path VARCHAR(255)'
];

foreach ($cols as $col_def) {
    preg_match('/^(\w+)/', $col_def, $matches);
    $col_name = $matches[1];
    
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM Mahasiswa LIKE '$col_name'");
    if ($check->num_rows == 0) {
        if ($conn->query("ALTER TABLE Mahasiswa ADD $col_def")) {
            echo "Added column: $col_name\n";
        } else {
            echo "Error adding $col_name: " . $conn->error . "\n";
        }
    } else {
        echo "Column $col_name already exists\n";
    }
}
?>
