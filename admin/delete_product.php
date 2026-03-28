<?php
include("../includes/db_connect.php"); 

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; 

   
    $query = "DELETE FROM products WHERE id = $id";
    mysqli_query($conn, $query);
}


header("Location: products.php");
exit;
