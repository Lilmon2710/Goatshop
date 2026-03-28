<?php
include("includes/db_connect.php");
$result = $conn->query("SELECT status, payment_method FROM orders LIMIT 5");
while($row = $result->fetch_assoc()) {
    echo "Status: " . $row['status'] . " | Method: " . $row['payment_method'] . "\n";
}
?>
