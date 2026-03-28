<?php
session_start();
include("../includes/db_connect.php");

$response = ['subtotal' => 0, 'total' => 0, 'error' => null];

if (isset($_POST['id']) && isset($_POST['quantity'])) {
    $product_key = $_POST['id']; // vd: 10_42
    $qty = (int)$_POST['quantity'];

    if (isset($_SESSION['cart'][$product_key])) {
        // --- KIỂM TRA TỒN KHO ---
        $parts = explode("_", $product_key);
        $pid = (int)$parts[0];
        $size = $parts[1] ?? "";

        $stmt = $conn->prepare("SELECT quantity FROM product_stock WHERE product_id = ? AND size = ?");
        $stmt->bind_param("is", $pid, $size);
        $stmt->execute();
        $res = $stmt->get_result();
        $stock = $res->fetch_assoc();
        $stock_available = $stock ? (int)$stock['quantity'] : 0;

        if ($qty > $stock_available) {
            $response['error'] = "Chỉ còn $stock_available sản phẩm trong kho.";
            // Trả về số lượng cũ nếu vượt quá
            $qty = $_SESSION['cart'][$product_key]['quantity'];
        }

        if ($qty > 0) {
            $_SESSION['cart'][$product_key]['quantity'] = $qty;
            $response['subtotal'] = $_SESSION['cart'][$product_key]['price'] * $qty;
        }
        else {
            unset($_SESSION['cart'][$product_key]);
        }
    }

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $response['total'] = $total;
    $response['current_qty'] = $qty;
}

echo json_encode($response);
exit;
