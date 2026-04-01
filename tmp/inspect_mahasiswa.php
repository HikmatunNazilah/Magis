<?php
require_once 'config.php';
$res = $conn->query("SHOW CREATE TABLE Mahasiswa");
$r = $res->fetch_array();
echo $r[1];
echo "\n\n";
$res2 = $conn->query("SHOW CREATE TABLE Penilaian");
$r2 = $res2->fetch_array();
echo $r2[1];
?>
