<?php
require_once 'config.php';
$output = "";
$tables = $conn->query("SHOW TABLES");
while($row = $tables->fetch_array()) {
    $table = $row[0];
    $output .= "--- $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    while($field = $res->fetch_assoc()) {
        $output .= $field['Field'] . " | " . $field['Type'] . " | " . $field['Null'] . " | " . $field['Key'] . " | " . $field['Default'] . " | " . $field['Extra'] . "\n";
    }
    $output .= "\n";
}
file_put_contents('tmp/schema_dump.txt', $output);
echo "Schema dumped to tmp/schema_dump.txt";
?>
