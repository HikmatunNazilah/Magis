<?php
$conn = new mysqli('103.30.147.68', 'sekelikn_magis_usr', '[]pl--Xt3)0-!WP[', 'sekelikn_magis_db');
if ($conn->connect_error) die($conn->connect_error);
$res = $conn->query("DESCRIBE sessions");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$conn->close();
?>
