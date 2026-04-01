<?php
$c = new mysqli('localhost', 'root', '', 'magis_db');
if ($c->connect_error) {
    die("Connection failed: " . $c->connect_error);
}
$res = $c->query('SHOW TABLES');
if ($res) {
    while ($r = $res->fetch_array()) {
        echo $r[0] . "\n";
    }
} else {
    echo "Error: " . $c->error;
}
?>
