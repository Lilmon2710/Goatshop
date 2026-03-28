<?php
session_start();
include("../includes/db_connect.php");

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php?page=login");
    exit;
}

// Xử lý vòng lặp xóa nhiều đơn hàng (POST)
if (isset($_POST['bulk_delete']) && !empty($_POST['order_ids'])) {
    $ids = $_POST['order_ids'];
    foreach ($ids as $order_id) {
        $order_id = (int)$order_id;
        
        $stmt1 = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt1->bind_param("i", $order_id);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();
        $stmt2->close();
    }
    echo "<script>alert('✅ Đã dọn dẹp sạch sẽ " . count($ids) . " đơn hàng!'); window.location.href='orders.php';</script>";
    exit;
}

// Xử lý đơn lẻ (GET)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = (int)$_GET['id'];

    // Xóa chi tiết đơn hàng trước để tránh lỗi khóa ngoại (nếu có)
    $stmt1 = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
    $stmt1->bind_param("i", $order_id);
    $stmt1->execute();
    $stmt1->close();

    // Xóa đơn hàng chính
    $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt2->bind_param("i", $order_id);
    
    if ($stmt2->execute()) {
        echo "<script>alert('✅ Đã xóa đơn hàng thành công!'); window.location.href='orders.php';</script>";
    } else {
        echo "<script>alert('❌ Lỗi khi xóa đơn hàng: " . $conn->error . "'); window.location.href='orders.php';</script>";
    }
    $stmt2->close();
} else {
    header("Location: orders.php");
}

$conn->close();
?>
