<?php
session_start();
include("../includes/db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $product_id = (int)$_POST['product_id'];
    $size = $_POST['size'] ?? "";
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $sql = "SELECT name, price, image, type_id FROM products WHERE id = $product_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        $type_id = (int)($product['type_id'] ?? 4);

        // Nếu sản phẩm loại 4 mà user không gửi size lên (cho an toàn)
        if ($type_id == 4 && empty($size)) {
            $size = "Kích thước chung";
        }

        // --- KIỂM TRA TỒN KHO ---
        // Dùng TRIM để bảo vệ khỏi các ký tự trắng thừa trong DB
        $stmt_check = $conn->prepare("SELECT quantity FROM product_stock WHERE product_id = ? AND TRIM(size) = ? LIMIT 1");
        $stmt_check->bind_param("is", $product_id, $size);
        $stmt_check->execute();
        $res_stock = $stmt_check->get_result();
        $stock_available = 0;
        
        if ($res_stock && $res_stock->num_rows > 0) {
            $stock_data = $res_stock->fetch_assoc();
            $stock_available = (int)$stock_data['quantity'];
        }
        $stmt_check->close();

        $product_key = $product_id . "_" . $size;
        $qty_in_cart = isset($_SESSION['cart'][$product_key]) ? (int)$_SESSION['cart'][$product_key]['quantity'] : 0;
        $total_requested = $quantity + $qty_in_cart;

        if ($total_requested > $stock_available) {
            $msg = ($stock_available > 0) 
                 ? "Sản phẩm này hiện chỉ còn $stock_available món (bạn đang muốn mua tổng $total_requested món). Vui lòng kiểm tra lại giỏ hàng."
                 : "Rất tiếc, sản phẩm này tạm thời đã hết hàng.";
            die("<script>alert('$msg'); window.history.back();</script>");
        }
        // -------------------------

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_key])) {
            $_SESSION['cart'][$product_key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_key] = [
                "name" => $product['name'],
                "price" => $product['price'],
                "image" => $product['image'],
                "size" => $size,
                "quantity" => $quantity
            ];
        }

        if (isset($_POST['buy_now'])) {
            header("Location: ../cart.php");
            exit;
        }

        header("Location: ../product_detail.php?id=" . $product_id);
        exit;
    }
}
header("Location: ../index.php");
exit;
