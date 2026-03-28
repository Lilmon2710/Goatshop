<?php
session_start(); //

// 1. KIỂM TRA DỮ LIỆU GỬI LÊN
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id']) || !isset($_POST['new_status'])) {
    die("Yêu cầu không hợp lệ.");
}

// 2. LẤY DỮ LIỆU
$order_id = (int)$_POST['order_id'];
$new_status = $_POST['new_status'];

$allowed_statuses = ['Chờ thanh toán', 'Đang xử lý', 'Đang giao hàng', 'Đã hoàn thành', 'Đã hủy'];
if (!in_array($new_status, $allowed_statuses)) {
    die("Trạng thái không hợp lệ.");
}

// 3. KẾT NỐI CSDL (Sửa lại thông tin của bạn)
$conn = new mysqli("localhost", "root", "", "goatshop"); // <-- SỬA DÒNG NÀY
$conn->set_charset("utf8mb4");
if ($conn->connect_error) { 
    die("Kết nối thất bại: " . $conn->connect_error); 
}

// 4. CẬP NHẬT TRẠNG THÁI TRONG DATABASE
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $order_id);
$stmt->execute();
$stmt->close();
$conn->close();

// 5. CHUYỂN HƯỚNG NGƯỢC LẠI TRANG CHI TIẾT
header("Location: order_detail.php?id=" . $order_id . "&updated=true");
exit();
?>