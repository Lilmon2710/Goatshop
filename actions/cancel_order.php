<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php?page=login");
    exit;
}

$user_id = $_SESSION['user']['id'];
$order_id = $_GET['order_id'] ?? ($_GET['id'] ?? 0);

if ($order_id > 0) {
    // 1. Kiểm tra đơn hàng có hợp lệ để hủy không (PENDING hoặc PROCESSING)
    $stmt_check = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $order_id, $user_id);
    $stmt_check->execute();
    $res = $stmt_check->get_result();
    $order = $res->fetch_assoc();
    
    if ($order && ($order['status'] == 'pending' || $order['status'] == 'processing')) {
        
        $conn->begin_transaction();
        try {
            // Cập nhật trạng thái đơn hàng sang CANCELLED
            $stmt_cancel = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmt_cancel->bind_param("i", $order_id);
            $stmt_cancel->execute();

            // Lấy danh sách sản phẩm trong đơn hàng để hoàn trả kho
            $stmt_details = $conn->prepare("SELECT product_id, product_size, quantity FROM order_details WHERE order_id = ?");
            $stmt_details->bind_param("i", $order_id);
            $stmt_details->execute();
            $items = $stmt_details->get_result();

            // Hoàn trả số lượng vào bảng product_stock
            $stmt_restore = $conn->prepare("UPDATE product_stock SET quantity = quantity + ? WHERE product_id = ? AND size = ?");
            while ($item = $items->fetch_assoc()) {
                $pid = $item['product_id'];
                $size = $item['product_size'];
                $qty = (int)$item['quantity'];

                $stmt_restore->bind_param("iis", $qty, $pid, $size);
                $stmt_restore->execute();
            }

            $conn->commit();
            $_SESSION['msg'] = "Đã hủy đơn hàng thành công và hoàn trả sản phẩm vào kho.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['err'] = "Lỗi khi hủy đơn hàng: " . $e->getMessage();
        }
    } else {
        $_SESSION['err'] = "Không thể hủy đơn hàng này (Đã giao hàng hoặc đã hủy trước đó).";
    }
}

header("Location: ../orders.php");
exit;