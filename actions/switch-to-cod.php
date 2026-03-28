<?php
session_start();
// 1. KẾT NỐI CSDL (Sửa lại thông tin của bạn)
$conn = new mysqli("localhost", "root", "", "goatshop");
$conn->set_charset("utf8mb4");
if ($conn->connect_error) { 
    die("Kết nối thất bại: " . $conn->connect_error); 
}

// 2. LẤY MÃ ĐƠN HÀNG
$order_code = htmlspecialchars($_GET['order_code']);

// 3. CẬP NHẬT DATABASE
$status_new = "Đang xử lý";
$payment_method_new = "cod";
$status_old = "Chờ thanh toán"; 

// Code này đã khớp với bảng bạn vừa sửa
$stmt = $conn->prepare("UPDATE orders SET payment_method = ?, status = ? WHERE order_code = ? AND status = ?");
$stmt->bind_param("ssss", $payment_method_new, $status_new, $order_code, $status_old);
$stmt->execute();
$stmt->close();
$conn->close();

// 4. CHUYỂN HƯỚNG
header("Location: ../dat-hang-thanh-cong.php?order_code=" . $order_code . "&switched=true");
exit();
?>