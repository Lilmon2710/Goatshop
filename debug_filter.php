<?php
require 'includes/db_connect.php';
$r = mysqli_query($conn, "SELECT COUNT(*) as c, brand FROM products GROUP BY brand");
while($row = mysqli_fetch_assoc($r)) {
    echo $row['brand'] . " : " . $row['c'] . "\n";
}
?>
