<?php
include("includes/db_connect.php");
$result = $conn->query("SHOW TRIGGERS LIKE 'products'");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No triggers found on 'products' table.";
}
?>
