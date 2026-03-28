<?php
session_start();
include(__DIR__ . '/../includes/db_connect.php');

if (!isset($_GET['order_id'])) {
    die("Thiếu order_id");
}

$order_id = (int)$_GET['order_id'];

// LẤY ORDER
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Không tìm thấy đơn");
}

if ($order['status'] !== 'pending' && $order['status'] !== 'Chờ thanh toán') {
    die("Đơn này không thể thanh toán tiếp");
}

// Chuyển hướng sang trang giả lập MoMo
header("Location: momo_mock.php?order_id=" . $order_id . "&amount=" . $order['total_amount'] . "&order_code=" . $order['order_code']);
exit;
?>
