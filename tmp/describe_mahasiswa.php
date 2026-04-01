<?php
require_once 'config.php';
$res = $conn->query("DESCRIBE mahasiswa");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
