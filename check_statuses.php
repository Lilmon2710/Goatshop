<?php
include("includes/db_connect.php");
$result = $conn->query("SELECT DISTINCT status FROM orders");
echo "Current statuses in DB:\n";
while($row = $result->fetch_assoc()) {
    echo "- [" . $row['status'] . "]\n";
}
?>
