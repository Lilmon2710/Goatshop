<?php
include("includes/db_connect.php");
$result = $conn->query("SHOW COLUMNS FROM product_stock");
while($row = $result->fetch_assoc()) {
    printf("%-15s | %-15s | %-5s | %-5s\n", $row['Field'], $row['Type'], $row['Null'], $row['Key']);
}
?>
